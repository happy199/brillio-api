<?php

namespace App\Jobs;

use App\Mail\Engagement\MissingPhoneReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;

class SendMissingPhoneReminders extends EngagementReminderJob
{
    /**
     * Select jeunes who have no phone number
     * and have not received an engagement email in the last 6 days.
     */
    protected function eligibleUsers(): Builder
    {
        return User::where('user_type', User::TYPE_JEUNE)
            ->where('is_archived', false)
            ->where('is_blocked', false)
            ->where(function ($query) {
                $query->whereNull('phone')
                    ->orWhere('phone', '');
            })
            ->where(function ($query) {
                $query->where('last_engagement_email_sent_at', '<=', now()->subDays(6))
                    ->orWhereNull('last_engagement_email_sent_at');
            });
    }

    protected function buildMailable(User $user): Mailable
    {
        return new MissingPhoneReminder($user);
    }
}
