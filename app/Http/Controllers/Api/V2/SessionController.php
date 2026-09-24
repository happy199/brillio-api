<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\SessionController as V1SessionController;
use App\Models\MentoringSession;
use App\Services\BrillioIAService;
use App\Services\JitsiService;
use App\Services\MentorshipNotificationService;
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
        WalletService $walletService,
        private MentorshipNotificationService $notificationService
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

        app(MentorshipNotificationService::class)->sendSessionConfirmed($session);

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

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);
        $reason = $validated['reason'] ?? 'Refusée par le mentor';

        $session->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        app(MentorshipNotificationService::class)->sendSessionCancelled($session, $reason);

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
            app(WalletService::class)->payoutMentor($session);
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
     * Télécharger l'enregistrement vidéo d'une séance (crédits configurés)
     */
    public function downloadVideoRecording(Request $request, $id)
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id && ! $session->mentees->contains('id', $user->id)) {
            return $this->forbidden();
        }

        $cost = $this->walletService->getFeatureCost('video_recording_download', 15);

        if (empty($session->video_recording_url) || $user->credits_balance < $cost) {
            $msg = empty($session->video_recording_url)
                ? 'Aucun enregistrement vidéo disponible.'
                : "Votre solde de crédits est insuffisant ($cost crédits requis).";
            $status = empty($session->video_recording_url) ? 404 : 402;

            return $this->error($msg, $status);
        }

        $this->walletService->deductCredits(
            $user,
            $cost,
            'feature_use',
            "Téléchargement de l'enregistrement vidéo de la séance : {$session->title}",
            $session
        );

        return $this->success(['url' => $session->video_recording_url], "Accès à l'enregistrement vidéo accordé.");
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

    /**
     * @OA\Post(
     *     path="/api/v2/sessions/{id}/prefill-report",
     *     summary="Pré-remplit le compte rendu via l'IA à partir de la transcription",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Compte rendu pré-rempli avec succès"),
     *     @OA\Response(response=402, description="Crédits insuffisants"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Séance non trouvée"),
     *     @OA\Response(response=422, description="Transcription non disponible ou erreur IA")
     * )
     */
    public function prefillReport(Request $request, $id, BrillioIAService $brillioIAService): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        if ($session->mentor_id !== $user->id) {
            return $this->forbidden('Seul le mentor de la séance peut pré-remplir le rapport.');
        }

        if (! $session->has_transcription) {
            return $this->error("La transcription n'est pas encore disponible. Vous pourrez pré-remplir le rapport une fois le meeting terminé et la transcription générée.", 422);
        }

        $cost = $this->walletService->getFeatureCost('ai_report_generation', 5);

        if ($user->credits_balance < $cost) {
            $missing = $cost - $user->credits_balance;

            return $this->error("Votre solde de crédits est insuffisant ($cost crédits requis). Il vous manque $missing crédits pour utiliser l'IA.", 402);
        }

        $mentorName = $session->mentor?->name ?? $user->name;
        $menteeNames = $session->mentees->pluck('name')->join(', ');

        $suggestedReport = $brillioIAService->summarizeTranscription(
            $session->transcription_raw,
            $mentorName,
            $menteeNames
        );

        if (! $suggestedReport) {
            return $this->error("L'IA n'a pas pu générer le résumé. Veuillez réessayer ou remplir manuellement.", 422);
        }

        $this->walletService->deductCredits(
            $user,
            $cost,
            'feature_use',
            "Pré-remplissage du compte rendu par l'IA : {$session->title}",
            $session
        );

        return $this->success([
            'suggested_report' => $suggestedReport,
            'credits_deducted' => $cost,
            'credits_balance' => $user->fresh()->credits_balance,
        ], "Le compte rendu a été pré-rempli par l'IA avec succès ($cost crédits déduits).");
    }

    /**
     * @OA\Get(
     *     path="/api/v2/sessions/{id}/meeting",
     *     summary="Récupère les informations et token JWT Jitsi pour rejoindre la visioconférence",
     *     tags={"Séances"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Paramètres de réunion Jitsi récupérés"),
     *     @OA\Response(response=403, description="Accès refusé"),
     *     @OA\Response(response=404, description="Séance non trouvée")
     * )
     */
    public function meeting(Request $request, $id, JitsiService $jitsiService): JsonResponse
    {
        $session = MentoringSession::findOrFail($id);
        $user = $request->user();

        $isMentor = $session->all_mentors->pluck('id')->contains($user->id);
        $isMentee = $session->mentees()->where('users.id', $user->id)->exists();
        $isOrganizationMember = $user->organization_id && (
            $user->organization_id === $session->scheduled_by_organization_id ||
            $session->mentees()->where('sponsored_by_organization_id', $user->organization_id)->exists()
        );

        if (! $isMentor && ! $isMentee && ! $isOrganizationMember) {
            return $this->forbidden('Accès refusé. Vous ne faites pas partie de cette séance.');
        }

        if ($session->status === 'cancelled') {
            return $this->error('Cette séance a été annulée.', 400);
        }

        $roomName = basename($session->meeting_link);
        $isModerator = $isMentor || $isOrganizationMember;
        $jwt = $jitsiService->generateToken($user, $roomName, $isModerator);
        $appId = env('JAAS_APP_ID');
        $meetingUrl = "https://8x8.vc/{$appId}/{$roomName}".($jwt ? "?jwt={$jwt}" : '');

        return $this->success([
            'session_id' => $session->id,
            'session_title' => $session->title,
            'room_name' => $roomName,
            'jaas_app_id' => $appId,
            'server_url' => 'https://8x8.vc',
            'jwt' => $jwt,
            'meeting_url' => $meetingUrl,
            'is_moderator' => $isModerator,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->profile_photo_url ?? null,
            ],
        ]);
    }
}
