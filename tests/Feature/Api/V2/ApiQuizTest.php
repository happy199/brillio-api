<?php

namespace Tests\Feature\Api\V2;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_quiz()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $resource = \App\Models\Resource::create([
            'user_id' => $jeune->id, // or create a mentor
            'title' => 'Test Resource',
            'type' => 'article',
            'price' => 0,
            'is_active' => true,
        ]);

        $quiz = Quiz::create([
            'resource_id' => $resource->id,
            'title' => 'Test Quiz',
            'is_active' => true,
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What is 2+2?',
            'type' => 'single',
        ]);

        $optionCorrect = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        $optionIncorrect = QuizOption::create([
            'quiz_question_id' => $question->id,
            'option_text' => '5',
            'is_correct' => false,
        ]);

        $response = $this->actingAs($jeune)->postJson("/api/v2/quizzes/{$quiz->id}/submit", [
            'answers' => [
                $question->id => $optionCorrect->id,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['score' => 1, 'max_score' => 1]);

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $jeune->id,
            'score' => 1,
        ]);
    }

    public function test_user_cannot_access_inactive_quiz()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $resource = \App\Models\Resource::create([
            'user_id' => $jeune->id, // or create a mentor
            'title' => 'Test Resource',
            'type' => 'article',
            'price' => 0,
            'is_active' => true,
        ]);

        $quiz = Quiz::create([
            'resource_id' => $resource->id,
            'title' => 'Test Quiz',
            'is_active' => false,
        ]);

        $response = $this->actingAs($jeune)->getJson("/api/v2/quizzes/{$quiz->id}");

        $response->assertStatus(404);
    }
}
