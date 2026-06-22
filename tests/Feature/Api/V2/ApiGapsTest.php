<?php

namespace Tests\Feature\Api\V2;

use App\Models\CreditPack;
use App\Models\Establishment;
use App\Models\MentoringSession;
use App\Models\PersonalityTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiGapsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_get_user_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v2/user');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user']]);
    }

    public function test_api_update_user_profile()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'phone' => '+22990000000',
        ]);

        $response = $this->actingAs($user)->postJson('/api/v2/user/profile', [
            'name' => 'Updated Name',
            'phone' => '+22991111111',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Updated Name');

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_api_get_onboarding_options()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v2/onboarding');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['countries', 'education_levels', 'situations']]);
    }

    public function test_api_complete_onboarding_success()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v2/onboarding/complete', [
            'birth_date' => '2005-05-12',
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => '771234567',
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertTrue($user->onboarding_completed);
        $this->assertEquals('+221771234567', $user->phone);
    }

    public function test_api_complete_onboarding_fails_validation()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v2/onboarding/complete', [
            'birth_date' => '2005-05-12',
            'country' => 'Senegal',
            'city' => 'Dakar',
            'phone' => '7712345', // Invalid phone length
            'education_level' => 'bac',
            'current_situation' => 'etudiant',
            'interests' => ['Technologie', 'Finance', 'Marketing', 'Design', 'Sante'],
            'goals' => ['orientation'],
            'how_found_us' => 'social_media',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_api_get_recommended_establishments()
    {
        $user = User::factory()->create();
        PersonalityTest::create([
            'user_id' => $user->id,
            'personality_type' => 'INFP',
            'personality_label' => 'Médiateur',
            'personality_description' => 'Les INFP sont empathiques...',
            'traits_scores' => [],
            'raw_responses' => [],
            'completed_at' => now(),
            'is_current' => true,
        ]);

        $establishment = Establishment::create([
            'name' => 'Test School',
            'description' => 'A test school',
            'mbti_types' => ['INFP'],
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/establishments/recommended');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mbti_type', 'INFP');
    }

    public function test_api_quick_interest_establishment()
    {
        $user = User::factory()->create([
            'phone' => '+22990000000',
        ]);
        $establishment = Establishment::create([
            'name' => 'Test School',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v2/establishments/{$establishment->id}/interest-quick");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_api_track_click_establishment()
    {
        $user = User::factory()->create();
        $establishment = Establishment::create([
            'name' => 'Test School',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v2/establishments/{$establishment->id}/track-click");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_api_purchase_credit_pack()
    {
        $user = User::factory()->create(['user_type' => 'jeune']);
        $pack = CreditPack::create([
            'user_type' => 'jeune',
            'price' => 1000,
            'credits' => 10,
            'name' => 'Test Pack',
            'is_active' => true,
        ]);

        Http::fake([
            '*/payments/initialize' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 'moneroo_tx_123',
                    'checkout_url' => 'https://checkout.moneroo.io/pay/moneroo_tx_123',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v2/wallet/purchase', [
            'pack_id' => $pack->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['checkout_url', 'transaction_id']]);
    }

    public function test_api_unlock_history()
    {
        $user = User::factory()->create(['credits_balance' => 10]);
        $profile = \App\Models\JeuneProfile::create([
            'user_id' => $user->id,
            'has_unlocked_session_history' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v2/sessions/unlock-history');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.new_balance', 5);

        $this->assertTrue((bool) $profile->fresh()->has_unlocked_session_history);
    }

    public function test_api_download_compiled_reports()
    {
        $user = User::factory()->create(['credits_balance' => 10]);
        $mentor = User::factory()->create(['user_type' => 'mentor']);
        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor->id,
            'status' => 'completed',
            'report_content' => ['summary' => 'Session completed successfully.'],
        ]);
        $session->mentees()->attach($user->id, ['status' => 'accepted']);

        $response = $this->actingAs($user)->postJson('/api/v2/sessions/compiled-reports', [
            'session_ids' => [$session->id],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $user->fresh()->credits_balance);
    }

    public function test_api_download_transcription_charge()
    {
        $user = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 10,
        ]);
        $mentor = User::factory()->create(['user_type' => 'mentor']);
        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor->id,
            'has_transcription' => true,
        ]);
        $session->mentees()->attach($user->id, ['status' => 'accepted']);

        $response = $this->actingAs($user)->getJson("/api/v2/sessions/{$session->id}/download-transcription");

        $response->assertStatus(200);
        $this->assertEquals(5, $user->fresh()->credits_balance);
    }

    public function test_api_feedback_nudge()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v2/feedback', [
            'rating' => 5,
            'comment' => 'Very helpful sessions.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_api_dynamic_personality_questions()
    {
        $user = User::factory()->create([
            'onboarding_data' => [
                'current_situation' => 'etudiant',
                'education_level' => 'bac',
            ],
        ]);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'questions' => [],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v2/personality/questions/dynamic');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
