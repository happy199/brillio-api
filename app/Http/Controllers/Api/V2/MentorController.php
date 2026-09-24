<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\MentorController as V1MentorController;
use App\Http\Requests\Mentor\CreateProfileRequest;
use App\Http\Requests\Mentor\CreateRoadmapStepRequest;
use App\Http\Requests\Mentor\ImportLinkedInPdfRequest;
use App\Http\Requests\Mentor\UpdateRoadmapStepRequest;
use App\Models\MentorProfile;
use App\Services\MentorLinkedInImportService;
use App\Traits\FormatsUrls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la recherche et consultation des mentors via API
 */
class MentorController extends V1MentorController
{
    use FormatsUrls;
    /**
     * @OA\Get(
     * path="/api/v2/mentors",
     * summary="Liste des mentors publiés",
     * tags={"Mentors"},
     *
     * @OA\Parameter(name="specialization", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="country", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Liste des mentors"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/mentors/{id}",
     * summary= "Détail d'un mentor",
     * tags={"Mentors"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Détail du mentor"),
     * @OA\Response(response= 404, description="Mentor non trouvé"),
     * )
     */
    public function show(int $id): JsonResponse
    {
        return parent::show($id);
    }

    /**
     * @OA\Post(
     * path="/api/v2/mentor/profile",
     * summary="Crée ou met à jour le profil mentor",
     * tags={"Mentors"},
     *
     * @OA\Response(response= 200, description="Profil mis à jour"),
     * )
     */
    public function createOrUpdateProfile(CreateProfileRequest $request): JsonResponse
    {
        return parent::createOrUpdateProfile($request);
    }

    /**
     * Récupère le profil mentor de l'utilisateur connecté
     */
    public function myProfile(Request $request): JsonResponse
    {
        return parent::myProfile($request);
    }

    /**
     * Ajoute une étape au parcours
     */
    public function addRoadmapStep(CreateRoadmapStepRequest $request): JsonResponse
    {
        return parent::addRoadmapStep($request);
    }

    /**
     * Met à jour une étape du parcours
     */
    public function updateRoadmapStep(UpdateRoadmapStepRequest $request, int $stepId): JsonResponse
    {
        return parent::updateRoadmapStep($request, $stepId);
    }

    /**
     * Supprime une étape du parcours
     */
    public function deleteRoadmapStep(Request $request, int $stepId): JsonResponse
    {
        return parent::deleteRoadmapStep($request, $stepId);
    }

    /**
     * Réorganise les étapes du parcours
     */
    public function reorderSteps(Request $request): JsonResponse
    {
        return parent::reorderSteps($request);
    }

    /**
     * Publie ou dépublie le profil mentor
     */
    public function publish(Request $request): JsonResponse
    {
        return parent::publish($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/specializations",
     * summary="Liste des spécialisations disponibles",
     * tags={"Mentors"},
     *
     * @OA\Response(response= 200, description="Liste des spécialisations"),
     * )
     */
    public function specializations(): JsonResponse
    {
        return parent::specializations();
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentor/profile/import-linkedin",
     *     summary="Importe les données du profil mentor depuis un export PDF LinkedIn",
     *     tags={"Mentors"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"pdf"},
     *                 @OA\Property(property="pdf", type="string", format="binary", description="Export PDF LinkedIn (max 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil importé avec succès"),
     *     @OA\Response(response=403, description="Accès réservé aux mentors"),
     *     @OA\Response(response=422, description="Erreur de validation ou profil non correspondant"),
     *     @OA\Response(response=500, description="Erreur de traitement du PDF")
     * )
     */
    public function importLinkedIn(ImportLinkedInPdfRequest $request, MentorLinkedInImportService $importService): JsonResponse
    {
        $user = $request->user();

        if (! $user->isMentor()) {
            return $this->forbidden('Seuls les mentors peuvent importer un profil LinkedIn');
        }

        $validated = $request->validated();
        $result = $importService->import($validated['pdf'], $user);

        if (! $result['success']) {
            return $this->error($result['error'] ?? 'Erreur lors de l\'import', $result['code'] ?? 422);
        }

        return $this->success($result['data'], $result['message']);
    }
}
