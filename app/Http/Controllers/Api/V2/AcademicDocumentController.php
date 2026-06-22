<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\UploadDocumentRequest;
use App\Models\AcademicDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des documents académiques
 */
class AcademicDocumentController extends Controller
{
    /**
     * Taille maximale en bytes (5 Mo par défaut)
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Types MIME autorisés
     */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * @OA\Get(
     * path="/api/v2/documents",
     * summary= "Liste les documents de l'utilisateur",
     * tags={"Documents"},
     *
     * @OA\Response(response= 200, description="Liste des documents"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $documents = $user->academicDocuments()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'documents' => $documents->map(fn ($doc) => $this->formatDocument($doc)),
            'total_count' => $documents->count(),
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/v2/documents",
     * summary="Upload un nouveau document",
     * tags={"Documents"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     *
     * @OA\Schema(
     * required={"file", "document_type"},
     *
     * @OA\Property(property="file", type="string", format="binary"),
     * @OA\Property(property="document_type", type="string", example="bulletin"),
     * @OA\Property(property="academic_year", type="string", example="2023-2024"),
     * @OA\Property(property="grade_level", type="string", example="Terminale"),
     * )
     * )
     * ),
     *
     * @OA\Response(response= 201, description="Document uploadé"),
     * )
     */
    public function upload(UploadDocumentRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $file = $request->file('file');

        // Validation de la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->error('Le fichier dépasse la taille maximale autorisée (5 Mo)', 422);
        }

        // Validation du type MIME
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, self::ALLOWED_MIMES)) {
            return $this->error('Type de fichier non autorisé', 422);
        }

        // Stocker le fichier dans un dossier sécurisé (hors public)
        $path = $file->store("academic_documents/{$user->id}", 'local');

        $document = AcademicDocument::create([
            'user_id' => $user->id,
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'academic_year' => $validated['academic_year'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'uploaded_at' => now(),
        ]);

        return $this->created([
            'document' => $this->formatDocument($document),
        ], 'Document uploadé avec succès');
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
        $user = $request->user();

        $document = $user->academicDocuments()->find($id);

        if (! $document) {
            return $this->notFound('Document non trouvé');
        }

        return $this->success([
            'document' => $this->formatDocument($document),
        ]);
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
        $user = $request->user();

        // Les admins peuvent télécharger tous les documents
        if ($user->isAdmin()) {
            $document = AcademicDocument::find($id);
        } else {
            $document = $user->academicDocuments()->find($id);
        }

        if (! $document) {
            return $this->notFound('Document non trouvé');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            return $this->error('Fichier introuvable sur le serveur', 500);
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name
        );
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
        $user = $request->user();

        $document = $user->academicDocuments()->find($id);

        if (! $document) {
            return $this->notFound('Document non trouvé');
        }

        // Le modèle supprime automatiquement le fichier via l'event deleting
        $document->delete();

        return $this->success(null, 'Document supprimé avec succès');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/document-types",
     *     summary="Liste les types de documents autorisés",
     *     tags={"Documents"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des types autorisés récupérée avec succès"
     *     )
     * )
     */
    public function types(): JsonResponse
    {
        return $this->success([
            'document_types' => AcademicDocument::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Formate un document pour la réponse API
     */
    private function formatDocument(AcademicDocument $document): array
    {
        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_type_label' => $document->document_type_label,
            'file_name' => $document->file_name,
            'file_size' => $document->file_size,
            'formatted_size' => $document->formatted_size,
            'mime_type' => $document->mime_type,
            'academic_year' => $document->academic_year,
            'grade_level' => $document->grade_level,
            'uploaded_at' => $document->uploaded_at?->toISOString(),
            'created_at' => $document->created_at->toISOString(),
        ];
    }
}
