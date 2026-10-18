<?php

namespace Tests\Feature\Admin;

use App\Models\CronAutomation;
use App\Models\ScheduledTaskLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCronAutomationsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);

        session(['admin_2fa_passed' => true]);

        return $admin;
    }

    public function test_admin_can_view_automations_list(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.audits.crons'));

        $response->assertStatus(200);
        $response->assertSee('Automatisations');
        $response->assertSee('Relance des jeunes inactifs');
        $response->assertSee('Rappels des séances de mentorat');
        $response->assertSee('Public cible :');
        $response->assertSee('Toutes les heures');
    }

    public function test_admin_can_filter_automations_by_category(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.audits.crons', ['category' => 'mentorship']));

        $response->assertStatus(200);
        $response->assertSee('Rappels des séances de mentorat');
    }

    public function test_admin_can_toggle_automation_status(): void
    {
        $admin = $this->createAdmin();
        CronAutomation::syncAutomations();

        $this->assertTrue(CronAutomation::isEnabled('inactivity_reminders'));

        // Pause
        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.audits.crons.toggle', 'inactivity_reminders'));

        $response->assertRedirect();
        $this->assertFalse(CronAutomation::isEnabled('inactivity_reminders'));

        // Resume
        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.audits.crons.toggle', 'inactivity_reminders'));

        $response->assertRedirect();
        $this->assertTrue(CronAutomation::isEnabled('inactivity_reminders'));
    }

    public function test_admin_can_view_technical_logs_tab(): void
    {
        $admin = $this->createAdmin();

        ScheduledTaskLog::create([
            'command' => 'emails:process-bounces',
            'status' => 'success',
            'duration' => 0.42,
            'output' => 'Zero bounces found',
            'run_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.audits.crons', ['tab' => 'logs']));

        $response->assertStatus(200);
        $response->assertSee('emails:process-bounces');
        $response->assertSee('0.42 s');
    }
}
