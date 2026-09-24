<?php

namespace Tests\Feature\Api\V2;

use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\PersonalityTest;
use App\Models\User;
use App\Services\BrillioIAService;
use App\Services\LinkedInPdfParserService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class MobileWebEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * Personality History Endpoints
     * ========================================================================= */

    public function test_personality_history_requires_authentication(): void
    {
        $response = $this->getJson('/api/v2/personality/history');
        $response->assertStatus(401);
    }

    public function test_personality_history_returns_empty_when_no_tests(): void
    {
        $user = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $response = $this->actingAs($user)->getJson('/api/v2/personality/history');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    public function test_personality_history_returns_user_completed_tests(): void
    {
        $user = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $otherUser = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $test1 = PersonalityTest::create([
            'user_id' => $user->id,
            'personality_type' => 'INTJ',
            'personality_label' => 'Architecte',
            'completed_at' => now()->subDays(5),
            'is_current' => false,
            'traits_scores' => ['E' => 20, 'I' => 80],
        ]);

        $test2 = PersonalityTest::create([
            'user_id' => $user->id,
            'personality_type' => 'ENFP',
            'personality_label' => 'Inspirateur',
            'completed_at' => now(),
            'is_current' => true,
            'traits_scores' => ['E' => 70, 'I' => 30],
        ]);

        // Test belonging to other user
        PersonalityTest::create([
            'user_id' => $otherUser->id,
            'personality_type' => 'ESTJ',
            'personality_label' => 'Directeur',
            'completed_at' => now(),
            'is_current' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/personality/history');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $test2->id)
            ->assertJsonPath('data.0.personality_type', 'ENFP')
            ->assertJsonPath('data.1.id', $test1->id)
            ->assertJsonPath('data.1.personality_type', 'INTJ');
    }

    public function test_personality_history_details_returns_full_data(): void
    {
        $user = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $test = PersonalityTest::create([
            'user_id' => $user->id,
            'personality_type' => 'INTJ',
            'personality_label' => 'Architecte',
            'personality_description' => 'Penseurs imaginatifs et stratèges.',
            'completed_at' => now(),
            'is_current' => true,
            'traits_scores' => ['I' => 80, 'N' => 75, 'T' => 85, 'J' => 90],
            'recommended_careers' => ['Data Scientist', 'Architecte Logiciel'],
        ]);

        $response = $this->actingAs($user)->getJson("/api/v2/personality/history/{$test->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $test->id)
            ->assertJsonPath('data.personality_type', 'INTJ')
            ->assertJsonPath('data.personality_label', 'Architecte')
            ->assertJsonPath('data.recommended_careers.0', 'Data Scientist');
    }

    public function test_personality_history_details_returns_404_for_other_user_test(): void
    {
        $user = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $otherUser = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $test = PersonalityTest::create([
            'user_id' => $otherUser->id,
            'personality_type' => 'INTJ',
            'completed_at' => now(),
            'is_current' => true,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v2/personality/history/{$test->id}");

        $response->assertStatus(404);
    }

    /* =========================================================================
     * Session Prefill Report Endpoint
     * ========================================================================= */

    private function createSessionWithMentorAndMentee(int $credits = 10, bool $hasTranscription = true): array
    {
        $mentor = User::factory()->mentor()->create([
            'credits_balance' => $credits,
        ]);
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'current_position' => 'Dev Lead',
        ]);

        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor->id,
            'title' => 'Séance Découverte Tech',
            'status' => 'completed',
            'scheduled_at' => now()->subHour(),
            'has_transcription' => $hasTranscription,
            'transcription_raw' => [
                ['speaker' => $mentor->name, 'text' => 'Bonjour, comment se passe ton projet ?'],
                ['speaker' => $jeune->name, 'text' => 'Bien, j\'ai commencé React.'],
            ],
        ]);
        $session->mentees()->attach($jeune->id);

        return [$mentor, $jeune, $session];
    }

    public function test_prefill_report_requires_authentication(): void
    {
        $response = $this->postJson('/api/v2/sessions/1/prefill-report');
        $response->assertStatus(401);
    }

    public function test_prefill_report_forbidden_for_non_session_mentor(): void
    {
        [$mentor, $jeune, $session] = $this->createSessionWithMentorAndMentee();
        $otherMentor = User::factory()->mentor()->create();

        $response = $this->actingAs($otherMentor)->postJson("/api/v2/sessions/{$session->id}/prefill-report");

        $response->assertStatus(403);
    }

    public function test_prefill_report_returns_422_when_transcription_missing(): void
    {
        [$mentor, $jeune, $session] = $this->createSessionWithMentorAndMentee(10, false);

        $response = $this->actingAs($mentor)->postJson("/api/v2/sessions/{$session->id}/prefill-report");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_prefill_report_returns_402_when_credits_insufficient(): void
    {
        [$mentor, $jeune, $session] = $this->createSessionWithMentorAndMentee(0, true);

        $response = $this->actingAs($mentor)->postJson("/api/v2/sessions/{$session->id}/prefill-report");

        $response->assertStatus(402)
            ->assertJsonPath('success', false);
    }

    public function test_prefill_report_successfully_deducts_credits_and_returns_summary(): void
    {
        [$mentor, $jeune, $session] = $this->createSessionWithMentorAndMentee(20, true);

        $mockAI = Mockery::mock(BrillioIAService::class);
        $mockAI->shouldReceive('summarizeTranscription')
            ->once()
            ->andReturn([
                'progress' => 'Progrès constatés sur React.',
                'obstacles' => 'Difficultés avec les hooks.',
                'smart_goals' => 'Finir le projet d\'ici vendredi.',
            ]);

        $this->app->instance(BrillioIAService::class, $mockAI);

        $response = $this->actingAs($mentor)->postJson("/api/v2/sessions/{$session->id}/prefill-report");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.suggested_report.progress', 'Progrès constatés sur React.')
            ->assertJsonPath('data.credits_deducted', 5);

        $this->assertEquals(15, $mentor->fresh()->credits_balance);
    }

    /* =========================================================================
     * LinkedIn PDF Import Endpoint
     * ========================================================================= */

    public function test_import_linkedin_forbidden_for_non_mentors(): void
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $file = UploadedFile::fake()->create('profile.pdf', 500, 'application/pdf');

        $response = $this->actingAs($jeune)->postJson('/api/v2/mentor/profile/import-linkedin', [
            'pdf' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_import_linkedin_validates_pdf_file(): void
    {
        $mentor = User::factory()->mentor()->create();

        $response = $this->actingAs($mentor)->postJson('/api/v2/mentor/profile/import-linkedin', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pdf']);
    }

    public function test_import_linkedin_rejects_non_matching_owner(): void
    {
        Storage::fake('local');

        $mentor = User::factory()->mentor()->create([
            'name' => 'Alice Mentor',
            'email' => 'alice@example.com',
        ]);

        $file = UploadedFile::fake()->create('profile.pdf', 500, 'application/pdf');

        $mockParser = Mockery::mock(LinkedInPdfParserService::class);
        $mockParser->shouldReceive('parsePdf')->once()->andReturn([
            'name' => 'Bob Inconnu',
            'contact' => ['email' => 'bob@example.com'],
            'experience' => [],
        ]);
        $mockParser->shouldReceive('sanitizeUtf8')->once()->andReturnArg(0);

        $this->app->instance(LinkedInPdfParserService::class, $mockParser);

        $response = $this->actingAs($mentor)->postJson('/api/v2/mentor/profile/import-linkedin', [
            'pdf' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_import_linkedin_successfully_imports_profile_and_roadmap(): void
    {
        Storage::fake('local');

        $mentor = User::factory()->mentor()->create([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'city' => null,
            'country' => null,
        ]);

        $file = UploadedFile::fake()->create('linkedin_jean.pdf', 500, 'application/pdf');

        $parsedData = [
            'name' => 'Jean Dupont',
            'headline' => 'Tech Lead & Mentor Cloud',
            'summary' => 'Passionné par le cloud et le mentorat.',
            'location' => 'Paris, France',
            'contact' => [
                'email' => 'jean.dupont@example.com',
                'linkedin' => 'linkedin.com/in/jeandupont',
                'website' => 'https://jeandupont.dev',
                'phone' => '+33612345678',
            ],
            'skills' => ['PHP', 'Laravel', 'Docker', 'AWS'],
            'experience' => [
                [
                    'title' => 'Lead Developer',
                    'company' => 'Acme Corp',
                    'description' => 'Management d\'équipe et architecture.',
                    'start_date' => '2020-01-01',
                    'end_date' => '2024-01-01',
                ],
            ],
            'education' => [
                [
                    'degree' => 'Master Informatique',
                    'school' => 'Université Paris-Saclay',
                    'year_start' => '2015',
                    'year_end' => '2020',
                ],
            ],
        ];

        $mockParser = Mockery::mock(LinkedInPdfParserService::class);
        $mockParser->shouldReceive('parsePdf')->once()->andReturn($parsedData);
        $mockParser->shouldReceive('sanitizeUtf8')->once()->andReturnArg(0);

        $this->app->instance(LinkedInPdfParserService::class, $mockParser);

        $response = $this->actingAs($mentor)->postJson('/api/v2/mentor/profile/import-linkedin', [
            'pdf' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.profile.name', 'Jean Dupont')
            ->assertJsonPath('data.profile.experience_count', 1);

        // Verify MentorProfile is updated
        $profile = $mentor->fresh()->mentorProfile;
        $this->assertNotNull($profile);
        $this->assertEquals('Lead Developer', $profile->current_position);
        $this->assertEquals('Acme Corp', $profile->current_company);
        $this->assertEquals('Passionné par le cloud et le mentorat.', $profile->bio);
        $this->assertEquals(1, $profile->linkedin_import_count);

        // Verify User city and country populated
        $this->assertEquals('Paris', $mentor->fresh()->city);
        $this->assertEquals('France', $mentor->fresh()->country);

        // Verify Roadmap steps created
        $this->assertDatabaseHas('roadmap_steps', [
            'mentor_profile_id' => $profile->id,
            'step_type' => 'work',
            'title' => 'Lead Developer',
            'institution_company' => 'Acme Corp',
        ]);

        $this->assertDatabaseHas('roadmap_steps', [
            'mentor_profile_id' => $profile->id,
            'step_type' => 'education',
            'title' => 'Master Informatique',
            'institution_company' => 'Université Paris-Saclay',
        ]);
    }
}
