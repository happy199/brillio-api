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
        (new SendProfileCompletionReminders)->handle();

        // Verify that the incomplete mentor gets a ProfileCompletionReminder
        Mail::assertQueued(ProfileCompletionReminder::class, function ($mail) use ($mentorIncomplete) {
            return $mail->hasTo($mentorIncomplete->email);
        });

        // Verify that the mentor with no resources gets a MentorEngagementMail
        Mail::assertQueued(MentorEngagementMail::class, function ($mail) use ($mentorNoResources) {
            return $mail->hasTo($mentorNoResources->email);
        });

        // Verify that the young user gets NOTHING from this job
        Mail::assertNotQueued(ProfileCompletionReminder::class, function ($mail) use ($jeuneIncomplete) {
            return $mail->hasTo($jeuneIncomplete->email);
        });
        Mail::assertNotQueued(MentorEngagementMail::class, function ($mail) use ($jeuneIncomplete) {
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

    public function test_send_campaign_email_job_sends_email_and_updates_campaign_stats()
    {
        Mail::fake();

        $user = User::factory()->create();

        $campaign = EmailCampaign::create([
            'subject' => 'Test Campaign',
            'body' => 'Hello team!',
            'type' => 'newsletter',
            'recipient_emails' => ['target@example.com'],
            'recipients_count' => 1,
            'status' => 'sending',
            'sent_by' => $user->id,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        // Run the single send job
        (new SendCampaignEmailJob($campaign, 'target@example.com'))->handle();

        // Verify email was sent
        Mail::assertSent(CampaignNewsletterMail::class, function ($mail) {
            return $mail->hasTo('target@example.com');
        });

        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->sent_count);
        $this->assertEquals(0, $campaign->failed_count);
        $this->assertEquals('sent', $campaign->status);
        $this->assertNotNull($campaign->sent_at);
    }
}
