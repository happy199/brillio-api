<?php

namespace App\Jobs;

use App\Mail\Engagement\ProfileCompletionReminder;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendProfileCompletionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // On récupère les utilisateurs avec onboarding non complété
        // Ou des critères spécifiques de profil vide
        $users = User::where('onboarding_completed', false)
            ->where('archived_at', null)
            ->with(['jeuneProfile', 'mentorProfile', 'personalityTest'])
            ->get();

        foreach ($users as $user) {
            $missingSections = $this->getMissingSections($user);

            if (!empty($missingSections)) {
                Mail::to($user->email)->queue(new ProfileCompletionReminder($user, $missingSections));
            }
        }
    }

    /**
     * Détermine les sections manquantes du profil
     */
    private function getMissingSections(User $user): array
    {
        $missing = [];

        if (!$user->profile_photo_path) {
            $missing[] = "Ajouter une photo de profil";
        }

        if ($user->isJeune()) {
            if (!$user->personalityTest) {
                $missing[] = "Passer le test de personnalité (MBTI)";
            }
            if ($user->jeuneProfile && !$user->jeuneProfile->bio) {
                $missing[] = "Rédiger votre biographie";
            }
        }

        if ($user->isMentor()) {
            if ($user->mentorProfile) {
                if (!$user->mentorProfile->bio) {
                    $missing[] = "Rédiger votre présentation bio";
                }
                if (!$user->mentorProfile->specialization) {
                    $missing[] = "Définir vos domaines d'expertise";
                }
            }
            else {
                $missing[] = "Configurer votre profil mentor";
            }
        }

        return $missing;
    }
}