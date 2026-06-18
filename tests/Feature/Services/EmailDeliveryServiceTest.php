<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\EmailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailDeliveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EmailDeliveryService::class);
    }

    public function test_admin_brillio_email_is_excluded_by_default(): void
    {
        $this->assertTrue($this->service->isExcludedEmail('admin@brillio.com'));
        $this->assertFalse($this->service->isExcludedEmail('jeune@example.com'));
    }

    public function test_filter_recipient_list_removes_excluded_emails(): void
    {
        $filtered = $this->service->filterRecipientList([
            'jeune@example.com',
            'admin@brillio.com',
            'invalid-email',
        ]);

        $this->assertSame(['jeune@example.com'], $filtered);
    }

    public function test_mailbox_full_error_archives_jeune_account(): void
    {
        $jeune = User::factory()->create([
            'user_type' => 'jeune',
            'email' => 'djegoulucresse@gmail.com',
            'is_archived' => false,
        ]);

        $exception = new \Exception(
            '452-4.2.2 The recipient\'s inbox is out of storage space. OverQuotaTemp'
        );

        $this->service->handleDeliveryFailure($jeune->email, $exception);

        $jeune->refresh();
        $this->assertTrue($jeune->is_archived);
        $this->assertNotNull($jeune->archived_at);
        $this->assertStringContainsString('boîte mail', $jeune->archived_reason);
    }

    public function test_non_mailbox_error_does_not_archive(): void
    {
        $jeune = User::factory()->create([
            'user_type' => 'jeune',
            'email' => 'test@example.com',
            'is_archived' => false,
        ]);

        $this->service->handleDeliveryFailure($jeune->email, new \Exception('Connection timeout'));

        $jeune->refresh();
        $this->assertFalse($jeune->is_archived);
    }

    public function test_mailbox_error_does_not_archive_mentor(): void
    {
        $mentor = User::factory()->mentor()->create([
            'email' => 'mentor@example.com',
            'is_archived' => false,
        ]);

        $exception = new \Exception('452-4.2.2 mailbox full');

        $this->service->handleDeliveryFailure($mentor->email, $exception);

        $mentor->refresh();
        $this->assertFalse($mentor->is_archived);
    }

    public function test_marketing_eligible_query_excludes_archived_blocked_and_admin_email(): void
    {
        User::factory()->create([
            'email' => 'active@example.com',
            'is_archived' => false,
            'is_blocked' => false,
        ]);
        User::factory()->create([
            'email' => 'archived@example.com',
            'is_archived' => true,
            'archived_at' => now(),
        ]);
        User::factory()->create([
            'email' => 'blocked@example.com',
            'is_blocked' => true,
            'blocked_at' => now(),
        ]);
        User::factory()->create([
            'email' => 'admin@brillio.com',
            'is_archived' => false,
            'is_blocked' => false,
        ]);

        $emails = $this->service->marketingEligibleUsersQuery()->pluck('email')->all();

        $this->assertContains('active@example.com', $emails);
        $this->assertNotContains('archived@example.com', $emails);
        $this->assertNotContains('blocked@example.com', $emails);
        $this->assertNotContains('admin@brillio.com', $emails);
    }
}
