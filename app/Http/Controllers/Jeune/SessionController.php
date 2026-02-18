<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Liste des séances du jeune
     */
    public function index()
    {
        $user = auth()->user();

        // Upcoming: Future sessions + NOT globally cancelled + NOT pivot cancelled
        $upcomingSessions = $user->mentoringSessionsAsMentee()
            ->where('mentoring_sessions.scheduled_at', '>=', now())
            ->where('mentoring_sessions.status', '!=', 'cancelled')
            ->where('mentoring_sessions.status', '!=', 'completed')
            ->wherePivotNotIn('status', ['cancelled', 'rejected']) // EXCLUDE if I cancelled locally
            ->orderBy('mentoring_sessions.scheduled_at', 'asc')
            ->get();

        // Past: Past dates OR Globally cancelled OR Completed OR Locally cancelled
        $pastSessions = $user->mentoringSessionsAsMentee()
            ->where(function ($query) {
            $query->where('mentoring_sessions.scheduled_at', '<', now())
                ->orWhere('mentoring_sessions.status', 'cancelled')
                ->orWhere('mentoring_sessions.status', 'completed')
                ->orWhereIn('mentoring_session_user.status', ['cancelled', 'rejected']); // INCLUDE if I cancelled locally
        })
            ->orderBy('mentoring_sessions.scheduled_at', 'desc')
            ->paginate(10);

        return view('jeune.mentorship.sessions.index', compact('upcomingSessions', 'pastSessions'));
    }

    /**
     * Vue Calendrier
     */
    public function calendar()
    {
        $user = auth()->user();

        $sessions = $user->mentoringSessionsAsMentee()
            ->where('mentoring_sessions.status', '!=', 'cancelled')
            ->orderBy('mentoring_sessions.scheduled_at', 'asc')
            ->get();

        return view('jeune.mentorship.sessions.calendar', compact('sessions'));
    }

    /**
     * Formulaire de réservation de séance (Calendrier/Disponibilités du mentor)
     */
    public function create(User $mentor)
    {
        $user = auth()->user();

        // Vérifier relation active
        $isMentee = Mentorship::where('mentor_id', $mentor->id)
            ->where('mentee_id', $user->id)
            ->where('status', 'accepted')
            ->exists();

        if (!$isMentee) {
            return redirect()->route('jeune.mentorship.index')->with('error', 'Vous devez être accepté par ce mentor pour réserver une séance.');
        }

        // Récupérer disponibilités (simplifiées pour l'instant)
        // Idéalement on passe les dispos en JSON pour Alpine/FullCalendar
        $availabilities = $mentor->mentorAvailabilities;

        return view('jeune.mentorship.sessions.create', compact('mentor', 'availabilities'));
    }

    /**
     * Enregistrer la réservation
     */
    public function store(Request $request)
    {
        $request->validate([
            'mentor_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $mentor = User::findOrFail($request->mentor_id);

        // TODO: Vérifier disponibilité réelle (conflits)

        // Création de la séance (statut 'proposed' ou 'pending_payment' si payant)
        // Pour l'instant on suppose gratuit ou post-paiement manuel
        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'proposed', // Attente validation mentor
            'created_by' => 'mentee',
        ]);

        // Attacher le jeune
        $session->mentees()->attach($user->id, ['status' => 'accepted']); // Le jeune accepte sa propre demande

        // Notification email au mentor
        app(\App\Services\MentorshipNotificationService::class)->sendSessionProposed($session);

        return redirect()->route('jeune.sessions.index')->with('success', 'Votre demande de séance a été envoyée au mentor.');
    }

    /**
     * Voir les détails d'une séance (et le compte rendu)
     */
    public function show(MentoringSession $session)
    {
        $user = auth()->user();

        // Vérifier que la séance appartient au jeune
        // On check via la relation many-to-many ou direct si c'est le createur
        // Ici simple verification via mentees()
        if (!$session->mentees->contains($user->id)) {
            abort(403);
        }

        return view('jeune.mentorship.sessions.show', compact('session'));
    }


    /**
     * Annuler une séance
     */
    /**
     * Annuler une séance (Logic Smart Cancellation with Refund Rules)
     */
    public function cancel(Request $request, MentoringSession $session)
    {
        $user = auth()->user();

        if (!$session->mentees->contains($user->id)) {
            abort(403);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $walletService = app(\App\Services\WalletService::class);

        // 1. Refund Logic
        if ($session->is_paid) {
            $hoursToSession = now()->diffInHours($session->scheduled_at, false);

            // If session is more than 24h away -> 100% refund. Otherwise 75%.
            $refundRatio = ($hoursToSession >= 24) ? 1.0 : 0.75;

            $walletService->refundJeune($session, $user, $refundRatio);
        }

        // 2. Update Pivot for THIS user
        $session->mentees()->updateExistingPivot($user->id, [
            'status' => 'cancelled',
            'rejection_reason' => $request->cancel_reason
        ]);

        // 3. Check if ANY active mentees remain
        $activeMenteesCount = $session->mentees()
            ->wherePivotNotIn('status', ['cancelled', 'rejected'])
            ->count();

        // 4. If NO active mentees left, cancel the global session
        if ($activeMenteesCount === 0) {
            $session->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Tous les participants ont annulé.',
            ]);
        }

        // Notification email d'annulation
        app(\App\Services\MentorshipNotificationService::class)->sendSessionCancelled($session, $user);

        return redirect()->route('jeune.sessions.index')
            ->with('success', 'Votre participation à la séance a été annulée. ' . ($session->is_paid ? 'Votre remboursement a été traité.' : ''));
    }

    /**
     * Payer et Rejoindre une séance
     */
    public function payAndJoin(MentoringSession $session)
    {
        $user = auth()->user();

        // 1. Vérif Participant
        if (!$session->mentees->contains($user->id)) {
            abort(403);
        }

        // 2. Vérif du statut de paiement de l'utilisateur (Pivot)
        $userPivot = $session->mentees()->where('user_id', $user->id)->first()->pivot;

        // Si gratuit ou déjà payé (statut pivot = accepted) => Redirection directe
        if (!$session->is_paid || $userPivot->status === 'accepted') {
            return redirect()->route('meeting.show', $session->meeting_id ?? 'error');
        }

        // 3. Conversion & Vérif Solde
        $price = $session->credit_cost;
        $balance = $user->credits_balance;

        if ($balance < $price) {
            return redirect()->route('jeune.wallet.index')
                ->with('error', "Solde insuffisant ($balance crédits dispos). Il vous manque " . ($price - $balance) . " crédits pour cette séance.");
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $session, $price) {
                $walletService = app(\App\Services\WalletService::class);

                // Debit Jeune
                $walletService->deductCredits(
                    $user,
                    $price,
                    'expense',
                    "Paiement séance (Escrow) : {$session->title}",
                    $session
                );

                // NOTE: We no longer credit the mentor immediately.
                // The payout is triggered upon report submission in Mentor\SessionController.

                // Update Session Status
                $session->update(['status' => 'confirmed']);
                $session->mentees()->updateExistingPivot($user->id, ['status' => 'accepted']);

                // Notification email de confirmation
                $notificationService = app(\App\Services\MentorshipNotificationService::class);
                $notificationService->sendSessionConfirmed($session);
                $notificationService->sendSessionPayment($session, $user, $price);

                // Notify mentor that payment is received but PENDING (Escrow)
                $mentor = $session->mentor;
                if ($mentor) {
                    $notificationService->sendPaymentReceived($session, $mentor, $session->price);
                }
            });

            return redirect()->route('meeting.show', $session->meeting_id)
                ->with('success', 'Paiement effectué avec succès. Les fonds sont en attente et seront libérés au mentor après la séance.');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', "Erreur lors du paiement : " . $e->getMessage());
        }
    }
}