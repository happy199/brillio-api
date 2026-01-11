<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\ChatConversation;
use App\Services\DeepSeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour le chatbot IA
 */
class ChatController extends Controller
{
    public function __construct(
        private DeepSeekService $deepSeekService
    ) {}

    /**
     * Liste les conversations de l'utilisateur
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->integer('limit', 20);

        $conversations = $this->deepSeekService->getUserConversations($user, $limit);

        return $this->success([
            'conversations' => $conversations->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'title' => $conv->title,
                    'created_at' => $conv->created_at->toISOString(),
                    'updated_at' => $conv->updated_at->toISOString(),
                    'messages_count' => $conv->messages()->count(),
                ];
            }),
        ]);
    }

    /**
     * Crée une nouvelle conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createConversation(Request $request): JsonResponse
    {
        $user = $request->user();
        $title = $request->input('title');

        $conversation = $this->deepSeekService->createConversation($user, $title);

        return $this->created([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->toISOString(),
            ],
        ], 'Conversation créée');
    }

    /**
     * Récupère les messages d'une conversation
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        $limit = $request->integer('limit', 50);
        $messages = $this->deepSeekService->getConversationMessages($conversation, $limit);

        return $this->success([
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Envoie un message et reçoit la réponse de l'IA
     *
     * @param SendMessageRequest $request
     * @return JsonResponse
     */
    public function send(SendMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Récupérer ou créer la conversation
        $conversationId = $validated['conversation_id'] ?? null;

        if ($conversationId) {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$conversation) {
                return $this->notFound('Conversation non trouvée');
            }
        } else {
            // Créer une nouvelle conversation
            $conversation = $this->deepSeekService->createConversation($user);
        }

        // Envoyer le message et obtenir la réponse
        $assistantMessage = $this->deepSeekService->sendMessage(
            $conversation,
            $validated['message']
        );

        // Recharger la conversation pour avoir le titre mis à jour
        $conversation->refresh();

        return $this->success([
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'message' => [
                'id' => $assistantMessage->id,
                'role' => $assistantMessage->role,
                'content' => $assistantMessage->content,
                'created_at' => $assistantMessage->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Supprime une conversation
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function deleteConversation(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        $this->deepSeekService->deleteConversation($conversation);

        return $this->success(null, 'Conversation supprimée');
    }
}
