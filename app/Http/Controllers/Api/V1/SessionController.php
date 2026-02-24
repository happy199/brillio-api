<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\User;
use App\Models\MentorProfile;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

/**
 * Controller pour la gestion des séances de mentorat via API
 */
class SessionController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private \App\Services\MentorshipNotificationService $notificationService
        )
    {
    }

    /**
     * Liste les séances de l'utilisateur
     */
    #[OA\Get(
        path: "/api/v1/sessions",
        summary: "Liste les séances de mentorat",
        tags: ["Séances"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string", enum: ["upcoming", "past"])),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Liste des séances")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $type = $request->get('type', 'upcoming'); // upcoming or past

        $query = $user->mentoringSessionsAsMentee();

        if ($type === 'upcoming') {
            $query->where('mentoring_sessions.scheduled_at', '>=', now())
                ->where('mentoring_sessions.status', '!=', 'cancelled')
                ->where('mentoring_sessions.status', '!=', 'completed')
                ->wherePivotNotIn('status', ['cancelled', 'rejected']);
        } else {
            $query->where(function ($q) {
                $q->where('mentoring_sessions.scheduled_at', '<', now())
                    ->orWhere('mentoring_sessions.status', 'cancelled')
                    ->orWhere('mentoring_sessions.status', 'completed')
                    ->orWhereIn('mentoring_session_user.status', ['cancelled', 'rejected']);
            });
        }

        if ($status = $request->get('status')) {
            $query->where('mentoring_sessions.status', $status);
        }

        $sessions = $query->with('mentor')->orderBy('scheduled_at', $type === 'upcoming' ? 'asc' : 'desc')->get();

        return $this->success([
            'sessions' => $sessions->map(fn ($s) => $this->formatSession($s)),
            'type' => $type,
        ]);
    }

    /**
     * Réserver une nouvelle séance
     */
    #[OA\Post(
        path: "/api/v1/sessions",
        summary: "Réserver une séance de mentorat",
        tags: ["Séances"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["mentor_id", "scheduled_at", "title"],
                properties: [
                    new OA\Property(property: "mentor_id", type: "integer", example: 1),
                    new OA\Property(property: "scheduled_at", type: "string", format: "date-time"),
                    new OA\Property(property: "title", type: "string", example: "Session d'orientation"),
                    new OA\Property(property: "duration_minutes", type: "integer", example: 60)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Séance réservée")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'mentor_id' => 'required|exists:mentor_profiles,id',
            'scheduled_at' => 'required|date|after:now',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'duration_minutes' => 'integer|min:30|max:120',
        ]);

        $mentorProfile = MentorProfile::find($request->mentor_id);
        
        // Vérifier si un mentorat actif existe
        $mentorship = Mentorship::where('mentee_id', $user->id)
            ->where('mentor_id', $mentorProfile->user_id)
            ->where('status', 'accepted')
            ->first();

        if (!$mentorship) {
            return $this->forbidden("Vous devez avoir un mentorat accepté avec ce mentor pour réserver une séance.");
        }

        $session = MentoringSession::create([
            'mentor_id' => $mentorProfile->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes ?? 60,
            'status' => 'proposed',
            'created_by' => 'mentee',
        ]);

        $session->mentees()->attach($user->id, ['status' => 'accepted']);

        // Notification
        try {
            $this->notificationService->sendSessionProposed($session);
        } catch (\Exception $e) {
            Log::error("Erreur notification session proposée: ".$e->getMessage());
        }

        return $this->created([
            'session' => $this->formatSession($session),
        ], 'Séance proposée au mentor');
    }

    /**
     * Annuler une séance
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $session = $user->mentoringSessionsAsMentee()->where('mentoring_sessions.id', $id)->first();

        if (!$session) {
            return $this->notFound('Session non trouvée');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        // Refund logic
        if ($session->is_paid) {
            $hoursToSession = now()->diffInHours($session->scheduled_at, false);
            $refundRatio = ($hoursToSession >= 24) ? 1.0 : 0.75;
            $this->walletService->refundJeune($session, $user, $refundRatio);
        }

        $session->mentees()->updateExistingPivot($user->id, [
            'status' => 'cancelled',
            'rejection_reason' => $request->cancel_reason,
        ]);

        // Global cancellation if no mentees left
        $activeMenteesCount = $session->mentees()->wherePivotNotIn('status', ['cancelled', 'rejected'])->count();
        if ($activeMenteesCount === 0) {
            $session->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Tous les participants ont annulé via API.',
            ]);
        }

        try {
            $this->notificationService->sendSessionCancelled($session, $user);
        } catch (\Exception $e) {
            Log::error("Erreur notification session annulée: ".$e->getMessage());
        }

        return $this->success(null, 'Session annulée avec succès.');
    }

    /**
     * Payer et rejoindre une séance
     */
    public function pay(int $id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $session = $user->mentoringSessionsAsMentee()->where('mentoring_sessions.id', $id)->first();

        if (!$session) {
            return $this->notFound('Session non trouvée');
        }

        $userPivot = $session->pivot;
        if (!$session->is_paid || $userPivot->status === 'accepted') {
            return $this->success(['meeting_id' => $session->meeting_id], 'Déjà payé ou gratuit.');
        }

        $price = $session->credit_cost;
        if ($user->credits_balance < $price) {
            return $this->error("Solde insuffisant ($price crédits requis).", 402);
        }

        try {
            DB::transaction(function () use ($user, $session, $price) {
                $this->walletService->deductCredits($user, $price, 'expense', "Paiement séance (Escrow) : {$session->title}", $session);
                $session->update(['status' => 'confirmed']);
                $session->mentees()->updateExistingPivot($user->id, ['status' => 'accepted']);

                $this->notificationService->sendSessionConfirmed($session);
                $this->notificationService->sendSessionPayment($session, $user, $price);
                if ($session->mentor) {
                    $this->notificationService->sendPaymentReceived($session, $session->mentor, $session->price);
                }
            });

            return $this->success(['meeting_id' => $session->meeting_id], 'Paiement effectué avec succès.');
        }
        catch (\Exception $e) {
            return $this->error('Erreur lors du paiement : ' . $e->getMessage(), 500);
        }
    }

    private function formatSession(MentoringSession $session): array
    {
        return [
            'id' => $session->id,
            'title' => $session->title,
            'description' => $session->description,
            'scheduled_at' => $session->scheduled_at->toISOString(),
            'duration_minutes' => $session->duration_minutes,
            'status' => $session->status,
            'is_paid' => $session->is_paid,
            'credit_cost' => $session->credit_cost,
            'meeting_id' => $session->meeting_id,
            'mentor' => [
                'id' => $session->mentor->id,
                'name' => $session->mentor->name,
                'avatar' => $session->mentor->avatar_url,
            ],
        ];
    }
}