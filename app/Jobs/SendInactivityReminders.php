<?php

namespace App\Jobs;

use App\Mail\Engagement\ReengagementMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInactivityReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // On limite à 500 emails par exécution pour respecter les quotas SMTP
        // et étaler la charge sur la journée (via un cron horaire)
        $limit = 500;
        $processedCount = 0;

        // On récupère les jeunes inactifs depuis au moins 7 jours
        // Et qui n'ont pas reçu de mail d'engagement depuis au moins 7 jours
        User::where('user_type', User::TYPE_JEUNE)
            ->whereNull('archived_at')
            ->where('is_blocked', false)
            ->where('last_login_at', '<=', now()->subWeek())
            ->where(function ($query) {
                $query->where('last_engagement_email_sent_at', '<=', now()->subWeek())
                    ->orWhereNull('last_engagement_email_sent_at');
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$processedCount, $limit) {
                foreach ($users as $user) {
                    if ($processedCount >= $limit) {
                        return false; // Stop chunking
                    }

                    // Validation basique de l'email pour éviter les erreurs SMTP (ex: mailcom sans point)
                    if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        Log::warning("Email d'engagement sauté : format invalide pour l'utilisateur #{$user->id}", [
                            'email' => $user->email,
                        ]);

                        // On marque quand même comme "envoyé" pour ne pas reboucler dessus indéfiniment
                        // ou on pourrait le marquer différemment, mais ici on veut surtout passer au suivant
                        $user->update(['last_engagement_email_sent_at' => now()]);

                        continue;
                    }

                    Mail::to($user->email)->queue(new ReengagementMail($user));

                    // Mise à jour de la date d'envoi
                    $user->update([
                        'last_engagement_email_sent_at' => now(),
                    ]);

                    $processedCount++;
                }
            });

        Log::info("Fin du job SendInactivityReminders : {$processedCount} emails mis en file d'attente.");
    }
}
