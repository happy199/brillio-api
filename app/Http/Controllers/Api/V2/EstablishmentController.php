<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentInterest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Etablissements", description="Gestion des recommandations et des intérêts pour les établissements")
 */
class EstablishmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/establishments/recommended",
     *     summary="Récupère les établissements recommandés basés sur le type MBTI",
     *     tags={"Etablissements"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des établissements recommandés",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="mbti_type", type="string", example="INFP"),
     *             @OA\Property(property="establishments", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();
        $test = $user->personalityTest;

        if (! $test || ! $test->isCompleted()) {
            return $this->success([
                'mbti_type' => null,
                'establishments' => [],
            ]);
        }

        $mbtiType = $test->personality_type;

        $establishments = Establishment::where('is_published', true)
            ->whereJsonContains('mbti_types', $mbtiType)
            ->get()
            ->map(function ($est) use ($user) {
                $est->user_has_interest = EstablishmentInterest::where('user_id', $user->id)
                    ->where('establishment_id', $est->id)
                    ->exists();

                return $est;
            });

        return $this->success([
            'mbti_type' => $mbtiType,
            'establishments' => $establishments,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/establishments/{establishment}/interest-quick",
     *     summary="Exprime un intérêt rapide pour un établissement",
     *     tags={"Etablissements"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="establishment", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="phone", type="string", example="+22990909090")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Intérêt enregistré",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Numéro de téléphone manquant")
     * )
     */
    public function quickInterest(Request $request, Establishment $establishment): JsonResponse
    {
        $user = $request->user();

        if ($request->has('phone') && ! empty($request->phone)) {
            $user->update(['phone' => $request->phone]);
        }

        if (empty($user->phone)) {
            return $this->error('Il manque votre numéro de téléphone.', 422);
        }

        EstablishmentInterest::firstOrCreate([
            'user_id' => $user->id,
            'establishment_id' => $establishment->id,
            'type' => 'quick',
        ]);

        return $this->success(null, "{$establishment->name} vous recontactera dans les meilleurs délais.");
    }

    /**
     * @OA\Post(
     *     path="/api/v2/establishments/{establishment}/interest-precise",
     *     summary="Exprime un intérêt précis avec des données de formulaire",
     *     tags={"Etablissements"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="establishment", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"form_data"},
     *
     *             @OA\Property(property="form_data", type="object"),
     *             @OA\Property(property="phone", type="string", example="+22990909090")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Intérêt précis enregistré",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function preciseInterest(Request $request, Establishment $establishment): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'form_data' => 'required|array',
            'phone' => 'sometimes|nullable|string',
        ]);

        if (! empty($request->phone)) {
            $user->update(['phone' => $request->phone]);
        }

        if (empty($user->phone)) {
            return $this->error('Il manque votre numéro de téléphone.', 422);
        }

        EstablishmentInterest::updateOrCreate(
            [
                'user_id' => $user->id,
                'establishment_id' => $establishment->id,
            ],
            [
                'type' => 'precise',
                'form_data' => $validated['form_data'],
            ]
        );

        return $this->success(null, 'Votre demande a été envoyée avec succès à '.$establishment->name);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/establishments/{establishment}/track-click",
     *     summary="Enregistre un clic sur un établissement",
     *     tags={"Etablissements"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="establishment", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Clic enregistré",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function trackClick(Request $request, Establishment $establishment): JsonResponse
    {
        $establishment->clicks()->create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success(null, 'Clic enregistré');
    }
}
