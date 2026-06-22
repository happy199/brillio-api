<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\ResourceController as V1ResourceController;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des ressources d'orientation via API
 */
class ResourceController extends V1ResourceController
{
    public function __construct(
        private WalletService $walletService,
        private \App\Services\MentorshipNotificationService $notificationService
    ) {
        parent::__construct($walletService, $notificationService);
    }

    /**
     * @OA\Get(
     * path="/api/v2/resources",
     * summary="Liste les ressources pédagogiques",
     * tags={"Ressources"},
     *
     * @OA\Parameter(name="filter", in="query", @OA\Schema(type="string", enum={"suggestions", "all"})),
     * @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="type", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="price", in="query", @OA\Schema(type="string", enum={"free", "premium"})),
     * @OA\Parameter(name="mbti", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="source", in="query", @OA\Schema(type="string", enum={"mentor", "brillio"})),
     * @OA\Parameter(name="ownership", in="query", @OA\Schema(type="string", enum={"mine", "all"})),
     *
     * @OA\Response(response= 200, description="Liste des ressources"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/resources/{id}",
     * summary= "Détails d'une ressource pédagogique",
     * tags={"Ressources"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Détails de la ressource"),
     * @OA\Response(response= 404, description="Ressource non trouvée"),
     * )
     */
    public function show(int $id, Request $request): JsonResponse
    {
        return parent::show($id, $request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/resources/{id}/unlock",
     * summary="Débloquer une ressource premium",
     * tags={"Ressources"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Ressource débloquée"),
     * @OA\Response(response= 400, description="Crédits insuffisants"),
     * @OA\Response(response= 404, description="Ressource non trouvée"),
     * )
     */
    public function unlock(int $id): JsonResponse
    {
        return parent::unlock($id);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/resources",
     *     summary="Créer une ressource pédagogique (Mentor)",
     *     tags={"Ressources"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"title", "description", "type", "price"},
     *
     *                 @OA\Property(property="title", type="string", example="Mon document de conseils"),
     *                 @OA\Property(property="description", type="string", example="Description de la ressource"),
     *                 @OA\Property(property="type", type="string", enum={"article", "video", "tool", "exercise", "template", "script"}, example="article"),
     *                 @OA\Property(property="price", type="number", example=10),
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="external_url", type="string", format="uri")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Ressource créée avec succès")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $mentor = $request->user();

        // Limiter la taille totale du payload pour éviter les attaques DoS (Règle SonarQube S5693)
        if ($request->header('Content-Length') > 60 * 1024 * 1024) { // 60Mo max
            return $this->error('Taille de la requête trop volumineuse (max 60Mo).', 413);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:article,video,tool,exercise,template,script',
            'price' => 'required|numeric|min:0',
            'file' => 'nullable|file|max:51200',
            'external_url' => 'nullable|url',
        ]);

        $resourceData = $request->only(['title', 'description', 'type', 'price', 'external_url']);
        $resourceData['user_id'] = $mentor->id;
        $resourceData['is_active'] = true;

        if ($request->hasFile('file')) {
            $resourceData['file_path'] = $request->file('file')->store('resources', 'public');
        }

        $resource = \App\Models\Resource::create($resourceData);

        return $this->success($resource, 'Ressource créée avec succès.');
    }

    /**
     * @OA\Put(
     *     path="/api/v2/resources/{id}",
     *     summary="Mettre à jour une ressource pédagogique (Mentor)",
     *     tags={"Ressources"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Ressource mise à jour avec succès")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $resource = \App\Models\Resource::findOrFail($id);

        if ($resource->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $resource->update($request->only(['title', 'description', 'price', 'is_active']));

        return $this->success($resource, 'Ressource mise à jour avec succès.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/resources/{id}",
     *     summary="Désactiver/Supprimer une ressource pédagogique (Mentor)",
     *     tags={"Ressources"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Ressource désactivée avec succès")
     * )
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $resource = \App\Models\Resource::findOrFail($id);

        if ($resource->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        // Logical delete or check if it has been bought
        $resource->update(['is_active' => false]);

        return $this->success(null, 'Ressource désactivée avec succès.');
    }
}
