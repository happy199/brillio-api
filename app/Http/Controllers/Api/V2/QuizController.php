<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Controller pour la gestion des quiz via API
 */
class QuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/quizzes/{quiz}",
     *     summary="Afficher les détails d'un quiz avec ses questions",
     *     tags={"Quiz"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="quiz", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Détails du quiz")
     * )
     */
    public function show(Request $request, $quizId): JsonResponse
    {
        $quiz = \App\Models\Quiz::with('questions.options')->findOrFail($quizId);

        if (! $quiz->is_active) {
            return $this->error('Ce quiz n\'est plus disponible.', 404);
        }

        return $this->success($quiz);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/quizzes/{quiz}/submit",
     *     summary="Soumettre les réponses à un quiz et obtenir le score",
     *     tags={"Quiz"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="quiz", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"answers"},
     *
     *             @OA\Property(property="answers", type="object", example={"1": 3, "2": 5})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Quiz soumis et attempt enregistré")
     * )
     */
    public function submit(Request $request, $quizId): JsonResponse
    {
        $quiz = \App\Models\Quiz::findOrFail($quizId);
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|exists:quiz_options,id',
        ]);

        $score = 0;
        $totalQuestions = $quiz->questions()->count();

        foreach ($request->answers as $questionId => $optionId) {
            $option = \App\Models\QuizOption::find($optionId);
            if ($option && $option->is_correct) {
                $score++;
            }
        }

        $attempt = \App\Models\QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'max_score' => $totalQuestions,
            'total_questions' => $totalQuestions,
            'answers_data' => $request->answers,
            'completed_at' => now(),
        ]);

        return $this->success($attempt, 'Quiz soumis avec succès.');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/quizzes/result/{attempt}",
     *     summary="Voir le résultat d'une tentative de quiz",
     *     tags={"Quiz"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="attempt", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Détails de la tentative")
     * )
     */
    public function result(Request $request, $attemptId): JsonResponse
    {
        $attempt = \App\Models\QuizAttempt::with('quiz.questions.options')->findOrFail($attemptId);

        if ($attempt->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        return $this->success($attempt);
    }
}
