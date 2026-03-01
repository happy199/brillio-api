<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Resource;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    /**
     * List all published resources (visible for the organization).
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $query = Resource::where('is_published', true)
            ->where('is_validated', true)
            ->whereHas('user', function ($q) {
                $q->where('is_admin', true)
                    ->orWhereHas('mentorProfile', fn ($mp) => $mp->where('is_published', true));
            })
            ->with('user')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('price')) {
            $request->price === 'free'
                ? $query->where('is_premium', false)
                : $query->where('is_premium', true);
        }

        $resources = $query->paginate(12)->withQueryString();

        // IDs déjà offerts par l'org (pour badge "déjà offert")
        $giftedIds = Purchase::where('gifted_by_organization_id', $organization->id)
            ->pluck('item_id')
            ->unique();

        return view('organization.resources.index', compact('resources', 'organization', 'giftedIds'));
    }

    /**
     * Show a single resource with the gift modal.
     */
    public function show(Resource $resource)
    {
        if (! $resource->is_published || ! $resource->is_validated) {
            abort(404);
        }

        $organization = $this->getCurrentOrganization();

        // Credit cost per young person
        $creditCost = 0;
        $isLocked = false;
        if ($resource->is_premium) {
            $isLocked = true;
            $creditPrice = $this->walletService->getCreditPrice('jeune');
            $creditCost = $creditPrice > 0 ? (int) ceil($resource->price / $creditPrice) : 0;

            // Security: don't show content or file for premium resources to organizations
            $resource->content = null;
            $resource->file_path = null;
        }

        // Jeunes of this organization who DON'T already own the resource
        $jeunes = $organization->users()
            ->where(function ($query) {
                $query->where('users.user_type', User::TYPE_JEUNE);
            })
            ->whereDoesntHave('purchases', function ($query) use ($resource) {
                $query->where('item_type', Resource::class)
                    ->where('item_id', $resource->id);
            })
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get();

        // Already gifted jeune IDs for this resource
        $alreadyGiftedJeuneIds = Purchase::where('item_type', Resource::class)
            ->where('item_id', $resource->id)
            ->where('gifted_by_organization_id', $organization->id)
            ->pluck('user_id')
            ->toArray();

        // Track free resource view
        if (! $isLocked) {
            $resource->increment('views_count');
        }

        return view('organization.resources.show', compact(
            'resource',
            'organization',
            'creditCost',
            'jeunes',
            'alreadyGiftedJeuneIds',
            'isLocked'
        ));
    }

    /**
     * Gift a premium resource to one or more young people.
     */
    public function gift(Request $request, Resource $resource)
    {
        if (! $resource->is_published || ! $resource->is_validated || ! $resource->is_premium) {
            return back()->with('error', 'Cette ressource n\'est pas disponible.');
        }

        if (auth()->user()->organization_role !== 'admin') {
            return redirect()->back()->with('error', 'Seuls les administrateurs peuvent offrir des ressources.');
        }

        $request->validate([
            'jeune_ids' => 'required|array|min:1',
            'jeune_ids.*' => 'integer|exists:users,id',
        ]);

        $organization = $this->getCurrentOrganization();

        $creditPrice = $this->walletService->getCreditPrice('jeune');
        $costPerJeune = $creditPrice > 0 ? (int) ceil($resource->price / $creditPrice) : 0;

        if ($costPerJeune === 0) {
            return back()->with('error', 'Impossible de calculer le coût en crédits.');
        }

        // Filter out jeunes already gifted this resource
        $alreadyGifted = Purchase::where('item_type', Resource::class)
            ->where('item_id', $resource->id)
            ->where('gifted_by_organization_id', $organization->id)
            ->pluck('user_id')
            ->toArray();

        $jeuneIds = collect($request->jeune_ids)
            ->diff($alreadyGifted)
            ->unique()
            ->values();

        if ($jeuneIds->isEmpty()) {
            return back()->with('info', 'Ces jeunes ont déjà reçu cette ressource.');
        }

        // Verify jeunes belong to this organization
        $validJeunesQuery = $organization->users()
            ->whereIn('users.id', $jeuneIds->toArray())
            ->where(function ($query) {
                $query->where('users.user_type', User::TYPE_JEUNE);
            });

        $validJeunes = $validJeunesQuery->get();

        if ($validJeunes->count() !== $jeuneIds->count()) {
            return back()->with('error', 'Un ou plusieurs jeunes sélectionnés ne font pas partie de votre organisation ou ne sont pas éligibles.');
        }

        // Secondary check: exclude those who already own the resource
        $validJeunes = $validJeunes->filter(function ($jeune) use ($resource) {
            return ! Purchase::where('user_id', $jeune->id)
                ->where('item_type', Resource::class)
                ->where('item_id', $resource->id)
                ->exists();
        });

        if ($validJeunes->count() === 0) {
            return back()->with('info', 'Tous les jeunes sélectionnés possèdent déjà cette ressource.');
        }

        if ($validJeunes->count() !== $jeuneIds->count()) {
            // Some were filtered out, but at least some remain
            session()->flash('warning', ($jeuneIds->count() - $validJeunes->count()).' jeunes ont été ignorés car ils possèdent déjà la ressource.');
        }

        $totalCost = $costPerJeune * $validJeunes->count();

        if ($organization->credits_balance < $totalCost) {
            return back()->with('error', "Solde insuffisant. Il vous faut {$totalCost} crédits (vous avez {$organization->credits_balance}).");
        }

        try {
            DB::transaction(function () use ($organization, $resource, $validJeunes, $costPerJeune, $totalCost) {
                // 1. Deduct total cost from organization wallet
                $this->walletService->deductCredits(
                    $organization,
                    $totalCost,
                    'expense',
                    "Ressource offerte : {$resource->title} (à {$validJeunes->count()} jeunes)"
                );

                // 2. Process for each jeune
                $mentor = $resource->user;
                $mentorCreditsPerSale = 0;
                if ($mentor && $resource->is_premium) {
                    $mentorCreditPrice = $this->walletService->getCreditPrice('mentor');
                    $mentorCreditsPerSale = $mentorCreditPrice > 0 ? (int) ceil($resource->price / $mentorCreditPrice) : 0;
                }

                foreach ($validJeunes as $jeune) {
                    // Create Purchase record
                    $purchase = Purchase::create([
                        'user_id' => $jeune->id,
                        'item_type' => Resource::class,
                        'item_id' => $resource->id,
                        'cost_credits' => $costPerJeune,
                        'original_price_fcfa' => $resource->price,
                        'gifted_by_organization_id' => $organization->id,
                        'purchased_at' => now(),
                    ]);

                    // 3. Credit the Mentor (if applicable)
                    if ($mentor && $mentorCreditsPerSale > 0) {
                        $this->walletService->addCredits(
                            $mentor,
                            $mentorCreditsPerSale,
                            'income',
                            "Offert par {$organization->name} à {$jeune->name} : {$resource->title}",
                            $purchase
                        );
                    }

                    // Notification par email au jeune
                    DB::afterCommit(function () use ($jeune, $resource, $organization) {
                        app(\App\Services\MentorshipNotificationService::class)->sendResourceGiftedNotification($jeune, $resource, $organization);
                    }
                    );
                }

                $resource->increment('sales_count', $validJeunes->count());
            });

            return back()->with('success', "Ressource offerte à {$validJeunes->count()} jeune(s) avec succès ! ({$totalCost} crédits débités)");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'opération : '.$e->getMessage());
        }
    }
}
