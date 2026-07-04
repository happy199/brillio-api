<?php

namespace App\Traits;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Resource;

trait ManagesQuizzes
{
    /**
     * Sauvegarde ou met à jour les quiz associés à une ressource
     *
     * @param  string|null  $quizzesData  JSON string des données du quiz
     */
    protected function saveQuizzes(Resource $resource, ?string $quizzesData): void
    {
        if (empty($quizzesData)) {
            // Delete all quizzes if no data
            $resource->quizzes()->delete();

            return;
        }

        $quizzes = json_decode($quizzesData, true);

        if (! is_array($quizzes)) {
            return;
        }

        $incomingQuizIds = [];

        foreach ($quizzes as $qData) {
            if (empty($qData['title'])) {
                continue;
            }

            if (! empty($qData['id'])) {
                $quiz = Quiz::find($qData['id']);
                if ($quiz && $quiz->resource_id == $resource->id) {
                    $quiz->update([
                        'title' => $qData['title'],
                        'description' => $qData['description'] ?? null,
                    ]);
                    $incomingQuizIds[] = $quiz->id;
                } else {
                    continue;
                }
            } else {
                $quiz = Quiz::create([
                    'resource_id' => $resource->id,
                    'title' => $qData['title'],
                    'description' => $qData['description'] ?? null,
                    'is_active' => true,
                ]);
                $incomingQuizIds[] = $quiz->id;
            }

            $incomingQuestionIds = [];
            if (! empty($qData['questions']) && is_array($qData['questions'])) {
                foreach ($qData['questions'] as $qIndex => $questionData) {
                    if (empty($questionData['question_text'])) {
                        continue;
                    }

                    if (! empty($questionData['id'])) {
                        $question = QuizQuestion::find($questionData['id']);
                        if ($question && $question->quiz_id == $quiz->id) {
                            $question->update([
                                'question_text' => $questionData['question_text'],
                                'type' => $questionData['type'] ?? 'single',
                                'points' => $questionData['points'] ?? 1,
                                'order' => $qIndex,
                            ]);
                            $incomingQuestionIds[] = $question->id;
                        } else {
                            continue;
                        }
                    } else {
                        $question = QuizQuestion::create([
                            'quiz_id' => $quiz->id,
                            'question_text' => $questionData['question_text'],
                            'type' => $questionData['type'] ?? 'single',
                            'points' => $questionData['points'] ?? 1,
                            'order' => $qIndex,
                        ]);
                        $incomingQuestionIds[] = $question->id;
                    }

                    $incomingOptionIds = [];
                    if (! empty($questionData['options']) && is_array($questionData['options'])) {
                        foreach ($questionData['options'] as $optionData) {
                            if (empty($optionData['option_text'])) {
                                continue;
                            }

                            if (! empty($optionData['id'])) {
                                $option = QuizOption::find($optionData['id']);
                                if ($option && $option->quiz_question_id == $question->id) {
                                    $option->update([
                                        'option_text' => $optionData['option_text'],
                                        'is_correct' => ! empty($optionData['is_correct']),
                                        'explanation' => $optionData['explanation'] ?? null,
                                    ]);
                                    $incomingOptionIds[] = $option->id;
                                } else {
                                    continue;
                                }
                            } else {
                                $option = QuizOption::create([
                                    'quiz_question_id' => $question->id,
                                    'option_text' => $optionData['option_text'],
                                    'is_correct' => ! empty($optionData['is_correct']),
                                    'explanation' => $optionData['explanation'] ?? null,
                                ]);
                                $incomingOptionIds[] = $option->id;
                            }
                        }
                    }
                    // Delete missing options
                    QuizOption::where('quiz_question_id', $question->id)
                        ->whereNotIn('id', $incomingOptionIds)->delete();
                }
            }
            // Delete missing questions
            QuizQuestion::where('quiz_id', $quiz->id)
                ->whereNotIn('id', $incomingQuestionIds)->delete();
        }

        // Delete missing quizzes
        Quiz::where('resource_id', $resource->id)
            ->whereNotIn('id', $incomingQuizIds)->delete();
    }
}
