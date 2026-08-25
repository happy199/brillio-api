<?php

namespace Tests\Feature\Admin;

use App\Models\AdvisorVideoCall;
use App\Models\ChatConversation;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisorVideoCallTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $jeune;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);

        $this->jeune = User::factory()->create([
            'user_type' => 'jeune',
            'credits_balance' => 100,
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_video_call_advisor'],
            ['value' => '50', 'type' => 'integer', 'description' => 'Cost for video call with advisor']
        );
    }

    private function createTestConversation(): ChatConversation
    {
        return ChatConversation::create([
            'user_id' => $this->jeune->id,
            'title' => 'Orientation test',
            'needs_human_support' => true,
            'human_support_active' => true,
            'human_support_admin_id' => $this->admin->id,
        ]);
    }

    public function test_jeune_can_propose_and_start_video_call_debiting_credits(): void
    {
        $conversation = $this->createTestConversation();

        $response = $this->actingAs($this->jeune)
            ->post(route('chat.video-call.propose-jeune', $conversation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(50, $this->jeune->fresh()->credits_balance);

        $call = AdvisorVideoCall::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($call);
        $this->assertEquals('accepted', $call->status);
        $this->assertEquals('jeune', $call->initiated_by);
        $this->assertEquals(50, $call->credits_cost);
    }

    public function test_counselor_can_propose_video_call_and_jeune_can_accept(): void
    {
        $conversation = $this->createTestConversation();

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.chat.video-call.propose-counselor', $conversation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $call = AdvisorVideoCall::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($call);
        $this->assertEquals('pending_acceptance', $call->status);
        $this->assertEquals('counselor', $call->initiated_by);

        // Jeune accepte
        $acceptResponse = $this->actingAs($this->jeune)
            ->post(route('chat.video-call.accept', $call));

        $acceptResponse->assertRedirect();
        $acceptResponse->assertSessionHas('success');

        $this->assertEquals(50, $this->jeune->fresh()->credits_balance);
        $this->assertEquals('accepted', $call->fresh()->status);
    }

    public function test_jeune_can_refuse_counselor_video_call_proposal(): void
    {
        $conversation = $this->createTestConversation();

        $call = AdvisorVideoCall::create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->jeune->id,
            'counselor_id' => $this->admin->id,
            'initiated_by' => 'counselor',
            'status' => 'pending_acceptance',
            'credits_cost' => 50,
            'meeting_id' => 'brillio_advisor_test_refuse',
        ]);

        $response = $this->actingAs($this->jeune)
            ->post(route('chat.video-call.refuse', $call));

        $response->assertRedirect();
        $response->assertSessionHas('info');

        $this->assertEquals(100, $this->jeune->fresh()->credits_balance);
        $this->assertEquals('refused', $call->fresh()->status);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'content' => "Le jeune a refusé l'appel vidéo.",
            'is_system_message' => true,
        ]);
    }

    public function test_finish_meeting_generates_ai_summary_in_chat(): void
    {
        $conversation = $this->createTestConversation();

        $call = AdvisorVideoCall::create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->jeune->id,
            'counselor_id' => $this->admin->id,
            'initiated_by' => 'jeune',
            'status' => 'accepted',
            'credits_cost' => 50,
            'meeting_id' => 'brillio_advisor_test_finish',
            'transcription_raw' => [
                ['speaker' => 'Jeune', 'text' => 'Je veux devenir ingénieur logiciel.'],
                ['speaker' => 'Conseiller', 'text' => 'Excellente idée, voici le parcours recommandé.'],
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('advisor-meeting.finish', $call));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertEquals('completed', $call->fresh()->status);
        $this->assertNotNull($call->fresh()->ai_summary);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'is_from_human' => true,
        ]);
    }
}
