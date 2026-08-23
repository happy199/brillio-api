<?php

namespace Tests\Feature\Admin;

use App\Models\EmailSuppression;
use App\Models\User;
use App\Services\EmailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSuppressionTest extends TestCase
{
    use RefreshDatabase;

    private const ACTIVE_USER_EMAIL = 'activeuser@example.com';

    private function createAdmin(): User
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);

        session(['admin_2fa_passed' => true]);

        return $admin;
    }

    public function test_admin_can_view_suppression_list(): void
    {
        $admin = $this->createAdmin();

        EmailSuppression::create([
            'email' => 'bouncing@example.com',
            'reason' => 'Mailbox full 452 4.2.2',
            'source' => 'system_auto',
        ]);

        $response = $this->actingAs($admin)->withSession(['admin_2fa_passed' => true])->get(route('admin.audits.suppressions'));

        $response->assertStatus(200);
        $response->assertSee('bouncing@example.com');
        $response->assertSee('Mailbox full 452 4.2.2');
    }

    public function test_admin_can_add_suppression_manually(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->withSession(['admin_2fa_passed' => true])->post(route('admin.audits.suppressions.store'), [
            'email' => 'spam@example.com',
            'reason' => 'Demande de désabonnement manuel',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_suppressions', [
            'email' => 'spam@example.com',
            'source' => 'admin_manual',
        ]);
    }

    public function test_admin_can_delete_suppression(): void
    {
        $admin = $this->createAdmin();

        $suppression = EmailSuppression::create([
            'email' => 'to_restore@example.com',
            'reason' => 'Erreur temporaire',
            'source' => 'system_auto',
        ]);

        $response = $this->actingAs($admin)->withSession(['admin_2fa_passed' => true])->delete(route('admin.audits.suppressions.destroy', $suppression));

        $response->assertRedirect();
        $this->assertDatabaseMissing('email_suppressions', [
            'id' => $suppression->id,
        ]);
    }

    public function test_email_delivery_service_checks_suppression_table(): void
    {
        EmailSuppression::create([
            'email' => 'blocked@example.com',
            'reason' => 'Boîte mail inaccessible',
            'source' => 'system_auto',
        ]);

        $service = app(EmailDeliveryService::class);

        $this->assertTrue($service->isExcludedEmail('blocked@example.com'));
        $this->assertFalse($service->isExcludedEmail('clean@example.com'));
    }

    public function test_delivery_failure_adds_to_suppression_without_archiving_user(): void
    {
        $user = User::factory()->create([
            'email' => self::ACTIVE_USER_EMAIL,
            'user_type' => 'jeune',
            'is_archived' => false,
        ]);

        $service = app(EmailDeliveryService::class);
        $exception = new \Exception('452 4.2.2 The recipient inbox is out of storage space');

        $service->handleDeliveryFailure(self::ACTIVE_USER_EMAIL, $exception);

        // Account remains ACTIVE (is_archived = false)
        $user->refresh();
        $this->assertFalse($user->is_archived);

        // Email added to suppression list
        $this->assertDatabaseHas('email_suppressions', [
            'email' => self::ACTIVE_USER_EMAIL,
            'source' => 'system_auto',
        ]);
    }

    public function test_global_mail_sending_listener_cancels_sending_for_suppressed_email(): void
    {
        EmailSuppression::create([
            'email' => 'armanceatchou1@gmail.com',
            'reason' => 'Boîte mail pleine',
            'source' => 'system_auto',
        ]);

        $sent = false;

        Event::listen(MessageSent::class, function () use (&$sent) {
            $sent = true;
        });

        // Tente un envoi direct via Mail::to()
        try {
            Mail::html('Test message', function ($message) {
                $message->to('armanceatchou1@gmail.com')->subject('Test Subject');
            });
        } catch (\Throwable $e) {
            // Ignoré
        }

        // L'e-mail ne doit PAS être envoyé (MessageSent ne doit pas se déclencher)
        $this->assertFalse($sent);
    }
}
