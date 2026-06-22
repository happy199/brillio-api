<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\SessionController as V1SessionController;
use App\Models\MentoringSession;
use App\Models\MentorProfile;
use App\Models\Mentorship;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des séances de mentorat via API
 */
class SessionController extends V1SessionController
{
    public function __construct(
        private WalletService $walletService,
        private \App\Services\MentorshipNotificationService $notificationService
    ) {
        parent::__construct($walletService, $notificationService);
    }

    /**
     * @OA\Get(
     * path="/api/v2/sessions",
     * summary="Liste les séances de mentorat",
     * tags={"Séances"},
     *
     * @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"upcoming", "past"})),
     * @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *
     * @OA\Response(response= 200, description="Liste des séances"),
     * )
     */
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
     * @OA\Post(
     * path="/api/v2/sessions",
     * summary="Réserver une séance de mentorat",
     * tags={"Séances"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"mentor_id", "scheduled_at", "title"},
     *
     * @OA\Property(property="mentor_id", type="integer", example= 1),
     * @OA\Property(property="scheduled_at", type="string", format="date-time"),
     * @OA\Property(property="title", type="string", example= "Session d'orientation"),
     * @OA\Property(property="duration_minutes", type="integer", example= 60),
     * )
     * ),
     *
     * @OA\Response(response= 201, description="Séance réservée"),
     * )
     */
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

        if (! $mentorship) {
            return $this->forbidden('Vous devez avoir un mentorat accepté avec ce mentor pour réserver une séance.');
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
            Log::error('Erreur notification session proposée: '.$e->getMessage());
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

        if (! $session) {
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
            Log::error('Erreur notification session annulée: '.$e->getMessage());
        }

        return $this->success(null, 'Session annulée avec succès.');
    }

    /**
     * Payer et rejoindre une séance
     */
    public function pay(int $id, Request $request): JsonResponse
    {
        return parent::pay($id, $request);
    }

    /**
     * @OA\Put(
     *     path="/api/v2/sessions/{id}",
     *     summary="Modifier la date ou la durée d'une séance",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"scheduled_at", "duration_minutes"},
     *
     *             @OA\Property(property="scheduled_at", type="string", format="date-time"),
     *             @OA\Property(property="duration_minutes", type="integer", example=60)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Séance mise à jour")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|in:30,45,60,90,120',
        ]);

        $session->update([
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return $this->success($session, 'Session mise à jour avec succès.');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/sessions/{id}/accept",
     *     summary="Accepter une proposition de séance",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Séance acceptée et confirmée")
     * )
     */
    public function accept(Request $request, $id): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if ($session->status !== 'pending') {
            return $this->error('Cette session n\'est pas en attente.', 400);
        }

        // URL dynamique selon la configuration de l'org ou Zoom
        $meetingUrl = config('services.jitsi.domain') ? 'https://'.config('services.jitsi.domain').'/'.\Str::uuid() : null;

        $session->update([
            'status' => 'confirmed',
            'meeting_url' => $meetingUrl,
        ]);

        app(\App\Services\MentorshipNotificationService::class)->sendSessionConfirmed($session);

        return $this->success($session, 'Session acceptée.');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/sessions/{id}/refuse",
     *     summary="Refuser une proposition de séance",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="reason", type="string", example="Créneau indisponible")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Séance refusée et annulée")
     * )
     */
    public function refuse(Request $request, $id): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        $session->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->get('reason', 'Refusée par le mentor'),
        ]);

        app(\App\Services\MentorshipNotificationService::class)->sendSessionCancelled($session, $request->get('reason'));

        return $this->success($session, 'Session refusée.');
    }

    /**
     * @OA\Put(
     *     path="/api/v2/sessions/{id}/report",
     *     summary="Enregistrer le compte-rendu de la séance",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"report_content", "status"},
     *
     *             @OA\Property(property="report_content", type="object"),
     *             @OA\Property(property="status", type="string", enum={"completed", "cancelled"})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Compte-rendu enregistré")
     * )
     */
    public function report(Request $request, $id): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        $request->validate([
            'report_content' => 'required|array',
            'status' => 'required|in:completed,cancelled',
        ]);

        $session->update([
            'report_content' => $request->report_content,
            'status' => $request->status,
        ]);

        // Optionnel : déclencher le payout
        if ($session->status === 'completed' && ! $session->is_paid_to_mentor) {
            app(\App\Services\WalletService::class)->payoutMentor($session);
        }

        return $this->success($session, 'Compte rendu enregistré.');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/sessions/{id}/download-report",
     *     summary="Télécharger le compte-rendu PDF",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Rapport PDF téléchargé")
     * )
     */
    public function downloadReport(Request $request, $id)
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id && ! $session->mentees->contains('id', $user->id)) {
            return $this->forbidden();
        }

        if ($session->status !== 'completed' || empty($session->report_content)) {
            return $this->error('Aucun rapport disponible.', 404);
        }

        $pdf = \PDF::loadView('mentor.reports.session_pdf', compact('session', 'user'));

        return $pdf->download('rapport-session-'.$session->id.'.pdf');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/sessions/{id}/download-transcription",
     *     summary="Télécharger la transcription audio PDF (Coûte 5 crédits pour les jeunes)",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Transcription PDF téléchargée"),
     *     @OA\Response(response=402, description="Crédits insuffisants")
     * )
     */
    public function downloadTranscription(Request $request, $id)
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id && ! $session->mentees->contains('id', $user->id)) {
            return $this->forbidden();
        }

        if (! $session->has_transcription) {
            return $this->error('Aucune transcription disponible.', 404);
        }

        $errorResponse = null;
        // Credit Check & Deduction for youths
        if ($user->user_type === 'jeune') {
            $cost = $this->walletService->getFeatureCost('transcription_download', 5);

            if ($user->credits_balance < $cost) {
                $errorResponse = $this->error("Votre solde de crédits est insuffisant ($cost crédits requis).", 402);
            } else {
                $this->walletService->deductCredits(
                    $user,
                    $cost,
                    'feature_use',
                    "Téléchargement de la transcription de la séance : {$session->title}",
                    $session
                );
            }
        }

        if ($errorResponse) {
            return $errorResponse;
        }

        $pdf = \PDF::loadView('common.reports.transcription_pdf', compact('session', 'user'));

        return $pdf->download('transcription-session-'.$session->id.'.pdf');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/sessions/unlock-history",
     *     summary="Débloque l'historique complet des séances (Coûte 5 crédits)",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Historique débloqué",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="new_balance", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(response=402, description="Crédits insuffisants")
     * )
     */
    public function unlockHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->jeuneProfile;

        if (! $profile) {
            return $this->error('Profil jeune introuvable.', 404);
        }

        if ($profile->has_unlocked_session_history) {
            return $this->success(['new_balance' => $user->credits_balance], "Vous avez déjà débloqué l'historique complet.");
        }

        $cost = $this->walletService->getFeatureCost('unlock_history', 5);

        if ($user->credits_balance < $cost) {
            return $this->error("Votre solde de crédits est insuffisant ($cost crédits requis).", 402);
        }

        $this->walletService->deductCredits(
            $user,
            $cost,
            'feature_unlock',
            "Déblocage de l'historique complet des séances"
        );

        $profile->update(['has_unlocked_session_history' => true]);
        $user->refresh();

        return $this->success([
            'new_balance' => $user->credits_balance,
        ], 'Historique complet débloqué avec succès !');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/sessions/compiled-reports",
     *     summary="Génère et télécharge un rapport compilé de plusieurs séances (Coûte 5 crédits)",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"session_ids"},
     *
     *             @OA\Property(property="session_ids", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Rapport compilé PDF généré"),
     *     @OA\Response(response=402, description="Crédits insuffisants")
     * )
     */
    public function downloadCompiledReports(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $ids = $request->session_ids;

        // Fetch valid completed sessions that belong to the mentee
        $sessions = $user->mentoringSessionsAsMentee()
            ->whereIn('mentoring_sessions.id', $ids)
            ->where('mentoring_sessions.status', 'completed')
            ->whereNotNull('mentoring_sessions.report_content')
            ->orderBy('mentoring_sessions.scheduled_at', 'asc')
            ->get();

        if ($sessions->isEmpty()) {
            return $this->error('Aucun compte rendu valide sélectionné.', 404);
        }

        $cost = $this->walletService->getFeatureCost('compiled_report', 5);

        if ($user->credits_balance < $cost) {
            return $this->error("Votre solde de crédits est insuffisant ($cost crédits requis).", 402);
        }

        $this->walletService->deductCredits(
            $user,
            $cost,
            'feature_use',
            "Génération d'un rapport compilé (".$sessions->count().' séances)'
        );

        $pdf = \PDF::loadView('mentor.reports.compiled_sessions_pdf', compact('sessions'));

        return $pdf->download('rapport-seances-compile.pdf');
    }
}
