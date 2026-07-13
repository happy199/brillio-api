<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\Message;
use App\Services\ContentModerator;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * Controller pour la messagerie instantanée via API
 */
class MessagesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/messages",
     *     summary="Liste des conversations actives de l'utilisateur",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Liste des conversations")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $mentorships = Mentorship::with(['mentor.mentorProfile', 'mentee', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->where(function ($query) use ($user) {
                $query->where('mentee_id', $user->id)
                    ->orWhere('mentor_id', $user->id);
            })
            ->where('status', 'accepted')
            ->latest()
            ->get();

        // Compter les messages non lus
        $unreadCounts = Message::whereIn('mentorship_id', $mentorships->pluck('id'))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->selectRaw('mentorship_id, count(*) as count')
            ->groupBy('mentorship_id')
            ->pluck('count', 'mentorship_id');

        $result = $mentorships->map(function ($mentorship) use ($unreadCounts, $user) {
            $isMentor = $user->id === $mentorship->mentor_id;
            $otherParty = $isMentor ? $mentorship->mentee : $mentorship->mentor;

            return [
                'mentorship_id' => $mentorship->id,
                'other_party' => [
                    'id' => $otherParty->id,
                    'name' => $otherParty->name,
                    'avatar_url' => $otherParty->avatar_url,
                ],
                'last_message' => $mentorship->messages->first(),
                'unread_count' => $unreadCounts[$mentorship->id] ?? 0,
            ];
        });

        return $this->success($result);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/messages/{mentorship}",
     *     summary="Afficher les messages d'une conversation spécifique",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="mentorship", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Liste des messages de la conversation")
     * )
     */
    public function show(Request $request, Mentorship $mentorship): JsonResponse
    {
        $user = $request->user();

        if ($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if ($mentorship->status !== 'accepted') {
            return $this->error('Mentorship not accepted', 403);
        }

        // Marquer comme lus les messages reçus
        $mentorship->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $mentorship->messages()->with('sender')->latest()->paginate(50);

        return $this->success($messages);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/messages/{mentorship}",
     *     summary="Envoyer un message dans une conversation",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="mentorship", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="body", type="string", example="Bonjour !"),
     *                 @OA\Property(property="attachment", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Message envoyé avec succès")
     * )
     */
    public function store(Request $request, Mentorship $mentorship): JsonResponse
    {
        $user = $request->user();

        if ($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if ($mentorship->status !== 'accepted') {
            return $this->error('Mentorship not accepted', 403);
        }

        // Limiter la taille totale du payload pour éviter les attaques DoS (Règle SonarQube S5693)
        if ($request->header('Content-Length') > 15 * 1024 * 1024) { // 15Mo max
            return $this->error('Taille de la requête trop volumineuse (max 15Mo).', 413);
        }

        $request->validate([
            'body' => 'nullable|string|max:5000',
        ]);

        $validatedFiles = $request->validate([
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,zip,txt',
        ]);
        if (isset($validatedFiles['attachment'])) {
            $attachment = $validatedFiles['attachment'];
            if (! $attachment->isValid()) {
                return $this->error('Fichier joint invalide.', 422);
            }
            if ($attachment->getSize() > 10240 * 1024) {
                return $this->error('Le fichier joint dépasse la limite autorisée de 10 Mo.', 422);
            }
            $allowedMimes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'txt'];
            $extension = strtolower($attachment->getClientOriginalExtension());
            if (! in_array($extension, $allowedMimes)) {
                return $this->error('Format de fichier non autorisé.', 422);
            }
        }

        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return $this->error('Veuillez écrire un message ou joindre un fichier.', 422);
        }

        $data = [
            'mentorship_id' => $mentorship->id,
            'sender_id' => $user->id,
            'body' => $request->body,
        ];

        if (isset($validatedFiles['attachment'])) {
            $file = $validatedFiles['attachment'];
            $path = $file->store('messages/attachments', 'local');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        // Modération du contenu
        if ($request->filled('body')) {
            $moderator = new ContentModerator;
            $moderationResult = $moderator->moderate($request->body, $mentorship);

            if ($moderationResult['is_flagged']) {
                $data['original_body'] = $request->body;
                $data['body'] = $moderationResult['redacted'];
                $data['is_flagged'] = true;
                $data['flag_reason'] = $moderationResult['reason'];
            }
        }

        $message = Message::create($data);

        // Envoyer une notification par email (immédiate)
        app(MentorshipNotificationService::class)->sendNewMessageNotification($message);

        return $this->created($message);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/messages/file/{message}/download",
     *     summary="Télécharger la pièce jointe d'un message",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="message", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Fichier téléchargé")
     * )
     */
    public function download(Request $request, Message $message)
    {
        $user = $request->user();
        $mentorship = $message->mentorship;

        if ($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if (! $message->hasAttachment()) {
            return $this->notFound('Attachment not found');
        }

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }

    /**
     * @OA\Patch(
     *     path="/api/v2/messages/{message}/update",
     *     summary="Modifier le contenu d'un message envoyé",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="message", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"body"},
     *
     *             @OA\Property(property="body", type="string", example="Message modifié")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Message mis à jour")
     * )
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        if ($message->sender_id !== $user->id || $message->is_deleted) {
            return $this->forbidden();
        }

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $data = ['body' => $request->body];

        // Modération du contenu
        $moderator = new ContentModerator;
        $moderationResult = $moderator->moderate($request->body, $message->mentorship);

        if ($moderationResult['is_flagged']) {
            $data['original_body'] = $request->body;
            $data['body'] = $moderationResult['redacted'];
            $data['is_flagged'] = true;
            $data['flag_reason'] = $moderationResult['reason'];
        } else {
            $data['is_flagged'] = false;
            $data['flag_reason'] = null;
            $data['original_body'] = null;
        }

        $message->update(array_merge($data, ['edited_at' => now()]));

        return $this->success($message);
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/messages/{message}",
     *     summary="Supprimer un message (soft delete)",
     *     tags={"Messagerie"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="message", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Message supprimé")
     * )
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        if ($message->sender_id !== $user->id) {
            return $this->forbidden();
        }

        $message->update([
            'is_deleted' => true,
            'body' => null,
            'attachment_path' => null,
            'attachment_name' => null,
        ]);

        return $this->success($message);
    }
}
