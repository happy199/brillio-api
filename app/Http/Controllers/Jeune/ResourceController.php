<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

use App\Models\Purchase;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        $userProfile = $user->onboarding_data ?? [];

        $userEducation = $userProfile['education_level'] ?? null;
        $userSituation = $userProfile['current_situation'] ?? null;
        $userInterests = $userProfile['interests'] ?? [];
        $userCountry = $userProfile['country'] ?? null;

        // Récupérer toutes les ressources validées et publiées
        $resources = Resource::where('is_published', true)
            ->where('is_validated', true)
            ->with('user') // Le créateur (Mentor/Admin)
            ->orderByDesc('created_at')
            ->get();

        // Filtrage PHP pour le ciblage
        $filteredResources = $resources->filter(function ($resource) use ($userEducation, $userSituation, $userInterests, $userCountry) {
            $targeting = $resource->targeting;

            // Si pas de ciblage, c'est pour tout le monde
            if (empty($targeting)) {
                return true;
            }

            // Vérification Niveau d'études
            $targetEducations = $targeting['education_levels'] ?? [];
            if (!empty($targetEducations) && $userEducation && !in_array($userEducation, $targetEducations)) {
                return false;
            }

            // Vérification Situation
            $targetSituations = $targeting['situations'] ?? [];
            if (!empty($targetSituations) && $userSituation && !in_array($userSituation, $targetSituations)) {
                return false;
            }

            // Vérification Pays
            $targetCountries = $targeting['countries'] ?? [];
            // Matching flou pour le pays (ex: "Benin" vs "Bénin") ou inclusion
            if (!empty($targetCountries) && $userCountry) {
                // On vérifie si le pays de l'user est dans la liste (simplifié)
                // Idéalement il faudrait normaliser les noms de pays
                $match = false;
                foreach ($targetCountries as $country) {
                    if (str_contains(strtolower($userCountry), strtolower($country))) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    return false;
                }
            }

            // Vérification Intérêts (Au moins un intérêt en commun)
            $targetInterests = $targeting['interests'] ?? [];
            if (!empty($targetInterests) && !empty($userInterests)) {
                $commonInterests = array_intersect($targetInterests, $userInterests);
                if (empty($commonInterests)) {
                    return false;
                }
            }

            return true;
        });

        // Pagination manuelle après filtrage (si nécessaire, ou juste take/slice)
        // Pour l'instant on retourne tout (MVP)

        return view('jeune.resources.index', [
            'resources' => $filteredResources,
            'user' => $user
        ]);
    }

    public function show(Resource $resource)
    {
        // Vérification basique
        if (!$resource->is_published || !$resource->is_validated) {
            abort(404);
        }

        $user = auth()->user();
        $isLocked = false;
        $unlockCost = 0;

        // Logique de verrouillage pour contenu Premium
        if ($resource->is_premium) {
            // Vérifier si l'utilisateur a déjà acheté cette ressource
            $hasPurchased = Purchase::where('user_id', $user->id)
                ->where('item_type', get_class($resource))
                ->where('item_id', $resource->id)
                ->exists();

            if (!$hasPurchased) {
                $isLocked = true;
                // Calcul du coût en crédits
                // Prix Ressource (FCFA) / Prix Crédit Jeune (FCFA)
                $creditPrice = $this->walletService->getCreditPrice('jeune');

                if ($creditPrice > 0) {
                    $unlockCost = (int) ceil($resource->price / $creditPrice);
                } else {
                    $unlockCost = 0; // Fallback ou erreur
                }

                // SECURITÉ : Ne PAS envoyer le contenu au front
                $resource->content = null;
                $resource->file_path = null;
            }
        }

        return view('jeune.resources.show', compact('resource', 'isLocked', 'unlockCost'));
    }

    public function unlock(Request $request, Resource $resource)
    {
        if (!$resource->is_premium) {
            return back();
        }

        $user = auth()->user();

        // Vérifier si déjà acheté
        $exists = Purchase::where('user_id', $user->id)
            ->where('item_type', get_class($resource))
            ->where('item_id', $resource->id)
            ->exists();

        if ($exists) {
            return back()->with('info', 'Vous avez déjà débloqué cette ressource.');
        }

        // Calcul du coût
        $creditPrice = $this->walletService->getCreditPrice('jeune');
        $unlockCost = (int) ceil($resource->price / $creditPrice);

        if ($user->credits_balance < $unlockCost) {
            // Rediriger vers le portefeuille si pas assez de crédits
            return redirect()->route('jeune.wallet.index')->withErrors(['credits' => 'Solde insuffisant pour débloquer cette ressource (' . $unlockCost . ' crédits nécessaires).']);
        }

        try {
            DB::transaction(function () use ($user, $resource, $unlockCost) {
                // 1. Débiter l'user
                $this->walletService->deductCredits(
                    $user,
                    $unlockCost,
                    'expense',
                    "Déblocage ressource : {$resource->title}",
                    $resource
                );

                // 2. Enregistrer l'achat
                Purchase::create([
                    'user_id' => $user->id,
                    'item_type' => get_class($resource),
                    'item_id' => $resource->id,
                    'cost_credits' => $unlockCost,
                    'original_price_fcfa' => $resource->price,
                    'purchased_at' => now(),
                ]);
            });

            return back()->with('success', 'Ressource débloquée avec succès !');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => "Erreur lors du déblocage : " . $e->getMessage()]);
        }
    }
}
