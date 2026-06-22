<?php

namespace App\Jobs;

use App\Mail\Engagement\ReengagementMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;

class SendInactivityReminders extends EngagementReminderJob
{
    /**
     * Select jeunes who have been inactive for at least a week
     * and have not received an engagement email in the last 7 days.
     */
    protected function eligibleUsers(): Builder
    {
        return User::where('user_type', User::TYPE_JEUNE)
            ->where('is_archived', false)
            ->where('is_blocked', false)
            ->where('last_login_at', '<=', now()->subWeek())
            ->where(function ($query) {
                $query->where('last_engagement_email_sent_at', '<=', now()->subWeek())
                    ->orWhereNull('last_engagement_email_sent_at');
            });
    }

    protected function buildMailable(User $user): Mailable
    {
        return new ReengagementMail($user);
    }
}
