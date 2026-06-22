<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\PersonalityController as V1PersonalityController;
use App\Http\Requests\Personality\SubmitTestRequest;
use App\Models\PersonalityQuestion;
use App\Services\BrillioIAService;
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
}
