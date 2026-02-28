<?php

namespace App\Services;

use App\Mail\Account\AccountArchivedByUser;
use App\Mail\Account\AccountDeleted;
use App\Mail\Mentorship\MentorshipAccepted;
use App\Mail\Mentorship\MentorshipRefused;
use App\Mail\Mentorship\MentorshipRequested;
use App\Mail\Onboarding\WelcomeJeune;
use App\Mail\Onboarding\WelcomeMentor;
use App\Mail\Resource\ResourcePurchased;
use App\Mail\Resource\ResourceRejected;
use App\Mail\Resource\ResourceValidated;
use App\Mail\Session\ReportReminder;
use App\Mail\Session\SessionCancelled;
use App\Mail\Session\SessionCompleted;
use App\Mail\Session\SessionConfirmed;
use App\Mail\Session\SessionProposed;
use App\Mail\Session\SessionRefused;
use App\Mail\Support\ContactConfirmation;
use App\Mail\Wallet\CreditRecharged;
use App\Mail\Wallet\IncomeReleased;
use App\Mail\Wallet\PaymentReceived;
use App\Mail\Wallet\PayoutProcessed;
use App\Mail\Wallet\PayoutRequested;
use App\Mail\Wallet\SessionPaid;
use App\Mail\Wallet\CreditGiftedMail;
use App\Mail\Wallet\SubscriptionActivatedMail;
use App\Mail\Wallet\CreditPackPurchasedMail;
use App\Mail\Resource\ResourceGiftedMail;
use App\Mail\Session\ReportAvailableMail;
use App\Mail\Wallet\SubscriptionExpiringMail;
use App\Mail\Wallet\SubscriptionDowngradedMail;
use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\Resource;
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

        $requestsUrl = route('mentor.mentorship.index');

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

    /**
     * Envoyer une notification de revenus libérés (après compte rendu)
     */
    public function sendIncomeReleased(MentoringSession $session, User $mentor, int $amount)
    {
        Mail::to($mentor->email)->send(new IncomeReleased($mentor, $session, $amount));
    }

    /**
     * Envoyer l'email de bienvenue (Onboarding personnalisé)
     */
    public function sendWelcomeEmail(User $user)
    {
        if ($user->isMentor()) {
            Mail::to($user->email)->send(new WelcomeMentor($user));
        }
        else {
            Mail::to($user->email)->send(new WelcomeJeune($user));
        }
    }

    /**
     * Envoyer un rappel de compte rendu au mentor
     */
    public function sendReportReminder(MentoringSession $session)
    {
        if ($session->mentor) {
            Mail::to($session->mentor->email)->send(new ReportReminder($session));
        }
    }

    /**
     * Envoyer une notification de compte supprimé par l'admin
     */
    public function sendAccountDeleted(User $user, string $reason = '')
    {
        Mail::to($user->email)->send(new AccountDeleted($user, $reason));
    }

    /**
     * Envoyer une notification de compte archivé par l'utilisateur
     */
    public function sendAccountArchivedByUser(User $user)
    {
        Mail::to($user->email)->send(new AccountArchivedByUser($user));
    }

    /**
     * Envoyer une confirmation de réception de message de contact
     */
    public function sendContactConfirmation(User $user, array $data)
    {
        Mail::to($user->email)->send(new ContactConfirmation($user, $data));
    }

    /**
     * Envoyer une notification de ressource validée
     */
    public function sendResourceValidated(Resource $resource)
    {
        if ($resource->user) {
            Mail::to($resource->user->email)->send(new ResourceValidated($resource));
        }
    }

    /**
     * Envoyer une notification de ressource rejetée
     */
    public function sendResourceRejected(Resource $resource)
    {
        if ($resource->user) {
            Mail::to($resource->user->email)->send(new ResourceRejected($resource));
        }
    }

    /**
     * Envoyer une notification d'achat de ressource
     */
    public function sendResourcePurchased(Resource $resource, User $buyer, int $creditsEarned)
    {
        if ($resource->user) {
            Mail::to($resource->user->email)->send(new ResourcePurchased($resource, $buyer, $creditsEarned));
        }
    }

    /**
     * Notifier un jeune lorsqu'il reçoit des crédits via une distribution d'organisation
     */
    public function sendCreditGiftedNotification(User $user, \App\Models\Organization $organization, int $amount)
    {
        Mail::to($user->email)->send(new CreditGiftedMail($user, $organization, $amount, $user->credits_balance));
    }

    /**
     * Notifier une organisation de l'activation de son abonnement
     */
    public function sendSubscriptionActivatedNotification(\App\Models\Organization $organization, \App\Models\CreditPack $plan)
    {
        if ($organization->contact_email) {
            Mail::to($organization->contact_email)->send(new SubscriptionActivatedMail($organization, $plan));
        }
    }

    /**
     * Notifier une organisation de l'achat d'un pack de crédits
     */
    public function sendCreditPackPurchasedNotification(\App\Models\Organization $organization, \App\Models\CreditPack $pack)
    {
        if ($organization->contact_email) {
            Mail::to($organization->contact_email)->send(new CreditPackPurchasedMail($organization, $pack, $organization->credits_balance));
        }
    }

    /**
     * Notifier un jeune d'une ressource offerte par son organisation
     */
    public function sendResourceGiftedNotification(User $user, \App\Models\Resource $resource, \App\Models\Organization $organization)
    {
        Mail::to($user->email)->send(new ResourceGiftedMail($user, $resource, $organization));
    }

    /**
     * Notifier le jeune et l'organisation (si applicable) qu'un compte rendu est disponible
     */
    public function sendReportAvailableNotification(MentoringSession $session)
    {
        // 1. Notifier chaque jeune participant
        foreach ($session->mentees as $mentee) {
            $sessionUrl = route('jeune.sessions.show', ['session' => $session->id]);
            Mail::to($mentee->email)->send(new ReportAvailableMail($mentee, $session, $sessionUrl));

            // 2. Notifier l'organisation parrain si le jeune est sponsorisé
            $org = $mentee->sponsoringOrganization;
            if ($org && $org->contact_email) {
                $orgSessionUrl = route('organization.sessions.show', ['session' => $session->id]);
                Mail::to($org->contact_email)->send(new ReportAvailableMail($org->users()->wherePivot('role', 'admin')->first() ?? $mentee, $session, $orgSessionUrl));
            }
        }
    }

    /**
     * Notifier une organisation que son abonnement expire bientôt
     */
    public function sendSubscriptionExpiringNotification(\App\Models\Organization $organization, string $timeLeft)
    {
        if ($organization->contact_email) {
            $renewUrl = route('organization.subscriptions.index');
            Mail::to($organization->contact_email)->send(new SubscriptionExpiringMail($organization, $timeLeft, $renewUrl));
        }
    }

    /**
     * Notifier une organisation qu'elle a été rétrogradée au plan gratuit
     */
    public function sendSubscriptionDowngradedNotification(\App\Models\Organization $organization)
    {
        if ($organization->contact_email) {
            $renewUrl = route('organization.subscriptions.index');
            Mail::to($organization->contact_email)->send(new SubscriptionDowngradedMail($organization, $renewUrl));
        }
    }
}