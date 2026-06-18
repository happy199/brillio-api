<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendMissingPhoneReminders;
use App\Mail\Engagement\MissingPhoneReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendMissingPhoneRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_missing_phone_reminders_only_targets_jeunes_without_phone()
    {
        Mail::fake();

        // 1. Jeune with missing phone, should receive email
        $jeuneMissingPhone = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'email' => 'jeune-missing@example.com',
            'last_engagement_email_sent_at' => null,
        ]);

        // 2. Jeune with empty string phone, should receive email
        $jeuneEmptyPhone = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => '',
            'email' => 'jeune-empty@example.com',
            'last_engagement_email_sent_at' => null,
        ]);

        // 3. Jeune with phone, should be ignored
        $jeuneWithPhone = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => '+22501020304',
            'email' => 'jeune-withphone@example.com',
            'last_engagement_email_sent_at' => null,
        ]);

        // 4. Jeune with missing phone but recently messaged (2 days ago), should be ignored
        $jeuneRecentlyMessaged = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'email' => 'jeune-recent@example.com',
            'last_engagement_email_sent_at' => now()->subDays(2),
        ]);

        // 5. Jeune with missing phone but messaged 7 days ago, should receive email
        $jeuneOldMessaged = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'email' => 'jeune-old@example.com',
            'last_engagement_email_sent_at' => now()->subDays(7),
        ]);

        // 6. Mentor with missing phone, should be ignored
        $mentorMissingPhone = User::factory()->create([
            'user_type' => User::TYPE_MENTOR,
            'phone' => null,
            'email' => 'mentor-missing@example.com',
            'last_engagement_email_sent_at' => null,
        ]);

        // 7. Blocked Jeune, should be ignored
        $jeuneBlocked = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'email' => 'jeune-blocked@example.com',
            'is_blocked' => true,
        ]);

        // 8. Archived Jeune, should be ignored
        $jeuneArchived = User::factory()->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'email' => 'jeune-archived@example.com',
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        // Run the job
        (new SendMissingPhoneReminders)->handle(app(\App\Services\EmailDeliveryService::class));

        // Assert emails queued for appropriate users
        Mail::assertQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneMissingPhone) {
            return $mail->hasTo($jeuneMissingPhone->email);
        });

        Mail::assertQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneEmptyPhone) {
            return $mail->hasTo($jeuneEmptyPhone->email);
        });

        Mail::assertQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneOldMessaged) {
            return $mail->hasTo($jeuneOldMessaged->email);
        });

        // Assert emails NOT queued for ignored users
        Mail::assertNotQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneWithPhone) {
            return $mail->hasTo($jeuneWithPhone->email);
        });

        Mail::assertNotQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneRecentlyMessaged) {
            return $mail->hasTo($jeuneRecentlyMessaged->email);
        });

        Mail::assertNotQueued(MissingPhoneReminder::class, function ($mail) use ($mentorMissingPhone) {
            return $mail->hasTo($mentorMissingPhone->email);
        });

        Mail::assertNotQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneBlocked) {
            return $mail->hasTo($jeuneBlocked->email);
        });

        Mail::assertNotQueued(MissingPhoneReminder::class, function ($mail) use ($jeuneArchived) {
            return $mail->hasTo($jeuneArchived->email);
        });

        // Assert timestamps updated
        $jeuneMissingPhone->refresh();
        $this->assertNotNull($jeuneMissingPhone->last_engagement_email_sent_at);
        $this->assertTrue(now()->diffInSeconds($jeuneMissingPhone->last_engagement_email_sent_at) < 5);

        $jeuneEmptyPhone->refresh();
        $this->assertNotNull($jeuneEmptyPhone->last_engagement_email_sent_at);

        $jeuneOldMessaged->refresh();
        $this->assertTrue($jeuneOldMessaged->last_engagement_email_sent_at->gt(now()->subMinutes(1)));
    }

    public function test_send_missing_phone_reminders_is_throttled_to_500()
    {
        Mail::fake();

        // Create 550 young users with missing phone
        User::factory()->count(550)->create([
            'user_type' => User::TYPE_JEUNE,
            'phone' => null,
            'last_engagement_email_sent_at' => null,
        ]);

        // Run the job
        (new SendMissingPhoneReminders)->handle(app(\App\Services\EmailDeliveryService::class));

        // Verify exactly 500 emails were queued
        Mail::assertQueued(MissingPhoneReminder::class, 500);

        // Verify exactly 500 users have their last_engagement_email_sent_at updated
        $updatedCount = User::whereNotNull('last_engagement_email_sent_at')->count();
        $this->assertEquals(500, $updatedCount);
    }
}
