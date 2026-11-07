<?php

namespace App\Jobs;

use App\Mail\Engagement\MentorEngagementMail;
use App\Mail\Engagement\ProfileCompletionReminder;
use App\Mail\MentorProfilePublished;
use App\Models\User;
use App\Services\EmailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendProfileCompletionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(EmailDeliveryService $deliveryService): void
    {
        // On récupère uniquement les mentors :
        // 1. Les mentors avec onboarding non complété
        // 2. Les mentors avec profil publié (pour confirmation unique de publication ou réengagement mensuel)
        $users = User::where('user_type', User::TYPE_MENTOR)
            ->where(function ($query) {
                $query->where('onboarding_completed', false)
                    ->orWhere(function ($q) {
                        $q->whereHas('mentorProfile', fn ($p) => $p->where('is_published', true));
                    });
            })
            ->where('is_archived', false)
            ->where('is_blocked', false)
            ->whereNotNull('email_verified_at')
            ->with(['mentorProfile'])
            ->get();

        foreach ($users as $user) {
            if ($deliveryService->isExcludedEmail($user->email)) {
                continue;
            }

            // Logique spécifique pour les mentors
            if ($user->isMentor() && $user->mentorProfile && $user->mentorProfile->is_published) {
                // 1. Si le mail de félicitations pour profil publié n'a JAMAIS été envoyé, on l'envoie une seule fois
                if (is_null($user->mentorProfile->published_email_sent_at)) {
                    try {
                        Mail::to($user->email)->send(new MentorProfilePublished($user));
                        $user->mentorProfile->update([
                            'published_email_sent_at' => now(),
                        ]);
                        $user->update([
                            'onboarding_completed' => true,
                            'last_engagement_email_sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Email publication profil mentor échoué pour #{$user->id}: ".$e->getMessage());
                        $deliveryService->handleDeliveryFailure($user->email, $e);
                    }

                    continue;
                }

                // 2. Pour les mentors dont le profil est déjà publié depuis, on envoie le mail de réengagement
                // (monétisation, séances, documents, communauté, impact) max une fois tous les 30 jours
                $canSendMonthlyEngagement = is_null($user->last_engagement_email_sent_at)
                    || $user->last_engagement_email_sent_at <= now()->subDays(30);

                if ($canSendMonthlyEngagement) {
                    try {
                        Mail::to($user->email)->send(new MentorEngagementMail($user));
                        $user->update([
                            'onboarding_completed' => true,
                            'last_engagement_email_sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Email d'engagement mentor échoué pour #{$user->id}: ".$e->getMessage());
                        $deliveryService->handleDeliveryFailure($user->email, $e);
                    }
                }

                continue;
            }

            $missingSections = $this->getMissingSections($user);

            if (! empty($missingSections)) {
                $canSendProfileCompletion = is_null($user->last_engagement_email_sent_at)
                    || $user->last_engagement_email_sent_at <= now()->subDays(7);

                if ($canSendProfileCompletion) {
                    try {
                        Mail::to($user->email)->send(new ProfileCompletionReminder($user, $missingSections));
                        $user->update(['last_engagement_email_sent_at' => now()]);
                    } catch (\Exception $e) {
                        Log::error("Email complétion profil échoué pour #{$user->id}: ".$e->getMessage());
                        $deliveryService->handleDeliveryFailure($user->email, $e);
                    }
                }
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

        if (empty($user->phone)) {
            $missing[] = 'Renseigner votre numéro de téléphone (Appel / WhatsApp)';
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
