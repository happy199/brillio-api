<?php

namespace App\Services;

use App\Mail\Mentorship\MentorshipAccepted;
use App\Mail\Mentorship\MentorshipRequested;
use App\Mail\Session\SessionCompleted;
use App\Mail\Session\SessionConfirmed;
use App\Mail\Session\SessionProposed;
use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MentorshipNotificationService
{
    /**
     * Envoyer une notification pour une nouvelle demande de mentorat (au mentor)
     */
    public function sendMentorshipRequest(Mentorship $mentorship)
    {
        $mentor = $mentorship->mentor;
        $mentee = $mentorship->mentee;

        $acceptUrl = route('mentor.mentorship.accept', ['mentorship' => $mentorship->id]);
        $refuseUrl = route('mentor.mentorship.refuse', ['mentorship' => $mentorship->id]);

        Mail::to($mentor->email)->send(new MentorshipRequested($mentorship, $mentor, $mentee, $acceptUrl, $refuseUrl));
    }

    /**
     * Envoyer une notification pour une demande de mentorat acceptée (au jeune)
     */
    public function sendMentorshipAccepted(Mentorship $mentorship)
    {
        $mentor = $mentorship->mentor;
        $mentee = $mentorship->mentee;

        $bookingUrl = route('jeune.sessions.create', ['mentor' => $mentor->id]);

        Mail::to($mentee->email)->send(new MentorshipAccepted($mentorship, $mentor, $mentee, $bookingUrl));
    }

    /**
     * Envoyer une notification pour une session proposée (au jeune)
     */
    public function sendSessionProposed(MentoringSession $session)
    {
        $mentor = $session->mentor;
        // Pour une proposition, on assume un seul jeune (V1) ou on envoie à tous les participants
        foreach ($session->mentees as $mentee) {
            $creditPrice = SystemSetting::getValue('credit_price_jeune', 50);
            $menteeCredits = (int)floor($session->price / $creditPrice);

            // Pour une séance proposée, on redirige vers le détail de la séance dans l'espace jeune
            $sessionUrl = route('jeune.sessions.show', ['session' => $session->id]);

            Mail::to($mentee->email)->send(new SessionProposed(
                $session,
                $mentor,
                $mentee,
                $menteeCredits,
                $sessionUrl, // acceptUrl (le jeune pourra agir sur la page)
                $sessionUrl // refuseUrl
                ));
        }
    }

    /**
     * Envoyer une notification pour une session confirmée (tout le monde)
     */
    public function sendSessionConfirmed(MentoringSession $session)
    {
        $mentor = $session->mentor;
        $mentees = $session->mentees;
        $calendarUrl = route('jeune.sessions.calendar'); // URL générique ou specifique

        // Envoyer au mentor
        Mail::to($mentor->email)->send(new SessionConfirmed($session, $mentor, $mentees, route('mentor.mentorship.calendar')));

        // Envoyer à chaque jeune
        foreach ($mentees as $mentee) {
            Mail::to($mentee->email)->send(new SessionConfirmed($session, $mentee, $mentees, $calendarUrl));
        }
    }

    /**
     * Envoyer une notification pour une session terminée (tout le monde)
     */
    public function sendSessionCompleted(MentoringSession $session)
    {
        $mentor = $session->mentor;
        $mentees = $session->mentees;

        $sessionUrl = route('jeune.sessions.show', ['session' => $session->id]);
        $bookingUrl = route('jeune.sessions.create', ['mentor' => $mentor->id]);

        // Envoyer au mentor
        Mail::to($mentor->email)->send(new SessionCompleted(
            $session,
            $mentor,
            $mentees,
            route('mentor.mentorship.sessions.show', ['session' => $session->id]),
            ''
            ));

        // Envoyer à chaque jeune
        foreach ($mentees as $mentee) {
            Mail::to($mentee->email)->send(new SessionCompleted(
                $session,
                $mentee,
                $mentees,
                $sessionUrl,
                $bookingUrl
                ));
        }
    }
}