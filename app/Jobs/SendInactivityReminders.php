<?php

namespace App\Jobs;

use App\Mail\Engagement\ReengagementMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInactivityReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // On récupère les jeunes inactifs depuis au moins 7 jours
        // Et qui n'ont pas reçu de mail d'engagement depuis au moins 7 jours
        $users = User::where('user_type', User::TYPE_JEUNE)
            ->where('archived_at', null)
            ->where('is_blocked', false)
            ->where('last_login_at', '<=', now()->subWeek())
            ->where(function ($query) {
                $query->where('last_engagement_email_sent_at', '<=', now()->subWeek())
                    ->orWhereNull('last_engagement_email_sent_at');
            })
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new ReengagementMail($user));
            
            // Mise à jour de la date d'envoi
            $user->update([
                'last_engagement_email_sent_at' => now(),
            ]);
        }
    }
}
