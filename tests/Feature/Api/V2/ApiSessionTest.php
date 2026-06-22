<?php

namespace Tests\Feature\Api\V2;

use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_accept_session()
    {
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech',
        ]);
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor->id,
            'status' => 'pending',
            'scheduled_at' => now()->addDays(1),
        ]);
        $session->mentees()->attach($jeune->id);

        $response = $this->actingAs($mentor)->postJson("/api/v2/sessions/{$session->id}/accept");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'confirmed']);

        $this->assertDatabaseHas('mentoring_sessions', [
            'id' => $session->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_mentor_can_report_session()
    {
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech',
        ]);
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor->id,
            'status' => 'confirmed',
            'scheduled_at' => now()->subDays(1),
        ]);
        $session->mentees()->attach($jeune->id);

        $response = $this->actingAs($mentor)->putJson("/api/v2/sessions/{$session->id}/report", [
            'report_content' => ['progress' => 'Très bonne séance, le jeune est motivé.'],
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);

        $this->assertDatabaseHas('mentoring_sessions', [
            'id' => $session->id,
            'status' => 'completed',
            'report_content' => json_encode(['progress' => 'Très bonne séance, le jeune est motivé.']),
        ]);
    }

    public function test_mentor_cannot_modify_other_mentor_session()
    {
        $mentor1 = User::factory()->mentor()->create();
        $mentor2 = User::factory()->mentor()->create();
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor1->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $session = MentoringSession::factory()->create([
            'mentor_id' => $mentor1->id,
            'status' => 'pending',
        ]);
        $session->mentees()->attach($jeune->id);

        $response = $this->actingAs($mentor2)->postJson("/api/v2/sessions/{$session->id}/accept");

        $response->assertStatus(403);
    }
}
