<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\CreateProfileRequest;
use App\Http\Requests\Mentor\CreateRoadmapStepRequest;
use App\Http\Requests\Mentor\UpdateRoadmapStepRequest;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour la gestion des profils mentors et roadmaps
 */
class MentorController extends Controller
{
    /**
     * Liste des mentors publiés (pour les jeunes)
     */
    #[OA\Get(
        path: "/api/v1/mentors",
        summary: "Liste des mentors publiés",
        tags: ["Mentors"],
        parameters: [
            new OA\Parameter(name: "specialization", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "country", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Liste des mentors")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = MentorProfile::published()
            ->with(['user', 'roadmapSteps']);

        // Filtres
        if ($specialization = $request->get('specialization')) {
            $query->bySpecialization($specialization);
        }

        if ($country = $request->get('country')) {
            $query->whereHas('user', function ($q) use ($country) {
                $q->where('country', $country);
            });
        }

        // Recherche par nom
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->integer('per_page', 15);
        $mentors = $query->paginate($perPage);

        return $this->success([
            'mentors' => $mentors->map(fn ($mentor) => $this->formatMentorForList($mentor)),
            'pagination' => [
                'current_page' => $mentors->currentPage(),
                'last_page' => $mentors->lastPage(),
                'per_page' => $mentors->perPage(),
                'total' => $mentors->total(),
            ],
        ]);
    }

    /**
     * Détail d'un mentor avec sa roadmap complète
     */
    #[OA\Get(
        path: "/api/v1/mentors/{id}",
        summary: "Détail d'un mentor",
        tags: ["Mentors"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détail du mentor"),
            new OA\Response(response: 404, description: "Mentor non trouvé")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $mentor = MentorProfile::with(['user', 'roadmapSteps'])
            ->where('id', $id)
            ->where('is_published', true)
            ->first();

        if (! $mentor) {
            return $this->notFound('Mentor non trouvé');
        }

        return $this->success([
            'mentor' => $this->formatMentorDetail($mentor),
        ]);
    }

    /**
     * Crée ou met à jour le profil mentor de l'utilisateur connecté
     */
    #[OA\Post(
        path: "/api/v1/mentor/profile",
        summary: "Crée ou met à jour le profil mentor",
        tags: ["Mentors"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Profil mis à jour")
        ]
    )]
    public function createOrUpdateProfile(CreateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est bien un mentor
        if (! $user->isMentor()) {
            return $this->forbidden('Seuls les mentors peuvent créer un profil mentor');
        }

        $validated = $request->validated();

        $profile = MentorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $validated['bio'] ?? null,
                'current_position' => $validated['current_position'] ?? null,
                'current_company' => $validated['current_company'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
            ]
        );

        return $this->success([
            'mentor_profile' => $this->formatMentorDetail($profile->load(['user', 'roadmapSteps'])),
        ], $profile->wasRecentlyCreated ? 'Profil mentor créé' : 'Profil mentor mis à jour');
    }

    /**
     * Récupère le profil mentor de l'utilisateur connecté
     */
    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isMentor()) {
            return $this->forbidden('Seuls les mentors ont un profil mentor');
        }

        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Profil mentor non créé');
        }

        $profile->load('roadmapSteps');

        return $this->success([
            'mentor_profile' => $this->formatMentorDetail($profile),
        ]);
    }

    /**
     * Ajoute une étape au parcours
     */
    public function addRoadmapStep(CreateRoadmapStepRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Créez d\'abord votre profil mentor');
        }

        $validated = $request->validated();

        // Déterminer la position (à la fin par défaut)
        $maxPosition = $profile->roadmapSteps()->max('position') ?? 0;
        $position = $validated['position'] ?? ($maxPosition + 1);

        $step = $profile->roadmapSteps()->create([
            'step_type' => $validated['step_type'],
            'title' => $validated['title'],
            'institution_company' => $validated['institution_company'] ?? null,
            'location' => $validated['location'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'position' => $position,
        ]);

        return $this->created([
            'roadmap_step' => $this->formatRoadmapStep($step),
        ], 'Étape ajoutée au parcours');
    }

    /**
     * Met à jour une étape du parcours
     */
    public function updateRoadmapStep(UpdateRoadmapStepRequest $request, int $stepId): JsonResponse
    {
        $user = $request->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Profil mentor non trouvé');
        }

        $step = $profile->roadmapSteps()->where('id', $stepId)->first();

        if (! $step) {
            return $this->notFound('Étape non trouvée');
        }

        $step->update($request->validated());

        return $this->success([
            'roadmap_step' => $this->formatRoadmapStep($step),
        ], 'Étape mise à jour');
    }

    /**
     * Supprime une étape du parcours
     */
    public function deleteRoadmapStep(Request $request, int $stepId): JsonResponse
    {
        $user = $request->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Profil mentor non trouvé');
        }

        $step = $profile->roadmapSteps()->where('id', $stepId)->first();

        if (! $step) {
            return $this->notFound('Étape non trouvée');
        }

        $step->delete();

        return $this->success(null, 'Étape supprimée');
    }

    /**
     * Réorganise les étapes du parcours
     */
    public function reorderSteps(Request $request): JsonResponse
    {
        $request->validate([
            'steps' => 'required|array',
            'steps.*.id' => 'required|integer',
            'steps.*.position' => 'required|integer|min:0',
        ]);

        $user = $request->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Profil mentor non trouvé');
        }

        foreach ($request->input('steps') as $stepData) {
            $profile->roadmapSteps()
                ->where('id', $stepData['id'])
                ->update(['position' => $stepData['position']]);
        }

        return $this->success(null, 'Ordre des étapes mis à jour');
    }

    /**
     * Publie ou dépublie le profil mentor
     */
    public function publish(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return $this->notFound('Créez d\'abord votre profil mentor');
        }

        // Vérifier que le profil est complet avant publication
        $publish = $request->boolean('publish', true);

        if ($publish && ! $profile->isComplete()) {
            return $this->error(
                'Votre profil n\'est pas complet. Ajoutez une bio, un poste actuel, une spécialisation et au moins une étape de parcours.',
                422
            );
        }

        $profile->is_published = $publish;
        $profile->save();

        $message = $publish ? 'Profil publié avec succès' : 'Profil dépublié';

        return $this->success([
            'is_published' => $profile->is_published,
        ], $message);
    }

    /**
     * Liste des spécialisations disponibles
     */
    #[OA\Get(
        path: "/api/v1/specializations",
        summary: "Liste des spécialisations disponibles",
        tags: ["Mentors"],
        responses: [
            new OA\Response(response: 200, description: "Liste des spécialisations")
        ]
    )]
    public function specializations(): JsonResponse
    {
        return $this->success([
            'specializations' => MentorProfile::SPECIALIZATIONS,
        ]);
    }

    /**
     * Formate un mentor pour la liste
     */
    private function formatMentorForList(MentorProfile $mentor): array
    {
        return [
            'id' => $mentor->id,
            'user' => [
                'id' => $mentor->user->id,
                'name' => $mentor->user->name,
                'profile_photo_url' => $mentor->user->avatar_url,
                'country' => $mentor->user->country,
                'city' => $mentor->user->city,
            ],
            'current_position' => $mentor->current_position,
            'current_company' => $mentor->current_company,
            'specialization' => $mentor->specialization,
            'specialization_label' => $mentor->specialization_label,
            'years_of_experience' => $mentor->years_of_experience,
            'roadmap_steps_count' => $mentor->roadmapSteps->count(),
        ];
    }

    /**
     * Formate un mentor avec tous les détails
     */
    private function formatMentorDetail(MentorProfile $mentor): array
    {
        $mentor->load(['user', 'roadmapSteps']);

        return [
            'id' => $mentor->id,
            'user' => [
                'id' => $mentor->user->id,
                'name' => $mentor->user->name,
                'email' => $mentor->user->email,
                'profile_photo_url' => $mentor->user->avatar_url,
                'linkedin_url' => $mentor->user->linkedin_url,
                'country' => $mentor->user->country,
                'city' => $mentor->user->city,
            ],
            'bio' => $mentor->bio,
            'current_position' => $mentor->current_position,
            'current_company' => $mentor->current_company,
            'specialization' => $mentor->specialization,
            'specialization_label' => $mentor->specialization_label,
            'years_of_experience' => $mentor->years_of_experience,
            'is_published' => $mentor->is_published,
            'is_complete' => $mentor->isComplete(),
            'roadmap' => $mentor->roadmapSteps->map(fn ($step) => $this->formatRoadmapStep($step)),
            'created_at' => $mentor->created_at->toISOString(),
            'updated_at' => $mentor->updated_at->toISOString(),
        ];
    }

    /**
     * Formate une étape de parcours
     */
    private function formatRoadmapStep(RoadmapStep $step): array
    {
        return [
            'id' => $step->id,
            'step_type' => $step->step_type,
            'step_type_label' => $step->step_type_label,
            'title' => $step->title,
            'institution_company' => $step->institution_company,
            'location' => $step->location,
            'start_date' => $step->start_date?->format('Y-m-d'),
            'end_date' => $step->end_date?->format('Y-m-d'),
            'duration' => $step->duration,
            'is_ongoing' => $step->isOngoing(),
            'description' => $step->description,
            'position' => $step->position,
        ];
    }
}