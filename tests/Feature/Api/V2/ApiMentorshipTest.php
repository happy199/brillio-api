<?php

namespace Tests\Feature\Api\V2;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMentorshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_accept_mentorship()
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
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mentor)->postJson("/api/v2/mentorships/{$mentorship->id}/accept");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'accepted']);

        $this->assertDatabaseHas('mentorships', [
            'id' => $mentorship->id,
            'status' => 'accepted',
        ]);
    }

    public function test_mentor_can_refuse_mentorship()
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
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mentor)->postJson("/api/v2/mentorships/{$mentorship->id}/refuse", [
            'refusal_reason' => 'Pas dispo',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'refused']);
    }

    public function test_user_can_disconnect_mentorship()
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

        $response = $this->actingAs($jeune)->postJson("/api/v2/mentorships/{$mentorship->id}/disconnect", [
            'diction_reason' => 'Objectif atteint',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'disconnected']);
    }
}
