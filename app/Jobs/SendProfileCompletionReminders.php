<?php

namespace App\Jobs;

use App\Mail\Engagement\ProfileCompletionReminder;
use App\Models\User;
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
        // On récupère uniquement les mentors :
        // 1. Les mentors avec onboarding non complété
        // 2. Les mentors avec profil publié mais SANS ressources publiées (pour réengagement mensuel)
        $users = User::where('user_type', User::TYPE_MENTOR)
            ->where(function ($query) {
                $query->where('onboarding_completed', false)
                    ->orWhere(function ($q) {
                        $q->where('onboarding_completed', true)
                            ->whereHas('mentorProfile', fn ($p) => $p->where('is_published', true))
                            ->whereDoesntHave('resources', fn ($r) => $r->where('is_published', true))
                            // On limite à un envoi par mois pour le réengagement
                            ->where(function ($dateQuery) {
                                $dateQuery->where('last_engagement_email_sent_at', '<=', now()->subDays(30))
                                    ->orWhereNull('last_engagement_email_sent_at');
                            });
                    });
            })
            ->whereNull('archived_at')
            ->where('is_blocked', false)
            ->with(['mentorProfile'])
            ->get();

        foreach ($users as $user) {
            // Logique spécifique pour les mentors
            if ($user->isMentor()) {
                if ($user->mentorProfile && $user->mentorProfile->is_published) {
                    // Si le profil est déjà publié, on envoie le mail d'engagement
                    Mail::to($user->email)->queue(new \App\Mail\Engagement\MentorEngagementMail($user));

                    // On marque comme complété et on met à jour la date d'envoi
                    $user->update([
                        'onboarding_completed' => true,
                        'last_engagement_email_sent_at' => now(),
                    ]);

                    continue;
                }
            }

            $missingSections = $this->getMissingSections($user);

            if (! empty($missingSections)) {
                Mail::to($user->email)->queue(new ProfileCompletionReminder($user, $missingSections));

                // On met à jour la date pour ne pas le solliciter trop souvent si besoin
                $user->update(['last_engagement_email_sent_at' => now()]);
            }
        }
    }

    /**
     * Détermine les sections manquantes du profil
     */
    private function getMissingSections(User $user): array
    {
        $missing = [];

        if (! $user->profile_photo_path) {
            $missing[] = 'Ajouter une photo de profil';
        }

        if ($user->isJeune()) {
            if (! $user->personalityTest) {
                $missing[] = 'Passer le test de personnalité (MBTI)';
            }
            if ($user->jeuneProfile && ! $user->jeuneProfile->bio) {
                $missing[] = 'Rédiger votre biographie';
            }
        }

        if ($user->isMentor()) {
            if ($user->mentorProfile) {
                if (! $user->mentorProfile->bio) {
                    $missing[] = 'Rédiger votre présentation bio';
                }
                if (! $user->mentorProfile->specialization) {
                    $missing[] = "Définir vos domaines d'expertise";
                }
            } else {
                $missing[] = 'Configurer votre profil mentor';
            }
        }

        return $missing;
    }
}
