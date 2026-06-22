<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\ChatConversation;
use App\Services\BrillioIAService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour le chatbot d'orientation via API
 */
class ChatController extends Controller
{
    private const MSG_CONVERSATION_NOT_FOUND = 'Conversation non trouvée';

    public function __construct(
        private BrillioIAService $brillioIAService,
        private WalletService $walletService
    ) {}

    /**
     * @OA\Get(
     * path="/api/v2/chat/conversations",
     * summary= "Liste les conversations de l'utilisateur",
     * tags={"Chat"},
     *
     * @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Liste des conversations"),
     * )
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->integer('limit', 20);

        $conversations = $this->brillioIAService->getUserConversations($user, $limit);

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
     * @OA\Post(
     *     path="/api/v2/chat/conversations",
     *     summary="Crée une nouvelle conversation d'orientation",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string", example="Nouvelle discussion d'orientation")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Conversation créée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conversation créée"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=402, description="Solde insuffisant")
     * )
     */
    public function createConversation(Request $request): JsonResponse
    {
        $user = $request->user();
        $title = $request->input('title');

        $cost = $this->walletService->getFeatureCost('new_chat', 10);
        if ($user->credits_balance < $cost) {
            return $this->error("Solde insuffisant pour créer une nouvelle conversation ($cost crédits requis).", 402);
        }

        $conversation = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $title, $cost) {
            $this->walletService->deductCredits(
                $user,
                $cost,
                'feature_use',
                'Création d\'une nouvelle conversation IA'
            );

            return $this->brillioIAService->createConversation($user, $title);
        });

        return $this->created([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->toISOString(),
            ],
        ], 'Conversation créée');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/chat/conversations/{id}",
     *     summary="Récupère les messages d'une conversation d'orientation",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la conversation",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de messages à récupérer",
     *
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Messages récupérés avec succès"
     *     ),
     *     @OA\Response(response=404, description="Conversation non trouvée")
     * )
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound(self::MSG_CONVERSATION_NOT_FOUND);
        }

        $limit = $request->integer('limit', 50);
        $messages = $this->brillioIAService->getConversationMessages($conversation, $limit);

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
     * @OA\Post(
     * path="/api/v2/chat/send",
     * summary= "Envoie un message à l'IA ou à un humain",
     * tags={"Chat"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"message"},
     *
     * @OA\Property(property="message", type="string", example= "Bonjour, j'ai besoin d'aide pour mon orientation."),
     * @OA\Property(property="conversation_id", type="integer", example= 1),
     * )
     * ),
     *
     * @OA\Response(response= 200, description= "Réponse du chatbot ou confirmation d'envoi"),
     * )
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
                return $this->notFound(self::MSG_CONVERSATION_NOT_FOUND);
            }
        } else {
            // Créer une nouvelle conversation
            $cost = $this->walletService->getFeatureCost('new_chat', 10);
            if ($user->credits_balance < $cost) {
                return $this->error("Solde insuffisant pour créer une nouvelle conversation ($cost crédits requis).", 402);
            }

            $conversation = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $cost) {
                $this->walletService->deductCredits(
                    $user,
                    $cost,
                    'feature_use',
                    'Création d\'une nouvelle conversation IA (via message)'
                );

                return $this->brillioIAService->createConversation($user);
            });
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
        $assistantMessage = $this->brillioIAService->sendMessage(
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
     * @OA\Delete(
     *     path="/api/v2/chat/conversations/{id}",
     *     summary="Supprime une conversation d'orientation",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la conversation",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Conversation supprimée avec succès"
     *     ),
     *     @OA\Response(response=404, description="Conversation non trouvée")
     * )
     */
    public function deleteConversation(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound(self::MSG_CONVERSATION_NOT_FOUND);
        }

        $this->brillioIAService->deleteConversation($conversation);

        return $this->success(null, 'Conversation supprimée');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/chat/conversations/{id}/request-human",
     *     summary="Demande l'intervention d'un conseiller humain sur la conversation",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la conversation",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Demande de support humain enregistrée"
     *     ),
     *     @OA\Response(response=402, description="Solde insuffisant"),
     *     @OA\Response(response=404, description="Conversation non trouvée")
     * )
     */
    public function requestHumanSupport(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return $this->notFound(self::MSG_CONVERSATION_NOT_FOUND);
        }

        $cost = $this->walletService->getFeatureCost('contact_advisor', 10);
        $errorResponse = null;

        if ($conversation->human_support_active) {
            $errorResponse = $this->success([
                'conversation_id' => $conversation->id,
                'needs_human_support' => true,
                'human_support_active' => true,
            ], 'Un conseiller est déjà en train de vous aider.');
        } elseif ($user->credits_balance < $cost) {
            $errorResponse = $this->error("Solde insuffisant pour contacter un conseiller ($cost crédits requis).", 402);
        }

        if ($errorResponse) {
            return $errorResponse;
        }

        // Demander le support humain
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $conversation, $cost) {
            $this->walletService->deductCredits(
                $user,
                $cost,
                'feature_use',
                'Demande de support humain (Conseiller)'
            );

            $conversation->requestHumanSupport();
        });

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
     * @OA\Post(
     *     path="/api/v2/chat/conversations/{id}/cancel-human",
     *     summary="Annule une demande d'intervention humaine en attente",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la conversation",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Demande annulée avec succès. Retour à l'orientation IA."
     *     ),
     *     @OA\Response(response=400, description="Impossible d'annuler car un conseiller a déjà pris en charge la conversation"),
     *     @OA\Response(response=404, description="Conversation non trouvée")
     * )
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
