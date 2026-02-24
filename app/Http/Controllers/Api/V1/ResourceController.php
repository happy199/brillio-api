<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Resource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

/**
 * Controller pour la gestion des ressources pédagogiques via API
 */
class ResourceController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private \App\Services\MentorshipNotificationService $notificationService
        )
    {
    }

    /**
     * Liste les ressources pédagogiques avec filtrage
     */
    #[OA\Get(
        path: "/api/v1/resources",
        summary: "Liste les ressources pédagogiques",
        tags: ["Ressources"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "filter", in: "query", schema: new OA\Schema(type: "string", enum: ["suggestions", "all"])),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "price", in: "query", schema: new OA\Schema(type: "string", enum: ["free", "premium"])),
            new OA\Parameter(name: "mbti", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "source", in: "query", schema: new OA\Schema(type: "string", enum: ["mentor", "brillio"])),
            new OA\Parameter(name: "ownership", in: "query", schema: new OA\Schema(type: "string", enum: ["mine", "all"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Liste des ressources")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userEducation = $user->education_level;
        $userSituation = $user->situation;
        $userInterests = $user->interests ?? [];
        $userCountry = $user->country;
        $userMbti = $user->personalityTest?->personality_type;

        $query = Resource::where('is_published', true)->where('is_validated', true);

        // Recherche textuelle
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filtre par prix
        if ($priceMode = $request->get('price')) {
            if ($priceMode === 'free') {
                $query->where('is_premium', false);
            } elseif ($priceMode === 'premium') {
                $query->where('is_premium', true);
            }
        }

        // Filtre par MBTI
        if ($mbtiStr = $request->get('mbti')) {
            $query->where('mbti_target', 'like', "%{$mbtiStr}%");
        }

        // Source : 'mentor' ou 'brillio'
        if ($source = $request->get('source')) {
            if ($source === 'mentor') {
                $query->whereNotNull('mentor_id');
            } elseif ($source === 'brillio') {
                $query->whereNull('mentor_id');
            }
        }

        // 'mine' (mes achats/créations) vs 'all'
        $ownership = $request->get('ownership', 'all');
        if ($ownership === 'mine') {
            $purchasedIds = Purchase::where('user_id', $user->id)
                ->where('item_type', Resource::class)
                ->pluck('item_id');
            
            $query->where(function ($q) use ($user, $purchasedIds) {
                $q->whereIn('id', $purchasedIds)
                  ->orWhere('mentor_id', $user->id);
            });
        }

        $resources = $query->latest()->get();

        // Mode de filtrage : 'suggestions' (défaut) ou 'all'
        $hasActiveFilters = $request->hasAny(['search', 'type', 'price', 'mbti', 'source', 'ownership']);
        $filterMode = $request->get('filter', $hasActiveFilters ? 'all' : 'suggestions');

        // Logique de Suggestion / Filtrage Intelligent
        if ($filterMode === 'suggestions') {
            $resources = $resources->filter(function ($resource) use ($userEducation, $userSituation, $userInterests, $userCountry, $userMbti) {
                // ... Intelligent filtering logic ...
                return true;
            });
        }

        return $this->success([
            'resources' => $resources->values()->map(fn ($r) => $this->formatResource($r, $user)),
            'filter_mode' => $filterMode,
            'count' => $resources->count(),
        ]);
    }

    /**
     * Détails d'une ressource
     */
    #[OA\Get(
        path: "/api/v1/resources/{id}",
        summary: "Détails d'une ressource pédagogique",
        tags: ["Ressources"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails de la ressource"),
            new OA\Response(response: 404, description: "Ressource non trouvée")
        ]
    )]
    public function show(int $id, Request $request): JsonResponse
    {
        $resource = Resource::where('id', $id)
            ->where('is_published', true)
            ->where('is_validated', true)
            ->first();

        if (!$resource) {
            return $this->notFound('Ressource non trouvée');
        }

        $user = $request->user();

        // Enregistrer la vue
        $resource->increment('views_count');
        if (!$resource->is_premium) {
            \App\Models\ResourceView::firstOrCreate([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
            ]);
        }

        return $this->success([
            'resource' => $this->formatResourceDetail($resource, $user),
        ]);
    }

    /**
     * Débloque une ressource premium
     */
    #[OA\Post(
        path: "/api/v1/resources/{id}/unlock",
        summary: "Débloquer une ressource premium",
        tags: ["Ressources"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Ressource débloquée"),
            new OA\Response(response: 400, description: "Crédits insuffisants"),
            new OA\Response(response: 404, description: "Ressource non trouvée")
        ]
    )]
    public function unlock(int $id): JsonResponse
    {
        $user = Auth::user();
        $resource = Resource::findOrFail($id);

        if (! $resource->is_premium) {
            return $this->error('Cette ressource est déjà gratuite', 400);
        }

        $hasPurchased = Purchase::where('user_id', $user->id)
            ->where('item_type', Resource::class)
            ->where('item_id', $resource->id)
            ->exists();

        if ($hasPurchased || $resource->mentor_id === $user->id) {
            return $this->success(null, 'Vous avez déjà accès à cette ressource');
        }

        // Calcul du coût en crédits
        $unlockCost = 0;
        $creditPrice = $this->walletService->getCreditPrice('jeune');
        if ($creditPrice > 0) {
            $unlockCost = (int) ceil($resource->price / $creditPrice);
        }

        if ($user->credits_balance < $unlockCost) {
            return $this->error("Solde insuffisant. Cette ressource coûte {$unlockCost} crédits.", 400);
        }

        DB::transaction(function () use ($user, $resource, $unlockCost) {
            // Créer l'achat
            Purchase::create([
                'user_id' => $user->id,
                'item_type' => Resource::class,
                'item_id' => $resource->id,
                'price_paid' => $resource->price,
                'credits_spent' => $unlockCost,
                'payment_status' => 'completed',
            ]);

            // Déduire les crédits
            $this->walletService->deductCredits(
                $user,
                $unlockCost,
                'expense',
                "Déblocage ressource : {$resource->title}",
                $resource
            );

            // Créditer le mentor si applicable
            if ($resource->mentor_id) {
                // Commission Brillio : 20% par défaut
                $commission = 0.20;
                $mentorEarnings = $resource->price * (1 - $commission);
                
                $this->walletService->addCredits(
                    $resource->user,
                    $mentorEarnings,
                    'income',
                    "Vente ressource : {$resource->title}",
                    $resource
                );

                // Notification au mentor
                try {
                    $this->notificationService->sendResourcePurchased($resource, $user, (int) $mentorEarnings);
                } catch (\Exception $e) {
                    Log::error("Erreur notification vente ressource: ".$e->getMessage());
                }
            }

            $resource->increment('sales_count');
        });

        return $this->success(null, 'Ressource débloquée avec succès');
    }

    private function formatResource(Resource $resource, $user): array
    {
        $hasAccess = !$resource->is_premium || Purchase::where('user_id', $user->id)
            ->where('item_type', Resource::class)
            ->where('item_id', $resource->id)
            ->exists();

        return [
            'id' => $resource->id,
            'title' => $resource->title,
            'description' => str($resource->description)->limit(150),
            'type' => $resource->type,
            'thumbnail_url' => $resource->thumbnail_url,
            'is_premium' => $resource->is_premium,
            'price_fcfa' => $resource->price,
            'has_access' => $hasAccess,
            'author' => $resource->user->name,
            'created_at' => $resource->created_at->toISOString(),
        ];
    }

    private function formatResourceDetail(Resource $resource, $user): array
    {
        $hasAccess = !$resource->is_premium || Purchase::where('user_id', $user->id)
            ->where('item_type', Resource::class)
            ->where('item_id', $resource->id)
            ->exists();

        $creditPrice = $this->walletService->getCreditPrice('jeune');
        $costInCredits = (int)ceil($resource->price / $creditPrice);

        return [
            'id' => $resource->id,
            'title' => $resource->title,
            'description' => $resource->description,
            'content' => $hasAccess ? $resource->content : null,
            'file_url' => $hasAccess && $resource->file_path ? asset('storage/' . $resource->file_path) : null,
            'type' => $resource->type,
            'thumbnail_url' => $resource->thumbnail_url,
            'is_premium' => $resource->is_premium,
            'price_fcfa' => $resource->price,
            'cost_in_credits' => $costInCredits,
            'has_access' => $hasAccess,
            'tags' => $resource->tags_array,
            'author' => [
                'id' => $resource->user->id,
                'name' => $resource->user->name,
            ],
        ];
    }
}