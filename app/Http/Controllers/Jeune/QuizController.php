<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Affiche le formulaire du quiz.
     */
    public function show(Quiz $quiz)
    {
        // On s'assure que la ressource parente est bien accessible
        $resource = $quiz->resource;

        if (! $resource || ! $resource->is_published || ! $resource->is_validated) {
            abort(404, 'Ressource indisponible.');
        }

        // Si premium, le jeune doit l'avoir débloquée
        if ($resource->is_premium) {
            $hasPurchased = Purchase::where('user_id', auth()->id())
                ->where('item_type', get_class($resource))
                ->where('item_id', $resource->id)
                ->exists();

            if (! $hasPurchased) {
                return redirect()->route('jeune.resources.show', $resource)
                    ->with('error', 'Vous devez débloquer la ressource avant de passer le quiz.');
            }
        }

        // Charger les questions avec leurs options (mais on ne donne pas explicitement la réponse au frontend)
        $quiz->load(['questions' => function ($query) {
            $query->orderBy('order', 'asc');
        }, 'questions.options']);

        return view('jeune.quizzes.show', compact('quiz', 'resource'));
    }

    /**
     * Traite la soumission du quiz.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|exists:quiz_options,id',
        ]);

        $user = auth()->user();

        try {
            DB::beginTransaction();

            // Créer une nouvelle tentative
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => 0,
                'max_score' => 0,
                'started_at' => now(), // Idéalement, on aurait pu tracer le début, mais on met maintenant pour simplifier
                'completed_at' => now(),
            ]);

            $score = 0;
            $maxScore = 0;

            $quiz->load('questions.options');

            foreach ($quiz->questions as $question) {
                $maxScore += $question->points;

                $submittedAnswers = $validated['answers'][$question->id] ?? null;
                $submittedOptionIds = is_array($submittedAnswers) ? $submittedAnswers : ($submittedAnswers ? [$submittedAnswers] : []);

                $isCorrect = false;

                if (! empty($submittedOptionIds)) {
                    // Si choix multiple, on vérifie que toutes les bonnes réponses sont cochées, et aucune mauvaise
                    if ($question->type === 'multiple') {
                        $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
                        sort($correctOptionIds);

                        $submittedSorted = $submittedOptionIds;
                        sort($submittedSorted);

                        // Convert to strings for safe comparison
                        $correctOptionIds = array_map('strval', $correctOptionIds);
                        $submittedSorted = array_map('strval', $submittedSorted);

                        if ($correctOptionIds === $submittedSorted) {
                            $isCorrect = true;
                            $score += $question->points;
                        }
                    } else {
                        // Choix unique
                        $submittedOptionId = $submittedOptionIds[0];
                        $option = $question->options->firstWhere('id', $submittedOptionId);
                        if ($option && $option->is_correct) {
                            $isCorrect = true;
                            $score += $question->points;
                        }
                    }

                    // Enregistrer les réponses
                    foreach ($submittedOptionIds as $optId) {
                        $option = $question->options->firstWhere('id', $optId);
                        QuizAttemptAnswer::create([
                            'quiz_attempt_id' => $attempt->id,
                            'quiz_question_id' => $question->id,
                            'quiz_option_id' => $optId,
                            'is_correct' => $option ? $option->is_correct : false,
                        ]);
                    }
                }
            }

            // Mettre à jour le score global
            $attempt->update([
                'score' => $score,
                'max_score' => $maxScore,
            ]);

            DB::commit();

            return redirect()->route('jeune.quizzes.result', $attempt);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de vos réponses.');
        }
    }

    /**
     * Affiche le résultat d'une tentative de quiz.
     */
    public function result(QuizAttempt $attempt)
    {
        // Vérifier que l'utilisateur accède bien à SA tentative
        if ($attempt->user_id !== auth()->id()) {
            abort(403, 'Accès refusé.');
        }

        $attempt->load(['quiz.resource', 'answers.question.options']);
        $quiz = $attempt->quiz;
        $resource = $quiz->resource;

        return view('jeune.quizzes.result', compact('attempt', 'quiz', 'resource'));
    }
}
