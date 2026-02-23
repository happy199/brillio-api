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
                    'needs_human_support' => (bool) $conv->needs_human_support,
                    'human_support_active' => (bool) $conv->human_support_active,
                ];
            }),
        ]);
    }

    /**
     * Crée une nouvelle conversation
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
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        $limit = $request->integer('limit', 50);
        $messages = $this->deepSeekService->getConversationMessages($conversation, $limit);

        return $this->success([
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'needs_human_support' => (bool) $conversation->needs_human_support,
            'human_support_active' => (bool) $conversation->human_support_active,
            'messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'is_from_human' => (bool) $msg->is_from_human,
                    'is_system_message' => (bool) $msg->is_system_message,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Envoie un message et reçoit la réponse de l'IA
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

            if (! $conversation) {
                return $this->notFound('Conversation non trouvée');
            }
        } else {
            // Créer une nouvelle conversation
            $conversation = $this->deepSeekService->createConversation($user);
        }

        // Si le support humain est actif, on enregistre juste le message utilisateur sans appeler l'IA
        if ($conversation->human_support_active) {
            $userMessage = $conversation->messages()->create([
                'role' => 'user',
                'content' => $validated['message'],
            ]);
            $conversation->touch();

            return $this->success([
                'conversation_id' => $conversation->id,
                'conversation_title' => $conversation->title,
                'human_support_active' => true,
                'message' => [
                    'id' => $userMessage->id,
                    'role' => $userMessage->role,
                    'content' => $userMessage->content,
                    'created_at' => $userMessage->created_at->toISOString(),
                ],
                'info' => 'Un conseiller humain est en train de gérer votre conversation. Votre message lui a été transmis.',
            ]);
        }

        // Envoyer le message et obtenir la réponse de l'IA
        $assistantMessage = $this->deepSeekService->sendMessage(
            $conversation,
            $validated['message']
        );

        // Recharger la conversation pour avoir le titre mis à jour
        $conversation->refresh();

        return $this->success([
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'human_support_active' => false,
            'message' => [
                'id' => $assistantMessage->id,
                'role' => $assistantMessage->role,
                'content' => $assistantMessage->content,
                'is_from_human' => false,
                'created_at' => $assistantMessage->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Supprime une conversation
     */
    public function deleteConversation(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        $this->deepSeekService->deleteConversation($conversation);

        return $this->success(null, 'Conversation supprimée');
    }

    /**
     * Demande un support humain pour une conversation
     */
    public function requestHumanSupport(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        // Vérifier si déjà en support humain
        if ($conversation->human_support_active) {
            return $this->success([
                'conversation_id' => $conversation->id,
                'needs_human_support' => true,
                'human_support_active' => true,
            ], 'Un conseiller est déjà en train de vous aider.');
        }

        // Demander le support humain
        $conversation->requestHumanSupport();

        // Ajouter un message système pour informer l'utilisateur
        $systemMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => "Votre demande de parler à un conseiller a bien été enregistrée. Un conseiller va prendre en charge votre conversation dans les meilleurs délais. En attendant, n'hésitez pas à décrire votre situation.",
            'is_from_human' => false,
            'is_system_message' => true,
        ]);

        return $this->success([
            'conversation_id' => $conversation->id,
            'needs_human_support' => true,
            'human_support_active' => false,
            'message' => [
                'id' => $systemMessage->id,
                'role' => $systemMessage->role,
                'content' => $systemMessage->content,
                'is_system_message' => true,
                'created_at' => $systemMessage->created_at->toISOString(),
            ],
        ], 'Demande de support humain enregistrée.');
    }

    /**
     * Annule une demande de support humain
     */
    public function cancelHumanSupport(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound('Conversation non trouvée');
        }

        // Si le support est actif, l'utilisateur ne peut pas annuler
        if ($conversation->human_support_active) {
            return $this->error('Un conseiller est déjà en charge. Vous ne pouvez pas annuler.', 400);
        }

        // Annuler la demande
        $conversation->update([
            'needs_human_support' => false,
        ]);

        return $this->success([
            'conversation_id' => $conversation->id,
            'needs_human_support' => false,
            'human_support_active' => false,
        ], 'Demande de support annulée. Vous pouvez continuer avec le chatbot IA.');
    }
}
