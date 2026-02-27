<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\Mentorship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * Controller pour la gestion des relations de mentorat via API
 */
class MentorshipController extends Controller
{
    protected $notificationService;

    public function __construct(\App\Services\MentorshipNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Liste les mentorats de l'utilisateur (Jeune)
     */
    #[OA\Get(
        path: '/api/v1/mentorships',
        summary: "Liste les relations de mentorat de l'utilisateur",
        tags: ['Mentorat'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'accepted', 'rejected', 'canceled', 'completed'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des mentorats'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Mentorship::with(['mentor.user', 'mentee']);

        if ($user->isMentor()) {
            $query->where('mentor_id', $user->id);
        } else {
            $query->where('mentee_id', $user->id);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $mentorships = $query->latest()->get();

        return $this->success([
            'mentorships' => $mentorships->map(fn ($m) => $this->formatMentorship($m)),
        ]);
    }

    /**
     * Envoyer une demande de mentorat
     */
    #[OA\Post(
        path: '/api/v1/mentorships',
        summary: 'Demander un mentorat',
        tags: ['Mentorat'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mentor_id', 'message'],
                properties: [
                    new OA\Property(property: 'mentor_id', type: 'integer', example: 1),
                    new OA\Property(property: 'message', type: 'string', example: "Bonjour, j'aimerais que vous soyez mon mentor."),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Demande envoyée'),
            new OA\Response(response: 400, description: 'Demande déjà existante'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'mentor_id' => 'required|exists:mentor_profiles,id',
            'message' => 'required|string|min:10|max:1000',
        ]);

        $mentorProfile = MentorProfile::find($request->mentor_id);
        $mentorUserId = $mentorProfile->user_id;

        // Vérifier si une demande existe déjà
        $existing = Mentorship::where('mentee_id', $user->id)
            ->where('mentor_id', $mentorUserId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return $this->error('Vous avez déjà une demande en cours ou acceptée avec ce mentor.', 400);
        }

        // Créer la demande
        $mentorship = Mentorship::create([
            'mentee_id' => $user->id,
            'mentor_id' => $mentorUserId,
            'status' => 'pending',
            'request_message' => $request->message,
        ]);

        // Si le profil du jeune n'est pas publié, on le publie automatiquement
        if ($user->isJeune() && (! $user->mentorProfile || ! $user->mentorProfile->is_published)) {
            // Note: Les jeunes n'ont pas forcément de mentorProfile, mais ils ont des infos de profil
        }

        // Notification
        try {
            $this->notificationService->sendMentorshipRequest($mentorship);
        } catch (\Exception $e) {
            \Log::error('Erreur notification demande mentorat: '.$e->getMessage());
        }

        return $this->created([
            'mentorship' => $this->formatMentorship($mentorship),
        ], 'Demande de mentorat envoyée avec succès');
    }

    /**
     * Annuler une demande de mentorat
     */
    #[OA\Delete(
        path: '/api/v1/mentorships/{id}/cancel',
        summary: 'Annuler une demande de mentorat',
        tags: ['Mentorat'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['cancellation_reason'],
                properties: [
                    new OA\Property(property: 'cancellation_reason', type: 'string', example: "Je n'ai plus besoin de mentorat."),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Demande annulée'),
            new OA\Response(response: 400, description: "Impossible d'annuler la demande"),
            new OA\Response(response: 404, description: 'Demande non trouvée'),
        ]
    )]
    public function cancel(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $mentorship = Mentorship::where('id', $id)->where('mentee_id', $user->id)->first();

        if (! $mentorship) {
            return $this->notFound('Demande non trouvée');
        }

        if ($mentorship->status !== 'pending') {
            return $this->error('Vous ne pouvez annuler qu\'une demande en attente.', 400);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $mentorship->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return $this->success(null, 'Demande annulée avec succès.');
    }

    private function formatMentorship(Mentorship $mentorship): array
    {
        return [
            'id' => $mentorship->id,
            'status' => $mentorship->status,
            'request_message' => $mentorship->request_message,
            'mentor' => [
                'id' => $mentorship->mentor->id,
                'name' => $mentorship->mentor->name,
                'avatar' => $mentorship->mentor->avatar_url,
                'specialization' => $mentorship->mentor->mentorProfile?->specialization,
            ],
            'created_at' => $mentorship->created_at->toISOString(),
        ];
    }
}
