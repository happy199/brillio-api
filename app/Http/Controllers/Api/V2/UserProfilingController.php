<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\UserDetailedProfile;
use App\Models\UserFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="UserProfiling", description="Gestion des nudges, feedbacks et profilage situationnel")
 */
class UserProfilingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v2/feedback",
     *     summary="Enregistre le feedback d'un utilisateur",
     *     tags={"UserProfiling"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"rating", "comment"},
     *
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="comment", type="string", maxLength=1000, example="Super service !")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Feedback enregistré",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="needs_situation", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function storeFeedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        UserFeedback::create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $user->update([
            'last_feedback_at' => now(),
            'last_rating' => $validated['rating'],
        ]);

        return $this->success([
            'needs_situation' => $user->needsSituationNudge(),
        ], 'Merci pour votre retour !');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/feedback/skip",
     *     summary="Reporte le nudge de feedback",
     *     tags={"UserProfiling"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Nudge reporté",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function skipFeedback(Request $request): JsonResponse
    {
        $request->user()->update(['last_feedback_at' => now()]);

        return $this->success(null, 'Nudge reporté');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/situation",
     *     summary="Enregistre la situation détaillée d'un jeune",
     *     tags={"UserProfiling"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"data"},
     *
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="city", type="string", example="Cotonou"),
     *                 @OA\Property(property="institution", type="string", example="Université"),
     *                 @OA\Property(property="specialization", type="string", example="Informatique")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Situation mise à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function storeSituation(Request $request): JsonResponse
    {
        $user = $request->user();
        $baseSituation = $user->onboarding_data['current_situation'] ?? 'autre';

        $validated = $request->validate([
            'data' => 'required|array',
            'data.institution' => 'nullable|string|max:255',
            'data.class_level' => 'nullable|string|max:50',
            'data.specialization' => 'nullable|string|max:255',
            'data.target_diploma' => 'nullable|string|max:255',
            'data.target_diploma_other' => 'nullable|string|max:255',
            'data.company' => 'nullable|string|max:255',
            'data.position' => 'nullable|string|max:255',
            'data.sector' => 'nullable|string|max:255',
            'data.experience' => 'nullable|string|max:50',
            'data.last_education' => 'nullable|string|max:255',
            'data.target_field' => 'nullable|string|max:255',
            'data.tuition_range' => 'nullable|string|max:50',
            'data.salary_range' => 'nullable|string|max:50',
            'data.city' => 'required|string|max:255',
        ]);

        UserDetailedProfile::create([
            'user_id' => $user->id,
            'status' => $baseSituation,
            'data' => $validated['data'],
        ]);

        $user->update([
            'last_situation_update_at' => now(),
        ]);

        return $this->success(null, 'Situation mise à jour avec succès !');
    }

    /**
     * @OA\Post(
     *     path="/api/v2/situation/skip",
     *     summary="Reporte le nudge de situation",
     *     tags={"UserProfiling"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Nudge reporté",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function skipSituation(Request $request): JsonResponse
    {
        $request->user()->update(['last_situation_update_at' => now()]);

        return $this->success(null, 'Nudge reporté');
    }
}
