<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Rules\ValidEmailDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidEmailDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_disposable_email_domain()
    {
        $rule = new ValidEmailDomain;

        $failed = false;
        $rule->validate('email', 'testuser@yopmail.com', function ($message) use (&$failed) {
            $failed = true;
            $this->assertStringContainsString('jetables', $message);
        });

        $this->assertTrue($failed);
    }

    public function test_rejects_typo_email_domain()
    {
        $rule = new ValidEmailDomain;

        $failed = false;
        $rule->validate('email', 'testuser@gmaill.com', function ($message) use (&$failed) {
            $failed = true;
            $this->assertStringContainsString('faute de frappe', $message);
        });

        $this->assertTrue($failed);
    }

    public function test_archive_unverified_users_command()
    {
        // Unverified user created 8 days ago -> should be archived
        $unverifiedOld = User::factory()->create([
            'email_verified_at' => null,
            'is_archived' => false,
            'created_at' => now()->subDays(8),
        ]);

        // Unverified user created 2 days ago -> should NOT be archived
        $unverifiedNew = User::factory()->create([
            'email_verified_at' => null,
            'is_archived' => false,
            'created_at' => now()->subDays(2),
        ]);

        // Verified user created 10 days ago -> should NOT be archived
        $verifiedOld = User::factory()->create([
            'email_verified_at' => now()->subDays(9),
            'is_archived' => false,
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan('app:archive-unverified')
            ->assertExitCode(0);

        $this->assertTrue((bool) $unverifiedOld->fresh()->is_archived);
        $this->assertFalse((bool) $unverifiedNew->fresh()->is_archived);
        $this->assertFalse((bool) $verifiedOld->fresh()->is_archived);
    }
}
