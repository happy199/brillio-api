<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\ChatController as V1ChatController;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\ChatConversation;
use App\Services\BrillioIAService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * Controller pour le chatbot d'orientation via API
 */
class ChatController extends V1ChatController
{
    private const MSG_CONVERSATION_NOT_FOUND = 'Conversation non trouvée';

    public function __construct(
        private BrillioIAService $brillioIAService,
        private WalletService $walletService
    ) {
        parent::__construct($brillioIAService, $walletService);
    }

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
        return parent::conversations($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/chat/conversations",
     * summary="Crée une nouvelle conversation d'orientation",
     * tags={"Chat"},
     *
     * @OA\RequestBody(
     * required=false,
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="title", type="string", example="Mon projet d'orientation")
     * )
     * ),
     *
     * @OA\Response(response=201, description="Conversation créée avec succès")
     * )
     */
    public function createConversation(Request $request): JsonResponse
    {
        return parent::createConversation($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/chat/conversations/{id}",
     * summary= "Récupère les messages d'une conversation d'orientation",
     * tags={"Chat"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *
     * @OA\Response(
     * response=200,
     * description="Messages récupérés avec succès"
     * ),
     * @OA\Response(response=404, description="Conversation non trouvée")
     * )
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        return parent::messages($request, $conversationId);
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
        return parent::send($request);
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
        return parent::deleteConversation($request, $conversationId);
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
        DB::transaction(function () use ($user, $conversation, $cost) {
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
     *     summary="Annule une demande d'aide humaine",
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
     *         description="Demande de support humain annulée"
     *     ),
     *     @OA\Response(response=400, description="Impossible d'annuler (conseiller déjà actif)"),
     *     @OA\Response(response=404, description="Conversation non trouvée")
     * )
     */
    public function cancelHumanSupport(Request $request, int $conversationId): JsonResponse
    {
        return parent::cancelHumanSupport($request, $conversationId);
    }
}
