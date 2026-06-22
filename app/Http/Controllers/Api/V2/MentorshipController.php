<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\MentorshipController as V1MentorshipController;
use App\Jobs\GenerateMentorshipKeywords;
use App\Models\Mentorship;
use App\Models\Session;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des relations de mentorat via API
 */
class MentorshipController extends V1MentorshipController
{
    /**
     * @OA\Get(
     * path="/api/v2/mentorships",
     * summary= "Liste les relations de mentorat de l'utilisateur",
     * tags={"Mentorat"},
     *
     * @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending", "accepted", "rejected", "canceled", "completed"})),
     *
     * @OA\Response(response= 200, description="Liste des mentorats"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * @OA\Post(
     * path="/api/v2/mentorships",
     * summary="Demander un mentorat",
     * tags={"Mentorat"},
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"mentor_id", "message"},
     *
     * @OA\Property(property="mentor_id", type="integer", example= 1),
     * @OA\Property(property="message", type="string", example= "Bonjour, j'aimerais que vous soyez mon mentor."),
     * )
     * ),
     *
     * @OA\Response(response= 201, description="Demande envoyée"),
     * @OA\Response(response= 400, description="Demande déjà existante"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        return parent::store($request);
    }

    /**
     * @OA\Delete(
     * path="/api/v2/mentorships/{id}/cancel",
     * summary="Annuler une demande de mentorat",
     * tags={"Mentorat"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     *
     * @OA\RequestBody(
     * required= true,
     *
     * @OA\JsonContent(
     * required={"cancellation_reason"},
     *
     * @OA\Property(property="cancellation_reason", type="string", example= "Je n'ai plus besoin de mentorat."),
     * )
     * ),
     *
     * @OA\Response(response= 200, description="Demande annulée"),
     * @OA\Response(response= 400, description= "Impossible d'annuler la demande"),
     * @OA\Response(response= 404, description="Demande non trouvée"),
     * )
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        return parent::cancel($id, $request);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentorships/{id}/disconnect",
     *     summary="Terminer une relation de mentorat active",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"diction_reason"},
     *
     *             @OA\Property(property="diction_reason", type="string", example="Objectif atteint")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Mentorat terminé"),
     *     @OA\Response(response=400, description="Erreur dans la demande")
     * )
     */
    public function disconnect(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $mentorship = Mentorship::findOrFail($id);

        if ($mentorship->mentee_id !== $user->id && $mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if ($mentorship->status !== 'accepted') {
            return $this->error('Vous ne pouvez terminer qu\'une relation active.', 400);
        }

        $request->validate([
            'diction_reason' => 'required|string|max:1000',
        ]);

        $mentorship->update([
            'status' => 'disconnected',
            'diction_reason' => $request->diction_reason,
            'updated_at' => now(),
        ]);

        // Notification
        app(MentorshipNotificationService::class)->sendMentorshipTerminated($mentorship, $user, $request->diction_reason);

        return $this->success($mentorship, 'Mentorat terminé avec succès.');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentorships/{id}/accept",
     *     summary="Accepter une demande de mentorat",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Demande acceptée")
     * )
     */
    public function accept(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $mentorship = Mentorship::findOrFail($id);

        if ($mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        if ($mentorship->status !== 'pending') {
            return $this->error('La demande n\'est pas en attente.', 400);
        }

        $mentorship->update([
            'status' => 'accepted',
            'updated_at' => now(),
        ]);

        app(MentorshipNotificationService::class)->sendMentorshipAccepted($mentorship);

        if (class_exists(GenerateMentorshipKeywords::class)) {
            GenerateMentorshipKeywords::dispatch($mentorship);
        }

        return $this->success($mentorship, 'Demande de mentorat acceptée avec succès.');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentorships/{id}/refuse",
     *     summary="Refuser une demande de mentorat",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"refusal_reason"},
     *
     *             @OA\Property(property="refusal_reason", type="string", example="Pas assez de disponibilités.")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Demande refusée")
     * )
     */
    public function refuse(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $mentorship = Mentorship::findOrFail($id);

        if ($mentorship->mentor_id !== $user->id) {
            return $this->forbidden();
        }

        $request->validate([
            'refusal_reason' => 'required|string|max:500',
        ]);

        $mentorship->update([
            'status' => 'refused',
            'refusal_reason' => $request->refusal_reason,
            'updated_at' => now(),
        ]);

        app(MentorshipNotificationService::class)->sendMentorshipRefused($mentorship, $request->refusal_reason);

        return $this->success($mentorship, 'Demande refusée.');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mentorships/requests",
     *     summary="Voir les demandes de mentorat en attente",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Liste des demandes en attente")
     * )
     */
    public function requests(Request $request): JsonResponse
    {
        $mentor = $request->user();

        $pendingRequests = $mentor->mentorshipsAsMentor()
            ->where('status', 'pending')
            ->with('mentee')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($pendingRequests);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentorships/availability",
     *     summary="Basculer la disponibilité globale pour le mentorat",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Disponibilité mise à jour")
     * )
     */
    public function setAvailability(Request $request): JsonResponse
    {
        $mentor = $request->user();
        $profile = $mentor->mentorProfile;

        if (! $profile) {
            return $this->error('Profil mentor non trouvé.', 404);
        }

        $profile->update([
            'accepts_mentorship_requests' => ! $profile->accepts_mentorship_requests,
        ]);

        return $this->success([
            'accepts_mentorship_requests' => $profile->accepts_mentorship_requests,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mentorships/calendar",
     *     summary="Voir le calendrier des séances du mentor",
     *     tags={"Mentorat"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Calendrier des séances")
     * )
     */
    public function calendar(Request $request): JsonResponse
    {
        $mentor = $request->user();

        $sessions = Session::whereHas('mentorship', function ($query) use ($mentor) {
            $query->where('mentor_id', $mentor->id)
                ->where('status', 'accepted');
        })
            ->with('mentorship.mentee')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return $this->success($sessions);
    }
}
