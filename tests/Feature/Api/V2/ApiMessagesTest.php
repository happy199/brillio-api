<?php

namespace Tests\Feature\Api\V2;

use App\Models\Mentorship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_messages()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech',
        ]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        Message::create([
            'mentorship_id' => $mentorship->id,
            'sender_id' => $mentor->id,
            'body' => 'Hello',
        ]);

        $response = $this->actingAs($jeune)->getJson('/api/v2/messages');

        $response->assertStatus(200)
            ->assertJsonFragment(['body' => 'Hello']);
    }

    public function test_user_can_send_message()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech',
        ]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($jeune)->postJson("/api/v2/messages/{$mentorship->id}", [
            'body' => 'Test message',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['body' => 'Test message']);

        $this->assertDatabaseHas('messages', [
            'mentorship_id' => $mentorship->id,
            'body' => 'Test message',
        ]);
    }

    public function test_user_cannot_send_message_if_not_accepted()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => 'Mentor bio',
            'current_position' => 'Senior Developer',
            'specialization' => 'tech',
        ]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($jeune)->postJson("/api/v2/messages/{$mentorship->id}", [
            'body' => 'Test message',
        ]);

        $response->assertStatus(403);
    }
}
