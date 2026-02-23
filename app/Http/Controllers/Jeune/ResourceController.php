<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Resource;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    protected $walletService;

    protected $notificationService;

    // MBTI Groups configuration
    protected $mbtiGroups = [
        'Analystes' => [
            'INTJ' => 'INTJ - Architecte',
            'INTP' => 'INTP - Logicien',
            'ENTJ' => 'ENTJ - Commandant',
            'ENTP' => 'ENTP - Innovateur',
        ],
        'Diplomates' => [
            'INFJ' => 'INFJ - Avocat',
            'INFP' => 'INFP - Médiateur',
            'ENFJ' => 'ENFJ - Protagoniste',
            'ENFP' => 'ENFP - Inspirateur',
        ],
        'Sentinelles' => [
            'ISTJ' => 'ISTJ - Logisticien',
            'ISFJ' => 'ISFJ - Défenseur',
            'ESTJ' => 'ESTJ - Directeur',
            'ESFJ' => 'ESFJ - Consul',
        ],
        'Explorateurs' => [
            'ISTP' => 'ISTP - Virtuose',
            'ISFP' => 'ISFP - Aventurier',
            'ESTP' => 'ESTP - Entrepreneur',
            'ESFP' => 'ESFP - Amuseur',
        ],
    ];

    public function __construct(WalletService $walletService, \App\Services\MentorshipNotificationService $notificationService)
    {
        $this->walletService = $walletService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userProfile = $user->onboarding_data ?? [];

        $userEducation = $userProfile['education_level'] ?? null;
        $userSituation = $userProfile['current_situation'] ?? null;
        $userInterests = $userProfile['interests'] ?? [];
        $userCountry = $userProfile['country'] ?? null;
        // User MBTI (clean up if string contains description)
        $rawMbti = $user->personalityTest?->type_code ?? $userProfile['mbti'] ?? null;
        $userMbti = $rawMbti ? explode(' ', $rawMbti)[0] : null;

        // Mode de filtrage : 'suggestions' (défaut) ou 'all'
        // Si l'utilisateur effectue une recherche ou applique des filtres spécifiques, on bascule en mode 'all' pour ne pas masquer les résultats
        $hasActiveFilters = $request->filled('search') || $request->filled('type') || $request->filled('price') || $request->filled('mbti') || $request->filled('source') || $request->filled('ownership');
        $filterMode = $request->get('filter', $hasActiveFilters ? 'all' : 'suggestions');

        // IDs des ressources acquises ou consultées
        $purchasedIds = Purchase::where('user_id', $user->id)->where('item_type', Resource::class)->pluck('item_id');
        $viewedIds = \App\Models\ResourceView::where('user_id', $user->id)->pluck('resource_id');
        $myResourceIds = $purchasedIds->merge($viewedIds)->unique();

        // Récupérer toutes les ressources validées et publiées
        // ET dont l'auteur (si mentor) a un profil PUBLIÉ
        $query = Resource::where('is_published', true)
            ->where('is_validated', true)
            ->whereHas('user', function ($q) {
                $q->where('is_admin', true) // Les admins sont toujours OK
                    ->orWhereHas('mentorProfile', function ($mp) {
                        $mp->where('is_published', true); // Les mentors doivent être publiés
                    });
            })
            ->with('user') // Le créateur (Mentor/Admin)
            ->orderByDesc('created_at');

        // --- FILTRES GLOBAUX ---

        // 6. Propriété / Consultation (Nouveau vs Déjà vu)
        // Par défaut (sans filtre ownership ou avec ownership='new'), on EXCLUT les ressources déjà vues/acquises
        // Si ownership='mine', on ne garde QUE celles-ci.
        // Option 'all' pour tout voir (y compris déjà vu) si besoin, mais la demande est "continu de ne voir que nouvelles choses".

        $ownership = $request->get('ownership', 'new'); // Default to 'new' if not specified

        if ($ownership === 'mine') {
            $query->whereIn('id', $myResourceIds);
        } elseif ($ownership === 'new') {
            $query->whereNotIn('id', $myResourceIds);
        }
        // else if ownership === 'all' -> do nothing (show everything)

        // 1. Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // 2. Filtres basiques
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // 3. Prix (Gratuit / Payant)
        if ($request->filled('price')) {
            if ($request->price === 'free') {
                $query->where('is_premium', false);
            } elseif ($request->price === 'premium') {
                $query->where('is_premium', true);
            }
        }

        // 4. Personnalité (MBTI)
        // On suppose que le champ mbti_types contient un JSON array des types ciblés
        if ($request->filled('mbti')) {
            $query->where(function ($q) use ($request) {
                $q->whereJsonContains('mbti_types', $request->mbti)
                    ->orWhereNull('mbti_types'); // On inclut ceux sans restrictions ? Non, restons strict si filtre.
            })->whereNotNull('mbti_types'); // On exclut ceux qui n'ont pas de mbti défini si on filtre par mbti
        }

        // 5. Source (Mentor vs Brillio)
        if ($request->filled('source')) {
            if ($request->source === 'mentor') {
                $query->whereHas('user', function ($q) {
                    $q->where('user_type', 'mentor')
                        ->where('is_admin', false);
                });
            } elseif ($request->source === 'brillio') {
                $query->whereHas('user', function ($q) {
                    $q->where('is_admin', true);
                });
            }
        }

        $resources = $query->get();

        // Logique de Suggestion / Filtrage Intelligent
        if ($filterMode === 'suggestions') {
            $resources = $resources->filter(function ($resource) use ($userEducation, $userSituation, $userInterests, $userCountry, $userMbti) {
                $targeting = $resource->targeting;

                // Si pas de ciblage particulier, on garde (sauf si logique "strict" à définir, mais généralement par défaut on garde tout ce qui n'exclut pas)
                // Cependant, on veut vérifier aussi les mbti_types (qui peuvent être hors du targeting json, selon implémentation)
                // On va considérer 'targeting' pour l'instant.

                // Vérification spécifique MBTI (Si la ressource est taguée MBTI)
                // Assumons mbti_types dans le modèle Resource (qui est cast en array)
                if (! empty($resource->mbti_types) && $userMbti) {
                    if (! in_array($userMbti, $resource->mbti_types)) {
                        return false;
                    }
                }

                if (empty($targeting)) {
                    return true;
                }

                $matches = 0;
                $criteriaCount = 0;

                // 1. Éducation
                $targetEducations = $targeting['education_levels'] ?? [];
                if (! empty($targetEducations)) {
                    $criteriaCount++;
                    if ($userEducation && in_array($userEducation, $targetEducations)) {
                        $matches++;
                    }
                }

                // 2. Situation
                $targetSituations = $targeting['situations'] ?? [];
                if (! empty($targetSituations)) {
                    $criteriaCount++;
                    if ($userSituation && in_array($userSituation, $targetSituations)) {
                        $matches++;
                    }
                }

                // 3. Pays (Matching souple)
                $targetCountries = $targeting['countries'] ?? [];
                if (! empty($targetCountries)) {
                    $criteriaCount++;
                    if ($userCountry) {
                        foreach ($targetCountries as $country) {
                            if (str_contains(strtolower($userCountry), strtolower($country))) {
                                $matches++;
                                break;
                            }
                        }
                    }
                }

                // 4. Intérêts (Au moins un commun)
                $targetInterests = $targeting['interests'] ?? [];
                if (! empty($targetInterests)) {
                    $criteriaCount++;
                    if (! empty($userInterests)) {
                        $commonInterests = array_intersect($targetInterests, $userInterests);
                        if (! empty($commonInterests)) {
                            $matches++;
                        }
                    }
                }

                // Logique de scoring pour suggestion :
                // Si la ressource a des critères de ciblage, on l'affiche si l'utilisateur matche au moins UN critère fort (Education/Situation)
                // OU s'il n'y a pas de critère spécifié, c'est pour tout le monde.

                // Ici, on est strict comme avant : si un critère est défini mais ne matche pas, on exclut ?
                // La demande : "afficher ou pas dans les suggestion...".
                // L'ancienne logique excluait si Education ne matchait pas alors que demandé.
                // Gardons une logique "Si ciblé, doit matcher".

                // Vérification Niveau d'études
                if (! empty($targetEducations) && $userEducation && ! in_array($userEducation, $targetEducations)) {
                    return false;
                }
                // Vérification Situation
                if (! empty($targetSituations) && $userSituation && ! in_array($userSituation, $targetSituations)) {
                    return false;
                }
                // Vérification Pays
                if (! empty($targetCountries) && $userCountry) {
                    $match = false;
                    foreach ($targetCountries as $country) {
                        if (str_contains(strtolower($userCountry), strtolower($country))) {
                            $match = true;
                            break;
                        }
                    }
                    if (! $match) {
                        return false;
                    }
                }

                // Vérification Intérêts
                if (! empty($targetInterests) && ! empty($userInterests)) {
                    $commonInterests = array_intersect($targetInterests, $userInterests);
                    if (empty($commonInterests)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Pagination manuelle après filtrage
        // Note: Pour de gros volumes, il faudrait faire le filtrage en SQL (JSON queries), mais pour MVP filtre PHP ok.
        $page = $request->get('page', 1);
        $perPage = 12;
        $items = $resources instanceof \Illuminate\Support\Collection ? $resources : \Illuminate\Support\Collection::make($resources);

        $paginatedResources = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('jeune.resources.index', [
            'resources' => $paginatedResources,
            'user' => $user,
            'currentFilter' => $filterMode,
            'mbtiGroups' => $this->mbtiGroups,
        ]);
    }

    public function show(Resource $resource)
    {
        // Vérification basique
        if (! $resource->is_published || ! $resource->is_validated) {
            abort(404);
        }

        $user = auth()->user();

        // Enregistrer la vue (Compteur global)
        $resource->increment('views_count');

        // Enregistrer la vue unique (si nécessaire pour la logique "déjà vu")
        // Pour les payantes, on peut aussi enregistrer mais "l'acquisition" est définie par l'achat.
        // La demande : "consultée car gratuite".
        if (! $resource->is_premium) {
            \App\Models\ResourceView::firstOrCreate([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
            ]);
        }

        $isLocked = false;
        $unlockCost = 0;

        // Logique de verrouillage pour contenu Premium
        if ($resource->is_premium) {
            // Vérifier si l'utilisateur a déjà acheté cette ressource
            $hasPurchased = Purchase::where('user_id', $user->id)
                ->where('item_type', get_class($resource))
                ->where('item_id', $resource->id)
                ->exists();

            if (! $hasPurchased) {
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
        // Sécurité : Vérifier que la ressource est toujours valide et publiée
        if (! $resource->is_published || ! $resource->is_validated) {
            return redirect()->route('jeune.resources.index')->with('error', 'Cette ressource n\'est plus disponible.');
        }

        if (! $resource->is_premium) {
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
            return redirect()->route('jeune.wallet.index')->withErrors(['credits' => 'Solde insuffisant pour débloquer cette ressource ('.$unlockCost.' crédits nécessaires).']);
        }

        try {
            DB::transaction(function () use ($user, $resource, $unlockCost) {
                // 1. Enregistrer l'achat d'abord (pour avoir l'ID et l'objet)
                $purchase = Purchase::create([
                    'user_id' => $user->id,
                    'item_type' => get_class($resource),
                    'item_id' => $resource->id,
                    'cost_credits' => $unlockCost,
                    'original_price_fcfa' => $resource->price,
                    'purchased_at' => now(),
                ]);

                // 2. Débiter l'user (Jeune)
                // Lié à l'achat pour traçabilité
                $this->walletService->deductCredits(
                    $user,
                    $unlockCost,
                    'expense',
                    "Déblocage ressource : {$resource->title}",
                    $purchase // On lie à l'achat
                );

                // 3. Créditer le créateur (Mentor)
                // On crédite 100% pour l'instant
                $mentor = $resource->user;
                if ($mentor) {
                    // IMPORTANT : Convertir le prix FCFA en crédits MENTOR (pas les crédits jeune)
                    // Prix ressource = 300 FCFA → Jeune paie 6 crédits (300/50)
                    // Mentor reçoit 3 crédits mentor (300/100)
                    $mentorCreditPrice = $this->walletService->getCreditPrice('mentor');
                    $mentorCredits = (int) ceil($resource->price / $mentorCreditPrice);

                    $this->walletService->addCredits(
                        $mentor,
                        $mentorCredits, // Crédits mentor (basé sur prix FCFA)
                        'income', // Type spécifique pour les revenus
                        "Achat par {$user->name} de : {$resource->title}",
                        $purchase // On lie à l'achat (qui contient l'info de l'acheteur via user_id)
                    );

                    // 4. Notifier le mentor de la vente
                    $this->notificationService->sendResourcePurchased($resource, $user, $mentorCredits);
                }

                // 5. Incrémenter le compteur de ventes
                $resource->increment('sales_count');
            });

            return back()->with('success', 'Ressource débloquée avec succès !');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du déblocage : '.$e->getMessage()]);
        }
    }
}
