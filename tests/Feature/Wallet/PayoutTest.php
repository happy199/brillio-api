<?php

namespace Tests\Feature\Wallet;

use App\Jobs\ProcessPayoutJob;
use App\Models\MentorProfile;
use App\Models\PayoutRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\MonerooService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed some essential settings
        SystemSetting::updateOrCreate(['key' => 'credit_price_mentor'], ['value' => 100]);
    }

    public function test_mentor_can_cancel_pending_payout_request()
    {
        $user = User::factory()->mentor()->create(['credits_balance' => 0]);
        $mentorProfile = MentorProfile::factory()->create([
            'user_id' => $user->id,
            'available_balance' => 10000,
        ]);

        // Create a pending payout request for 10,000 FCFA
        $payout = PayoutRequest::create([
            'mentor_profile_id' => $mentorProfile->id,
            'amount' => 10000,
            'fee' => 500,
            'net_amount' => 9500,
            'payment_method' => 'mtn_bj',
            'phone_number' => '12345678',
            'country_code' => 'BJ',
            'dial_code' => '+229',
            'status' => PayoutRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->postJson("/api/mentor/payout/{$payout->id}/cancel");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Demande de retrait annulée avec succès.']);

        $payout->refresh();
        $this->assertEquals(PayoutRequest::STATUS_CANCELLED, $payout->status);

        // FCFA available balance should be refunded: 10,000 + 10,000 = 20,000
        $mentorProfile->refresh();
        $this->assertEquals(20000, $mentorProfile->available_balance);

        // Credits should be refunded: 10,000 FCFA / 100 = 100 credits
        $user->refresh();
        $this->assertEquals(100, $user->credits_balance);

        // Ensure a refund transaction is logged
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'refund',
            'related_id' => $payout->id,
            'related_type' => PayoutRequest::class,
        ]);
    }

    public function test_mentor_cannot_cancel_payout_of_another_mentor()
    {
        $user1 = User::factory()->mentor()->create();
        $mentorProfile1 = MentorProfile::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->mentor()->create();
        $mentorProfile2 = MentorProfile::factory()->create(['user_id' => $user2->id]);

        $payout = PayoutRequest::create([
            'mentor_profile_id' => $mentorProfile2->id,
            'amount' => 10000,
            'fee' => 500,
            'net_amount' => 9500,
            'payment_method' => 'mtn_bj',
            'phone_number' => '12345678',
            'country_code' => 'BJ',
            'dial_code' => '+229',
            'status' => PayoutRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user1)->postJson("/api/mentor/payout/{$payout->id}/cancel");

        $response->assertStatus(403);
        $payout->refresh();
        $this->assertEquals(PayoutRequest::STATUS_PENDING, $payout->status);
    }

    public function test_mentor_cannot_cancel_non_pending_payout_request()
    {
        $user = User::factory()->mentor()->create();
        $mentorProfile = MentorProfile::factory()->create(['user_id' => $user->id]);

        $statuses = [
            PayoutRequest::STATUS_PROCESSING,
            PayoutRequest::STATUS_COMPLETED,
            PayoutRequest::STATUS_FAILED,
        ];

        foreach ($statuses as $status) {
            $payout = PayoutRequest::create([
                'mentor_profile_id' => $mentorProfile->id,
                'amount' => 10000,
                'fee' => 500,
                'net_amount' => 9500,
                'payment_method' => 'mtn_bj',
                'phone_number' => '12345678',
                'country_code' => 'BJ',
                'dial_code' => '+229',
                'status' => $status,
            ]);

            $response = $this->actingAs($user)->postJson("/api/mentor/payout/{$payout->id}/cancel");

            $response->assertStatus(422);
            $payout->refresh();
            $this->assertEquals($status, $payout->status);
        }
    }

    public function test_process_payout_job_aborts_if_payout_is_already_cancelled()
    {
        $user = User::factory()->mentor()->create();
        $mentorProfile = MentorProfile::factory()->create(['user_id' => $user->id]);

        $payout = PayoutRequest::create([
            'mentor_profile_id' => $mentorProfile->id,
            'amount' => 10000,
            'fee' => 500,
            'net_amount' => 9500,
            'payment_method' => 'mtn_bj',
            'phone_number' => '12345678',
            'country_code' => 'BJ',
            'dial_code' => '+229',
            'status' => PayoutRequest::STATUS_CANCELLED,
        ]);

        $mockMoneroo = $this->createMock(MonerooService::class);
        $mockMoneroo->expects($this->never())->method('createPayout');

        $job = new ProcessPayoutJob($payout);
        $job->handle($mockMoneroo);

        $payout->refresh();
        $this->assertEquals(PayoutRequest::STATUS_CANCELLED, $payout->status);
    }
}
