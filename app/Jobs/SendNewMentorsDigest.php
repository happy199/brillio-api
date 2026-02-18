<?php

namespace App\Jobs;

use App\Mail\Engagement\NewMentorsWeekly;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewMentorsDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Récupérer les nouveaux mentors de la semaine (validés et publiés)
        $newMentors = User::where('user_type', 'mentor')
            ->whereHas('mentorProfile', function ($query) {
            $query->where('is_published', true)
                ->where('created_at', '>=', now()->subWeek());
        })
            ->with(['mentorProfile.specializationModel'])
            ->get();

        // Check for at least 3 mentors
        if ($newMentors->count() < 3) {
            return;
        }

        // Limit to maximum 10 mentors
        if ($newMentors->count() > 10) {
            $newMentors = $newMentors->take(10);
        }

        // 2. Récupérer tous les jeunes actifs
        $jeunes = User::where('user_type', 'jeune')
            ->where('archived_at', null)
            ->get();

        // 3. Envoyer le digest à chaque jeune
        foreach ($jeunes as $jeune) {
            Mail::to($jeune->email)->queue(new NewMentorsWeekly($jeune, $newMentors));
        }
    }
}