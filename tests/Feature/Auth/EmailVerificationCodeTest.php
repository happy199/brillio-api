<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_notification_with_verification_code_on_send()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->sendEmailVerificationNotification();

        $user->refresh();

        $this->assertNotNull($user->verification_code);
        $this->assertEquals(6, strlen($user->verification_code));
        $this->assertNotNull($user->verification_code_expires_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_api_user_can_verify_email_with_valid_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $code = $user->generateVerificationCode();

        $response = $this->postJson('/api/v2/auth/verify-email-code', [
            'email' => $user->email,
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->verification_code);
    }

    public function test_api_verification_fails_with_invalid_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->generateVerificationCode();

        $response = $this->postJson('/api/v2/auth/verify-email-code', [
            'email' => $user->email,
            'code' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_web_user_can_verify_email_with_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'user_type' => 'jeune',
        ]);

        $code = $user->generateVerificationCode();

        $response = $this->actingAs($user)->post(route('verification.verify-code'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('jeune.dashboard'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
