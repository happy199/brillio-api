<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Resource;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use App\Services\WalletService;
use App\Traits\ManagesQuizzes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    use ManagesQuizzes;

    public function __construct(protected WalletService $walletService) {}

    /**
     * List all resources (internal created by this organization, and external unless hidden).
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        // If organization hides external resources, force tab to internal
        $tab = $request->get('tab', $organization->hide_external_resources ? 'internal' : 'all');
        if ($organization->hide_external_resources) {
            $tab = 'internal';
        }

        $query = Resource::where('is_published', true)
            ->where('is_validated', true);

        if ($tab === 'internal') {
            $query->where('organization_id', $organization->id);
        } elseif ($tab === 'external') {
            if ($organization->hide_external_resources) {
                // Return empty if external resources are hidden
                $query->whereRaw('1 = 0');
            } else {
                $query->whereNull('organization_id')
                    ->whereHas('user', function ($q) {
                        $q->where('is_admin', true)
                            ->orWhereHas('mentorProfile', fn ($mp) => $mp->where('is_published', true));
                    });
            }
        } else {
            // 'all': internal resources OR external resources (if not hidden)
            if ($organization->hide_external_resources) {
                $query->where('organization_id', $organization->id);
            } else {
                $query->where(function ($q) use ($organization) {
                    $q->where('organization_id', $organization->id)
                        ->orWhere(function ($q2) {
                            $q2->whereNull('organization_id')
                                ->whereHas('user', function ($u) {
                                    $u->where('is_admin', true)
                                        ->orWhereHas('mentorProfile', fn ($mp) => $mp->where('is_published', true));
                                });
                        });
                });
            }
        }

        $query->with(['user', 'organization'])->orderByDesc('created_at');

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

        // Counts for tabs
        $internalCount = Resource::where('organization_id', $organization->id)->count();
        $externalCount = $organization->hide_external_resources ? 0 : Resource::where('is_published', true)
            ->where('is_validated', true)
            ->whereNull('organization_id')
            ->whereHas('user', function ($q) {
                $q->where('is_admin', true)
                    ->orWhereHas('mentorProfile', fn ($mp) => $mp->where('is_published', true));
            })->count();

        // IDs déjà offerts par l'org (pour badge "déjà offert")
        $giftedIds = Purchase::where('gifted_by_organization_id', $organization->id)
            ->pluck('item_id')
            ->unique();

        return view('organization.resources.index', compact(
            'resources',
            'organization',
            'giftedIds',
            'tab',
            'internalCount',
            'externalCount'
        ));
    }

    /**
     * Show the form for creating a new internal organization resource.
     */
    public function create()
    {
        $organization = $this->getCurrentOrganization();

        return view('organization.resources.create', compact('organization'));
    }

    /**
     * Store a newly created organization resource.
     */
    public function store(Request $request)
    {
        if ($request->header('Content-Length') > 30 * 1024 * 1024) { // 30MB max
            return back()->with('error', 'La taille de la requête est trop volumineuse (max 30 Mo).');
        }

        $organization = $this->getCurrentOrganization();

        $messages = [
            'required' => 'Ce champ est obligatoire.',
            'string' => 'Ce champ doit être une chaîne de caractères.',
            'max' => 'La taille ne doit pas dépasser :max.',
            'in' => 'La valeur sélectionnée est invalide.',
            'integer' => 'Ce champ doit être un entier.',
            'min' => 'La valeur doit être au moins :min.',
            'file' => 'Le fichier doit être valide.',
            'image' => 'Le fichier doit être une image.',
            'file.max' => 'Le fichier est trop volumineux (Max 20 Mo).',
            'preview_image.max' => 'L\'image de couverture est trop volumineuse (Max 5 Mo).',
        ];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement,book,podcast,webinar,guide,case_study,course',
            'price' => 'nullable|integer',
            'is_premium' => 'required|in:0,1',
            'file' => 'nullable|file|max:20480', // 20MB
            'preview_image' => 'nullable|image|max:5120', // 5MB
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string',
            'targeting' => 'nullable|array',
            'quizzes_data' => 'nullable|string',
        ], $messages);

        if ($request->is_premium == '1') {
            $request->validate([
                'price' => 'required|integer|min:200',
            ], [
                'price.required' => 'Le prix est obligatoire pour une ressource payante.',
                'price.min' => 'Le prix minimum pour une ressource payante est de 200 FCFA.',
            ]);
        }

        // File handling
        $filePath = null;
        if (isset($validated['file'])) {
            $filePath = $validated['file']->store('resources/files', 'public');
        }

        $previewPath = null;
        if (isset($validated['preview_image'])) {
            $previewPath = $validated['preview_image']->store('resources/previews', 'public');
        }

        $tags = ! empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        // Must have at least content, file, or quizzes
        $hasQuizzes = false;
        if (! empty($validated['quizzes_data'])) {
            $quizzesDecoded = json_decode($validated['quizzes_data'], true);
            if (is_array($quizzesDecoded) && count($quizzesDecoded) > 0) {
                foreach ($quizzesDecoded as $qData) {
                    if (! empty($qData['title'])) {
                        $hasQuizzes = true;
                        break;
                    }
                }
            }
        }

        if (empty($validated['content']) && ! $request->hasFile('file') && ! $hasQuizzes) {
            return back()->withInput()->withErrors(['content' => 'Vous devez fournir au moins un contenu texte, un fichier joint ou un quiz.']);
        }

        try {
            DB::beginTransaction();

            $resource = Resource::create([
                'user_id' => auth()->id(),
                'organization_id' => $organization->id,
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']).'-'.uniqid(),
                'description' => $validated['description'],
                'content' => $validated['content'] ?? null,
                'type' => $validated['type'],
                'price' => $request->is_premium == '1' ? ($request->price ?? 0) : 0,
                'is_premium' => $request->is_premium == '1',
                'file_path' => $filePath,
                'preview_image_path' => $previewPath,
                'metadata' => $validated['metadata'] ?? [],
                'mbti_types' => $validated['mbti_types'] ?? [],
                'tags' => $tags,
                'targeting' => $validated['targeting'] ?? [],
                'is_published' => true,
                'is_validated' => true,
                'validated_at' => now(),
                'admin_feedback' => null,
                'unpublished_at' => null,
            ]);

            // Save quizzes
            $this->saveQuizzes($resource, $validated['quizzes_data'] ?? null);

            DB::commit();

            return redirect()->route('organization.resources.index', ['tab' => 'internal'])
                ->with('success', 'Ressource interne créée et publiée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Erreur lors de la création : '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing an internal organization resource.
     */
    public function edit(Resource $resource)
    {
        $organization = $this->getCurrentOrganization();

        if (! $resource->isInternalTo($organization)) {
            abort(403, 'Vous ne pouvez modifier que les ressources internes créées par votre organisation.');
        }

        return view('organization.resources.edit', compact('resource', 'organization'));
    }

    /**
     * Update an internal organization resource.
     */
    public function update(Request $request, Resource $resource)
    {
        $organization = $this->getCurrentOrganization();

        if (! $resource->isInternalTo($organization)) {
            abort(403, 'Vous ne pouvez modifier que les ressources internes créées par votre organisation.');
        }

        if ($request->header('Content-Length') > 30 * 1024 * 1024) {
            return back()->with('error', 'La taille de la requête est trop volumineuse (max 30 Mo).');
        }

        $messages = [
            'required' => 'Ce champ est obligatoire.',
            'string' => 'Ce champ doit être une chaîne de caractères.',
            'max' => 'La taille ne doit pas dépasser :max.',
            'in' => 'La valeur sélectionnée est invalide.',
            'integer' => 'Ce champ doit être un entier.',
            'min' => 'La valeur doit être au moins :min.',
            'file' => 'Le fichier doit être valide.',
            'image' => 'Le fichier doit être une image.',
            'file.max' => 'Le fichier est trop volumineux (Max 20 Mo).',
            'preview_image.max' => 'L\'image de couverture est trop volumineuse (Max 5 Mo).',
        ];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement,book,podcast,webinar,guide,case_study,course',
            'price' => 'nullable|integer',
            'is_premium' => 'required|in:0,1',
            'file' => 'nullable|file|max:20480',
            'preview_image' => 'nullable|image|max:5120',
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string',
            'targeting' => 'nullable|array',
            'quizzes_data' => 'nullable|string',
        ], $messages);

        if ($request->is_premium == '1') {
            $request->validate([
                'price' => 'required|integer|min:200',
            ], [
                'price.required' => 'Le prix est obligatoire pour une ressource payante.',
                'price.min' => 'Le prix minimum pour une ressource payante est de 200 FCFA.',
            ]);
        }

        if ($request->hasFile('preview_image')) {
            if ($resource->preview_image_path) {
                Storage::disk('public')->delete($resource->preview_image_path);
            }
            $resource->preview_image_path = $request->file('preview_image')->store('resources/previews', 'public');
        }

        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $resource->file_path = $request->file('file')->store('resources/files', 'public');
        }

        $tags = ! empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        $hasQuizzes = false;
        if ($request->has('quizzes_data')) {
            $quizzesDecoded = json_decode($request->quizzes_data, true);
            if (is_array($quizzesDecoded) && count($quizzesDecoded) > 0) {
                foreach ($quizzesDecoded as $qData) {
                    if (! empty($qData['title'])) {
                        $hasQuizzes = true;
                        break;
                    }
                }
            }
        } elseif ($resource->quizzes()->count() > 0) {
            $hasQuizzes = true;
        }

        $hasContent = ! empty($validated['content']) || (! array_key_exists('content', $validated) && ! empty($resource->content));
        $hasFile = $request->hasFile('file') || (! empty($resource->file_path) && ! $request->has('remove_file'));

        if (! $hasContent && ! $hasFile && ! $hasQuizzes) {
            return back()->withInput()->withErrors(['content' => 'Vous devez fournir au moins un contenu texte, un fichier joint ou un quiz.']);
        }

        try {
            DB::beginTransaction();

            $resource->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'content' => $validated['content'] ?? null,
                'type' => $validated['type'],
                'price' => $request->is_premium == '1' ? ($request->price ?? 0) : 0,
                'is_premium' => $request->is_premium == '1',
                'metadata' => $validated['metadata'] ?? [],
                'mbti_types' => $validated['mbti_types'] ?? [],
                'tags' => $tags,
                'targeting' => $validated['targeting'] ?? [],
                'is_published' => true,
                'is_validated' => true,
                'validated_at' => $resource->validated_at ?? now(),
                'admin_feedback' => null,
                'unpublished_at' => null,
            ]);

            if ($request->has('quizzes_data')) {
                $this->saveQuizzes($resource, $request->quizzes_data);
            }

            DB::commit();

            return redirect()->route('organization.resources.index', ['tab' => 'internal'])
                ->with('success', 'Ressource mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : '.$e->getMessage());
        }
    }

    /**
     * Delete an internal organization resource.
     */
    public function destroy(Resource $resource)
    {
        $organization = $this->getCurrentOrganization();

        if (! $resource->isInternalTo($organization)) {
            abort(403, 'Vous ne pouvez supprimer que les ressources internes créées par votre organisation.');
        }

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        if ($resource->preview_image_path) {
            Storage::disk('public')->delete($resource->preview_image_path);
        }

        $resource->delete();

        return redirect()->route('organization.resources.index', ['tab' => 'internal'])
            ->with('success', 'Ressource supprimée avec succès.');
    }

    /**
     * Show a single resource with the gift modal or internal view.
     */
    public function show(Resource $resource)
    {
        $organization = $this->getCurrentOrganization();

        // If external resources are hidden, ensure resource is internal to this organization
        if ($organization->hide_external_resources && ! $resource->isInternalTo($organization)) {
            abort(404);
        }

        if (! $resource->is_published || ! $resource->is_validated) {
            abort(404);
        }

        $isInternal = $resource->isInternalTo($organization);

        // Credit cost per young person
        $creditCost = 0;
        $isLocked = false;
        if (! $isInternal && $resource->is_premium) {
            $isLocked = true;
            $creditPrice = $this->walletService->getCreditPrice('jeune');
            $creditCost = $creditPrice > 0 ? (int) ceil($resource->price / $creditPrice) : 0;

            // Security: don't show content or file for premium resources to organizations
            $resource->content = null;
            $resource->file_path = null;
        }

        if ($isInternal) {
            $resource->load(['quizzes']);
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
            'isLocked',
            'isInternal'
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
                    $purchase = Purchase::create([
                        'user_id' => $jeune->id,
                        'item_type' => Resource::class,
                        'item_id' => $resource->id,
                        'cost_credits' => $costPerJeune,
                        'original_price_fcfa' => $resource->price,
                        'gifted_by_organization_id' => $organization->id,
                        'purchased_at' => now(),
                    ]);

                    if ($mentor && $mentorCreditsPerSale > 0) {
                        $this->walletService->addCredits(
                            $mentor,
                            $mentorCreditsPerSale,
                            'income',
                            "Offert par {$organization->name} à {$jeune->name} : {$resource->title}",
                            $purchase
                        );
                    }

                    DB::afterCommit(function () use ($jeune, $resource, $organization) {
                        app(MentorshipNotificationService::class)->sendResourceGiftedNotification($jeune, $resource, $organization);
                    });
                }

                $resource->increment('sales_count', $validJeunes->count());
            });

            return back()->with('success', "Ressource offerte à {$validJeunes->count()} jeune(s) avec succès ! ({$totalCost} crédits débités)");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'opération : '.$e->getMessage());
        }
    }
}
