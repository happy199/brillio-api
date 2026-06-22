<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\SessionController as V1SessionController;
use App\Models\MentoringSession;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return parent::index($request);
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
        return parent::store($request);
    }

    /**
     * Annuler une séance
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        return parent::cancel($id, $request);
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
