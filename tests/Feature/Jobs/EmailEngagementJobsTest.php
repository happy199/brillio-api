<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendCampaignEmailJob;
use App\Jobs\SendNewsletterJob;
use App\Jobs\SendProfileCompletionReminders;
use App\Mail\CampaignNewsletterMail;
use App\Mail\Engagement\MentorEngagementMail;
use App\Mail\Engagement\ProfileCompletionReminder;
use App\Models\EmailCampaign;
use App\Models\MentorProfile;
use App\Models\User;
use App\Services\EmailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailEngagementJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_profile_completion_reminders_only_targets_mentors()
    {
        Mail::fake();

        // 1. A mentor with incomplete onboarding
        $mentorIncomplete = User::factory()->create([
            'user_type' => 'mentor',
            'onboarding_completed' => false,
            'email' => 'mentor-incomplete@example.com',
        ]);
        MentorProfile::create([
            'user_id' => $mentorIncomplete->id,
            'is_published' => false,
        ]);

        // 2. A mentor with published profile but no resources
        $mentorNoResources = User::factory()->create([
            'user_type' => 'mentor',
            'onboarding_completed' => true,
            'email' => 'mentor-noresources@example.com',
            'last_engagement_email_sent_at' => now()->subDays(31),
        ]);
        MentorProfile::create([
            'user_id' => $mentorNoResources->id,
            'is_published' => true,
        ]);

        // 3. A young user with incomplete onboarding (should be ignored)
        $jeuneIncomplete = User::factory()->create([
            'user_type' => 'jeune',
            'onboarding_completed' => false,
            'email' => 'jeune-incomplete@example.com',
        ]);

        // Run the job
        (new SendProfileCompletionReminders)->handle(app(EmailDeliveryService::class));

        // Verify that the incomplete mentor gets a ProfileCompletionReminder
        Mail::assertSent(ProfileCompletionReminder::class, function ($mail) use ($mentorIncomplete) {
            return $mail->hasTo($mentorIncomplete->email);
        });

        // Verify that the mentor with no resources gets a MentorEngagementMail
        Mail::assertSent(MentorEngagementMail::class, function ($mail) use ($mentorNoResources) {
            return $mail->hasTo($mentorNoResources->email);
        });

        // Verify that the young user gets NOTHING from this job
        Mail::assertNotSent(ProfileCompletionReminder::class, function ($mail) use ($jeuneIncomplete) {
            return $mail->hasTo($jeuneIncomplete->email);
        });
        Mail::assertNotSent(MentorEngagementMail::class, function ($mail) use ($jeuneIncomplete) {
            return $mail->hasTo($jeuneIncomplete->email);
        });
    }

    public function test_send_newsletter_job_dispatches_staggered_campaign_emails()
    {
        Queue::fake();

        $user = User::factory()->create();

        $campaign = EmailCampaign::create([
            'subject' => 'Test Campaign',
            'body' => 'Hello team!',
            'type' => 'newsletter',
            'recipient_emails' => ['one@example.com', 'two@example.com', 'three@example.com'],
            'recipients_count' => 3,
            'status' => 'queued',
            'sent_by' => $user->id,
        ]);

        // Run the master job
        (new SendNewsletterJob($campaign))->handle();

        // Verify campaign status is set to sending
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        $this->assertEquals(3, $campaign->recipients_count);
        $this->assertEquals(0, $campaign->sent_count);
        $this->assertEquals(0, $campaign->failed_count);

        // Verify individual email jobs are dispatched with delays
        Queue::assertPushed(SendCampaignEmailJob::class, 3);
    }

    private const TARGET_EMAIL = 'target@example.com';

    public function test_send_campaign_email_job_sends_email_and_updates_campaign_stats()
    {
        Mail::fake();

        $user = User::factory()->create();

        $campaign = EmailCampaign::create([
            'subject' => 'Test Campaign',
            'body' => 'Hello team!',
            'type' => 'newsletter',
            'recipient_emails' => [self::TARGET_EMAIL],
            'recipients_count' => 1,
            'status' => 'sending',
            'sent_by' => $user->id,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        // Run the single send job
        (new SendCampaignEmailJob($campaign, self::TARGET_EMAIL))->handle(app(EmailDeliveryService::class));

        // Verify email was sent
        Mail::assertSent(CampaignNewsletterMail::class, function ($mail) {
            return $mail->hasTo(self::TARGET_EMAIL);
        });

        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->sent_count);
        $this->assertEquals(0, $campaign->failed_count);
        $this->assertEquals('sent', $campaign->status);
        $this->assertNotNull($campaign->sent_at);
    }

    public function test_send_campaign_email_job_archives_jeune_on_mailbox_error(): void
    {
        $admin = User::factory()->create();
        $jeune = User::factory()->create([
            'user_type' => 'jeune',
            'email' => 'mailbox-full@example.com',
            'is_archived' => false,
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with('mailbox-full@example.com')
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('452-4.2.2 The recipient\'s inbox is out of storage space'));

        $campaign = EmailCampaign::create([
            'subject' => 'Test Campaign',
            'body' => 'Hello!',
            'type' => 'newsletter',
            'recipient_emails' => ['mailbox-full@example.com'],
            'recipients_count' => 1,
            'status' => 'sending',
            'sent_by' => $admin->id,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        (new SendCampaignEmailJob($campaign, 'mailbox-full@example.com'))->handle(app(EmailDeliveryService::class));

        $jeune->refresh();
        $this->assertTrue($jeune->is_archived);
        $campaign->refresh();
        $this->assertEquals(1, $campaign->failed_count);
    }
}
