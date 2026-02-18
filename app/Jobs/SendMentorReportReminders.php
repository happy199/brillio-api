<?php

namespace App\Jobs;

use App\Models\MentoringSession;
use App\Services\MentorshipNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMentorReportReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    //
    }

    /**
     * Execute the job.
     */
    public function handle(MentorshipNotificationService $notificationService): void
    {
        Log::info('Démarrage du job SendMentorReportReminders...');

        // Trouver les sessions passées (plus de 24h) qui sont payées mais sans compte rendu
        // On cible les sessions "scheduled" ou "confirmed" dont la date est passée
        $sessions = MentoringSession::where('is_paid', true)
            ->whereNull('report_content')
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('scheduled_at', '<', now()->subHours(24))
            ->where('scheduled_at', '>', now()->subDays(7)) // Ne pas relancer les sessions trop vieilles
            ->get();

        Log::info("{$sessions->count()} sessions trouvées nécessitant un rappel.");

        foreach ($sessions as $session) {
            try {
                $notificationService->sendReportReminder($session);
                Log::info("Rappel envoyé pour la session ID: {$session->id}");
            }
            catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi du rappel pour la session {$session->id}: " . $e->getMessage());
            }
        }

        Log::info('Job SendMentorReportReminders terminé.');
    }
}