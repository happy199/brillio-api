<?php

namespace App\Services;

use App\Mail\Mentorship\MentorshipAccepted;
use App\Mail\Mentorship\MentorshipRequested;
use App\Mail\Mentorship\MentorshipRefused;
use App\Mail\Session\SessionCompleted;
use App\Mail\Session\SessionConfirmed;
use App\Mail\Session\SessionProposed;
use App\Mail\Session\SessionRefused;
use App\Mail\Session\SessionCancelled;
use App\Mail\Wallet\CreditRecharged;
use App\Mail\Wallet\SessionPaid;
use App\Mail\Wallet\PaymentReceived;
use App\Mail\Wallet\PayoutRequested;
use App\Mail\Wallet\PayoutProcessed;
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

        $requestsUrl = route('mentor.mentorship.requests');

        Mail::to($mentor->email)->send(new MentorshipRequested($mentorship, $mentor, $mentee, $requestsUrl, $requestsUrl));
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

    /**
     * Envoyer une notification pour un mentorat refusé (au jeune)
     */
    public function sendMentorshipRefused(Mentorship $mentorship, ?string $reason = null)
    {
        $mentor = $mentorship->mentor;
        $mentee = $mentorship->mentee;

        Mail::to($mentee->email)->send(new MentorshipRefused($mentorship, $mentor, $mentee, $reason));
    }

    /**
     * Envoyer une notification pour une séance refusée (au jeune)
     */
    public function sendSessionRefused(MentoringSession $session, ?string $reason = null)
    {
        $mentor = $session->mentor;
        foreach ($session->mentees as $mentee) {
            Mail::to($mentee->email)->send(new SessionRefused($session, $mentor, $mentee, $reason));
        }
    }

    /**
     * Envoyer une notification pour une séance annulée (à tous)
     */
    public function sendSessionCancelled(MentoringSession $session, User $cancelledBy)
    {
        $mentor = $session->mentor;
        $mentees = $session->mentees;

        // Notifier le mentor si ce n'est pas lui qui a annulé
        if ($mentor->id !== $cancelledBy->id) {
            Mail::to($mentor->email)->send(new SessionCancelled($session, $mentor, $cancelledBy, $mentees));
        }

        // Notifier les jeunes qui n'ont pas annulé
        foreach ($mentees as $mentee) {
            if ($mentee->id !== $cancelledBy->id) {
                Mail::to($mentee->email)->send(new SessionCancelled($session, $mentee, $cancelledBy, $mentees));
            }
        }
    }

    /**
     * Envoyer une notification pour une recharge de crédits réussie (au jeune)
     */
    public function sendCreditRecharge(User $user, int $amount)
    {
        Mail::to($user->email)->send(new CreditRecharged($user, $amount, $user->credits_balance));
    }

    /**
     * Envoyer une notification de paiement de séance (au jeune)
     */
    public function sendSessionPayment(MentoringSession $session, User $jeune, int $amount)
    {
        Mail::to($jeune->email)->send(new SessionPaid($jeune, $session, $amount));
    }

    /**
     * Envoyer une notification de revenus reçus (au mentor)
     */
    public function sendPaymentReceived(MentoringSession $session, User $mentor, int $amount)
    {
        Mail::to($mentor->email)->send(new PaymentReceived($mentor, $session, $amount));
    }

    /**
     * Envoyer une notification de demande de retrait soumise (au mentor)
     */
    public function sendPayoutRequested(\App\Models\PayoutRequest $payout)
    {
        Mail::to($payout->mentorProfile->user->email)->send(new PayoutRequested($payout));
    }

    /**
     * Envoyer une notification de retrait traité (succès ou échec) (au mentor)
     */
    public function sendPayoutProcessed(\App\Models\PayoutRequest $payout)
    {
        Mail::to($payout->mentorProfile->user->email)->send(new PayoutProcessed($payout));
    }
}