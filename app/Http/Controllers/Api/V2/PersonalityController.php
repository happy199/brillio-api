<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\PersonalityController as V1PersonalityController;
use App\Http\Requests\Personality\SubmitTestRequest;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityTest;
use App\Services\BrillioIAService;
use App\Services\MbtiCareersService;
use App\Services\PersonalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des tests de personnalité via API
 */
class PersonalityController extends V1PersonalityController
{
    /**
     * Récupère les questions du test de personnalité
     */
    public function questions(Request $request): JsonResponse
    {
        return parent::questions($request);
    }

    /**
     * Soumet les réponses et enregistre le type de personnalité pré-calculé
     */
    public function submit(SubmitTestRequest $request): JsonResponse
    {
        return parent::submit($request);
    }

    /**
     * Récupère le résultat du test d'un utilisateur
     */
    public function result(Request $request, ?int $userId = null): JsonResponse
    {
        return parent::result($request, $userId);
    }

    /**
     * Vérifie si l'utilisateur a complété le test
     */
    public function status(Request $request): JsonResponse
    {
        return parent::status($request);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/personality/questions/dynamic",
     *     summary="Récupère les questions de personnalité reformulées par l'IA",
     *     tags={"Test de personnalité"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Questions reformulées récupérées",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="total_questions", type="integer"),
     *             @OA\Property(property="questions", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="is_personalized", type="boolean")
     *         )
     *     )
     * )
     */
    public function dynamicQuestions(Request $request, BrillioIAService $brillioIAService): JsonResponse
    {
        $user = $request->user();

        // Check cache
        if (! empty($user->mbti_reformulated_questions)) {
            return $this->success([
                'total_questions' => count($user->mbti_reformulated_questions),
                'questions' => $user->mbti_reformulated_questions,
                'is_personalized' => true,
                'from_cache' => true,
            ]);
        }

        $questions = PersonalityQuestion::getAllFormatted('fr');

        $onboarding = $user->onboarding_data ?? [];
        $situation = $onboarding['current_situation'] ?? 'étudiant';
        $education = $onboarding['education_level'] ?? '';

        $situationMap = [
            'etudiant' => 'étudiant',
            'recherche_emploi' => 'jeune diplômé en recherche d\'emploi',
            'emploi' => 'salarié',
            'entrepreneur' => 'entrepreneur',
        ];

        $educationMap = [
            'college' => 'collégien (élève au collège)',
            'lycee' => 'lycéen (élève au lycée)',
            'bac' => 'bachelier',
        ];

        $situationText = $situationMap[$situation] ?? $situation;
        if ($situation === 'etudiant' && isset($educationMap[$education])) {
            $situationText = $educationMap[$education];
        }

        try {
            $dynamicQuestions = $brillioIAService->reformulatePersonalityQuestions($questions, $situationText);

            $user->update([
                'mbti_reformulated_questions' => $dynamicQuestions,
                'mbti_reformulated_at' => now(),
            ]);

            return $this->success([
                'total_questions' => count($dynamicQuestions),
                'questions' => $dynamicQuestions,
                'is_personalized' => true,
                'context' => $situationText,
                'from_cache' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur API dynamicQuestions: '.$e->getMessage());

            return $this->success([
                'total_questions' => count($questions),
                'questions' => $questions,
                'is_personalized' => false,
                'error' => 'Fallback to original questions due to AI error',
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/personality/history",
     *     summary="Historique des tests de personnalité passés par l'utilisateur",
     *     tags={"Test de personnalité"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(response=200, description="Historique des tests")
     * )
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $tests = PersonalityTest::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get()
            ->map(function ($test) {
                return [
                    'id' => $test->id,
                    'personality_type' => $test->personality_type,
                    'personality_label' => $test->personality_label,
                    'completed_at' => $test->completed_at?->toISOString(),
                    'completed_at_formatted' => $test->completed_at?->format('d/m/Y à H:i'),
                    'is_current' => (bool) $test->is_current,
                ];
            });

        return $this->success($tests);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/personality/history/{id}",
     *     summary="Détails complets d'un test historique spécifique",
     *     tags={"Test de personnalité"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Détails du test"),
     *     @OA\Response(response=404, description="Test non trouvé")
     * )
     */
    public function historyDetails(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $test = PersonalityTest::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $test) {
            return $this->notFound('Test non trouvé.');
        }

        $typeInfo = PersonalityService::TYPE_DESCRIPTIONS[$test->personality_type] ?? [
            'label' => $test->personality_type,
            'description' => 'Type de personnalité '.$test->personality_type,
        ];

        $careers = $test->recommended_careers;
        if (empty($careers)) {
            $careers = MbtiCareersService::getCareersForType($test->personality_type);
        }

        $sectors = MbtiCareersService::getSectorsForType($test->personality_type);

        return $this->success([
            'id' => $test->id,
            'personality_type' => $test->personality_type,
            'personality_label' => $test->personality_label ?? $typeInfo['label'],
            'personality_description' => $test->personality_description ?? $typeInfo['description'],
            'traits_scores' => $test->traits_scores,
            'recommended_careers' => $careers,
            'recommended_sectors' => $sectors,
            'completed_at' => $test->completed_at ? $test->completed_at->toISOString() : null,
            'completed_at_formatted' => $test->completed_at ? $test->completed_at->format('d/m/Y à H:i') : null,
            'is_current' => (bool) $test->is_current,
        ]);
    }
}
