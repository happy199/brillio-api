<?php

namespace Tests\Feature\Mentorship;

use App\Models\User;
use App\Models\Mentorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_jeune_can_request_mentorship()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        // Create JeuneProfile for middleware
        $jeune->jeuneProfile()->create(['is_public' => true]);

        $mentor = User::factory()->mentor()->create();
        // Create MentorProfile for middleware
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech'
        ]);

        $response = $this->actingAs($jeune)->post(route('jeune.mentorship.request'), [
            'mentor_id' => $mentor->id,
            'message' => 'Hello, I would like you to be my mentor.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mentorships', [
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'pending',
        ]);
    }

    public function test_mentor_can_propose_session()
    {
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
        ]);

        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $jeune->jeuneProfile()->create(['is_public' => true]);

        $response = $this->actingAs($mentor)->post(route('mentor.mentorship.sessions.store'), [
            'title' => 'First Session',
            'mentee_ids' => [$jeune->id],
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'is_paid' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mentoring_sessions', [
            'mentor_id' => $mentor->id,
            'title' => 'First Session',
            'status' => 'confirmed',
        ]);
    }

    public function test_jeune_can_propose_session()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $jeune->jeuneProfile()->create(['is_public' => true]);

        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
        ]);

        // Need mentorship relationship to propose session
        Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($jeune)->post(route('jeune.sessions.store'), [
            'mentor_id' => $mentor->id,
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'title' => 'Question about React',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mentoring_sessions', [
            'mentor_id' => $mentor->id,
            'title' => 'Question about React',
            'status' => 'proposed',
            'created_by' => 'mentee',
        ]);
    }

    public function test_mentor_can_accept_mentorship()
    {
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech'
        ]);

        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $jeune->jeuneProfile()->create(['is_public' => true]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mentor)->post(route('mentor.mentorship.accept', $mentorship));

        $response->assertRedirect();
        $this->assertDatabaseHas('mentorships', [
            'id' => $mentorship->id,
            'status' => 'accepted',
        ]);
    }
}