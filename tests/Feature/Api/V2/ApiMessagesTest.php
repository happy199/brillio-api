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

    private const POSITION_SENIOR_DEV = 'Senior Developer';

    private const TEST_MESSAGE_BODY = 'Test message';

    private const MENTOR_BIO = 'Mentor bio';

    private function setupMentorship(string $status = 'accepted'): array
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $mentor = User::factory()->mentor()->create();
        $mentor->mentorProfile()->create([
            'is_published' => true,
            'is_validated' => true,
            'bio' => self::MENTOR_BIO,
            'current_position' => self::POSITION_SENIOR_DEV,
            'specialization' => 'tech',
        ]);

        $mentorship = Mentorship::factory()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $jeune->id,
            'status' => $status,
        ]);

        return [$jeune, $mentor, $mentorship];
    }

    public function test_user_can_list_messages()
    {
        [$jeune, $mentor, $mentorship] = $this->setupMentorship('accepted');

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
        [$jeune, $mentor, $mentorship] = $this->setupMentorship('accepted');

        $response = $this->actingAs($jeune)->postJson("/api/v2/messages/{$mentorship->id}", [
            'body' => self::TEST_MESSAGE_BODY,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['body' => self::TEST_MESSAGE_BODY]);

        $this->assertDatabaseHas('messages', [
            'mentorship_id' => $mentorship->id,
            'body' => self::TEST_MESSAGE_BODY,
        ]);
    }

    public function test_user_cannot_send_message_if_not_accepted()
    {
        [$jeune, $mentor, $mentorship] = $this->setupMentorship('pending');

        $response = $this->actingAs($jeune)->postJson("/api/v2/messages/{$mentorship->id}", [
            'body' => self::TEST_MESSAGE_BODY,
        ]);

        $response->assertStatus(403);
    }
}
