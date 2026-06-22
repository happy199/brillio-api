<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personality\SubmitTestRequest;
use App\Services\PersonalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des tests de personnalité via API
 */
class PersonalityController extends Controller
{
    public function __construct(
        private PersonalityService $personalityService
    ) {}

    /**
     * Récupère les questions du test de personnalité
     */
    public function questions(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'fr');
        $questions = $this->personalityService->getQuestions($locale);

        return $this->success([
            'total_questions' => count($questions),
            'questions' => $questions,
        ]);
    }

    /**
     * Soumet les réponses et enregistre le type de personnalité pré-calculé
     */
    public function submit(SubmitTestRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $personalityTest = $this->personalityService->savePreCalculatedResult(
            $user,
            $validated['personality_type'],
            $validated['personality_label'],
            $validated['personality_description'],
            $validated['traits_scores'],
            $validated['responses']
        );

        return $this->success([
            'personality_type' => $personalityTest->personality_type,
            'personality_label' => $personalityTest->personality_label,
            'personality_description' => $personalityTest->personality_description,
            'traits_scores' => $personalityTest->traits_scores,
            'completed_at' => $personalityTest->completed_at->toISOString(),
        ], 'Test de personnalité complété avec succès');
    }

    /**
     * Récupère le résultat du test d'un utilisateur
     */
    public function result(Request $request, ?int $userId = null): JsonResponse
    {
        // Si pas d'userId fourni, utiliser l'utilisateur connecté
        if ($userId === null) {
            $user = $request->user();
        } else {
            // Vérifier que l'utilisateur demande ses propres résultats ou est admin
            if ($userId !== $request->user()->id && ! $request->user()->isAdmin()) {
                return $this->forbidden('Vous ne pouvez pas voir les résultats d\'autres utilisateurs');
            }
            $user = \App\Models\User::find($userId);
        }

        if (! $user) {
            return $this->notFound('Utilisateur non trouvé');
        }

        $result = $this->personalityService->getResult($user);

        if (! $result || ! $result->isCompleted()) {
            return $this->notFound('Aucun test de personnalité complété');
        }

        return $this->success([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'personality_type' => $result->personality_type,
            'personality_label' => $result->personality_label,
            'personality_description' => $result->personality_description,
            'traits_scores' => $result->traits_scores,
            'completed_at' => $result->completed_at->toISOString(),
        ]);
    }

    /**
     * Vérifie si l'utilisateur a complété le test
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $hasCompleted = $this->personalityService->hasCompletedTest($user);

        $data = [
            'has_completed' => $hasCompleted,
        ];

        if ($hasCompleted) {
            $result = $user->personalityTest;
            $data['personality_type'] = $result->personality_type;
            $data['personality_label'] = $result->personality_label;
            $data['completed_at'] = $result->completed_at->toISOString();
        }

        return $this->success($data);
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
    public function dynamicQuestions(Request $request, \App\Services\BrillioIAService $brillioIAService): JsonResponse
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

        $questions = \App\Models\PersonalityQuestion::getAllFormatted('fr');

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
            \Illuminate\Support\Facades\Log::error('Erreur API dynamicQuestions: '.$e->getMessage());

            return $this->success([
                'total_questions' => count($questions),
                'questions' => $questions,
                'is_personalized' => false,
                'error' => 'Fallback to original questions due to AI error',
            ]);
        }
    }
}
