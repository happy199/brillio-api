<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\AcademicDocumentController as V1AcademicDocumentController;
use App\Http\Requests\Academic\UploadDocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des documents académiques
 */
class AcademicDocumentController extends V1AcademicDocumentController
{
    private const MSG_DOCUMENT_NOT_FOUND = 'Document non trouvé';

    /**
     * @OA\Get(
     *     path="/api/v2/documents",
     *     summary="Liste les documents académiques de l'utilisateur",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des documents récupérée avec succès"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/documents",
     *     summary="Téléverse un nouveau document académique",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"file", "type"},
     *
     *                 @OA\Property(property="file", type="string", format="binary", description="Fichier du document (PDF, JPG, PNG, DOC, DOCX, max 5Mo)"),
     *                 @OA\Property(property="type", type="string", description="Type de document (ex: diplome, bulletin, cv, autre)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Document téléversé avec succès"
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation ou fichier invalide")
     * )
     */
    public function upload(UploadDocumentRequest $request): JsonResponse
    {
        return parent::upload($request);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/documents/{id}",
     *     summary="Récupère les détails d'un document académique",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du document",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détails du document récupérés avec succès"
     *     ),
     *     @OA\Response(response=404, description="Document non trouvé")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return parent::show($request, $id);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/documents/{id}/download",
     *     summary="Télécharge le fichier d'un document académique",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du document",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Téléchargement du fichier démarré",
     *
     *         @OA\MediaType(mediaType="application/octet-stream")
     *     ),
     *
     *     @OA\Response(response=404, description="Document ou fichier non trouvé")
     * )
     */
    public function download(Request $request, int $id)
    {
        return parent::download($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/documents/{id}",
     *     summary="Supprime un document académique",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du document",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Document supprimé avec succès"
     *     ),
     *     @OA\Response(response=404, description="Document non trouvé")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return parent::destroy($request, $id);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/document-types",
     *     summary="Récupère les types de documents acceptés",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des types récupérée avec succès"
     *     )
     * )
     */
    public function types(): JsonResponse
    {
        return parent::types();
    }
}
