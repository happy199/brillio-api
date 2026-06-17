<?php

namespace App\Jobs;

use App\Mail\Engagement\MissingPhoneReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMissingPhoneReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // On limite à 500 emails par exécution pour respecter les quotas SMTP
        $limit = 500;
        $processedCount = 0;

        // On récupère les jeunes qui n'ont pas de numéro de téléphone
        // Et qui n'ont pas reçu de mail d'engagement depuis au moins 6 jours
        User::where('user_type', User::TYPE_JEUNE)
            ->whereNull('archived_at')
            ->where('is_blocked', false)
            ->where(function ($query) {
                $query->whereNull('phone')
                    ->orWhere('phone', '');
            })
            ->where(function ($query) {
                $query->where('last_engagement_email_sent_at', '<=', now()->subDays(6))
                    ->orWhereNull('last_engagement_email_sent_at');
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$processedCount, $limit) {
                foreach ($users as $user) {
                    if ($processedCount >= $limit) {
                        return false; // Stop chunking
                    }

                    // Validation basique de l'email pour éviter les erreurs SMTP
                    if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        Log::warning("Email d'engagement sauté : format invalide pour l'utilisateur #{$user->id}", [
                            'email' => $user->email,
                        ]);

                        // On marque quand même comme "envoyé" pour ne pas reboucler dessus indéfiniment
                        $user->update(['last_engagement_email_sent_at' => now()]);

                        continue;
                    }

                    Mail::to($user->email)->queue(new MissingPhoneReminder($user));

                    // Mise à jour de la date d'envoi
                    $user->update([
                        'last_engagement_email_sent_at' => now(),
                    ]);

                    $processedCount++;
                }
            });

        Log::info("Fin du job SendMissingPhoneReminders : {$processedCount} emails mis en file d'attente.");
    }
}
