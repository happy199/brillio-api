<?php

namespace Tests\Feature\Wallet;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_jeune_can_redeem_coupon()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE, 'credits_balance' => 0]);
        $coupon = Coupon::factory()->create(['credits_amount' => 500]);

        $response = $this->actingAs($jeune)->post(route('jeune.wallet.redeem'), [
            'code' => $coupon->code,
        ]);

        $response->assertSessionHas('success');
        $jeune->refresh();
        $this->assertEquals(500, $jeune->credits_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $jeune->id,
            'amount' => 500,
            'type' => 'coupon',
        ]);
        $this->assertDatabaseHas('coupon_user', [
            'user_id' => $jeune->id,
            'coupon_id' => $coupon->id,
        ]);
    }

    public function test_mentor_can_redeem_coupon()
    {
        $mentor = User::factory()->mentor()->create(['credits_balance' => 0]);
        $coupon = Coupon::factory()->create(['credits_amount' => 1000]);

        $response = $this->actingAs($mentor)->post(route('mentor.wallet.redeem'), [
            'code' => $coupon->code,
        ]);

        $response->assertSessionHas('success');
        $mentor->refresh();
        $this->assertEquals(1000, $mentor->credits_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $mentor->id,
            'amount' => 1000,
            'type' => 'coupon',
        ]);
    }

    public function test_cannot_redeem_invalid_coupon()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);

        $response = $this->actingAs($jeune)->post(route('jeune.wallet.redeem'), [
            'code' => 'INVALID',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_cannot_redeem_expired_coupon()
    {
        $jeune = User::factory()->create(['user_type' => User::TYPE_JEUNE]);
        $coupon = Coupon::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($jeune)->post(route('jeune.wallet.redeem'), [
            'code' => $coupon->code,
        ]);

        $response->assertSessionHasErrors('code');
    }
}
