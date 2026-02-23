<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personality\SubmitTestRequest;
use App\Services\PersonalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour le test de personnalité
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
}
