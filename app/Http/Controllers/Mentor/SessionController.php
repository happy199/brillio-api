<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /**
     * Dashboard calendrier et séances à venir
     */
    public function index()
    {
        $mentor = Auth::user();

        // Récupérer les séances à venir
        $upcomingSessions = $mentor->mentoringSessionsAsMentor()
            ->where('scheduled_at', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get();

        // Récupérer les disponibilités
        $availabilities = $mentor->mentorAvailabilities()->get();

        // Récupérer historique (passé, annulé ou terminé)
        $pastSessionsQuery = $mentor->mentoringSessionsAsMentor()
            ->where(function ($query) {
                $query->where('scheduled_at', '<', now())
                    ->orWhere('status', 'cancelled')
                    ->orWhere('status', 'completed');
            })
            ->orderByDesc('scheduled_at');

        $profile = $mentor->mentorProfile;
        $hasUnlockedHistory = $profile && $profile->has_unlocked_session_history;

        if ($hasUnlockedHistory) {
            $pastSessions = $pastSessionsQuery->paginate(10);
        } else {
            $pastSessions = $pastSessionsQuery->take(10)->get();
        }

        return view('mentor.mentorship.calendar', compact('upcomingSessions', 'availabilities', 'pastSessions', 'hasUnlockedHistory'));
    }

    /**
     * Sauvegarder les disponibilités (Simple V1: Remplacer tout pour un jour donné ou global)
     * Pour V1, on peut imaginer un formulaire qui envoie une liste de slots.
     */
    /**
     * Sauvegarder les disponibilités
     * Gère les disponibilités récurrentes et ponctuelles.
     */
    public function storeAvailability(Request $request)
    {
        $request->validate([
            'availabilities' => 'array',
            'availabilities.*.is_recurring' => 'required|boolean',
            'availabilities.*.day_of_week' => 'nullable|integer|min:0|max:6|required_if:availabilities.*.is_recurring,true',
            'availabilities.*.specific_date' => 'nullable|date|required_if:availabilities.*.is_recurring,false',
            'availabilities.*.start_time' => 'required|date_format:H:i',
            'availabilities.*.end_time' => 'required|date_format:H:i|after:availabilities.*.start_time',
        ]);

        $mentor = Auth::user();

        // On supprime les anciennes disponibilités pour éviter les doublons/conflits
        // TODO: Pour une version plus complexe, on pourrait faire un diff ou gérer par ID
        $mentor->mentorAvailabilities()->delete();

        if ($request->has('availabilities')) {
            foreach ($request->availabilities as $slot) {
                $mentor->mentorAvailabilities()->create([
                    'mentor_id' => $mentor->id,
                    'is_recurring' => $slot['is_recurring'],
                    'day_of_week' => $slot['is_recurring'] ? $slot['day_of_week'] : null,
                    'specific_date' => ! $slot['is_recurring'] ? $slot['specific_date'] : null,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Disponibilités mises à jour.');
    }

    /**
     * Formulaire de création de séance
     */
    public function create()
    {
        $mentor = Auth::user();
        // Récupérer seulement les mentés acceptés
        $mentees = $mentor->mentorshipsAsMentor()
            ->where('status', 'accepted')
            ->with('mentee')
            ->get()
            ->pluck('mentee');

        return view('mentor.mentorship.sessions.create', compact('mentees'));
    }

    /**
     * Enregistrer une nouvelle séance
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mentee_ids' => 'required|array|min:1',
            'mentee_ids.*' => 'exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0|required_if:is_paid,true',
        ]);

        $mentor = Auth::user();

        // Génération lien Jitsi sécurisé (nom de room complexe)
        // meet.jit.si/Brillio_{MentorID}_{Random}_{Timestamp}
        $roomName = 'Brillio_'.$mentor->id.'_'.Str::random(10).'_'.time();
        $meetingLink = 'https://meet.jit.si/'.$roomName;

        $session = MentoringSession::create([
            'mentor_id' => $mentor->id,
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'is_paid' => $request->boolean('is_paid'),
            'price' => $request->boolean('is_paid') ? $request->price : 0,
            // Si payant => pending_payment, sinon confirmed direct
            'status' => $request->boolean('is_paid') ? 'pending_payment' : 'confirmed',
            'meeting_link' => $meetingLink,
            'created_by' => 'mentor',
        ]);

        // Attacher les participants (mentees)
        $pivotStatus = $request->boolean('is_paid') ? 'pending' : 'accepted';
        foreach ($request->mentee_ids as $menteeId) {
            $session->mentees()->attach($menteeId, ['status' => $pivotStatus]);
        }

        // Notification email au jeune
        app(\App\Services\MentorshipNotificationService::class)->sendSessionProposed($session);

        return redirect()->route('mentor.mentorship.sessions.show', $session)
            ->with('success', 'Séance proposée avec succès.');
    }

    /**
     * Afficher une séance
     */
    public function show(MentoringSession $session)
    {
        $session->load(['mentees.jeuneProfile', 'mentor']);

        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        return view('mentor.mentorship.sessions.show', compact('session'));
    }

    /**
     * Formulaire d'édition d'une séance
     */
    public function edit(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $mentor = Auth::user();
        // Récupérer les mentés acceptés pour la liste de choix
        $mentees = $mentor->mentorshipsAsMentor()
            ->where('status', 'accepted')
            ->with('mentee')
            ->get()
            ->pluck('mentee');

        return view('mentor.mentorship.sessions.edit', compact('session', 'mentees'));
    }

    /**
     * Mettre à jour une séance
     */
    public function update(Request $request, MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'nullable|numeric|min:0',
            'mentee_ids' => 'sometimes|array', // Optional to allow no changes, but logic below handles it
            'mentee_ids.*' => 'exists:users,id',
        ]);

        // Sécurité : Ne pas autoriser le changement de prix si la séance est déjà confirmée ou terminée
        $canChangePrice = ! in_array($session->status, ['confirmed', 'completed']);
        $newPrice = ($session->is_paid && $canChangePrice) ? $request->price : $session->price;

        $session->update([
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'price' => $newPrice,
        ]);

        // Gestion des participants (Sync Intelligent)
        if ($request->has('mentee_ids')) {
            $newMenteeIds = $request->mentee_ids;

            // 1. Detach removed mentees (those NOT in new list)
            // But be careful not to detach cancelled ones if we want to keep history?
            // For simplicity/MVP: User wants to "remove", so we detach.
            $session->mentees()->wherePivotNotIn('user_id', $newMenteeIds)->detach();

            // 2. Attach NEW mentees
            // We need to check which ones are already there to preserve their status (e.g. if they paid)
            $existingMenteeIds = $session->mentees()->pluck('user_id')->toArray();
            $idsToAttach = array_diff($newMenteeIds, $existingMenteeIds);

            $pivotStatus = $session->is_paid ? 'pending' : 'accepted'; // 'pending' for new paid additions

            foreach ($idsToAttach as $id) {
                $session->mentees()->attach($id, ['status' => $pivotStatus]);
            }
        }

        // Notification de modification
        try {
            $session->load(['mentor', 'mentees', 'additionalMentors', 'organization']);
            app(\App\Services\MentorshipNotificationService::class)->sendSessionUpdated($session, auth()->user());
        } catch (\Exception $e) {
            \Log::error("Erreur notifications modification session mentor : " . $e->getMessage());
        }

        return redirect()->route('mentor.mentorship.sessions.show', $session)
            ->with('success', 'Séance modifiée avec succès.');
    }

    /**
     * Mettre à jour le compte rendu (Après séance)
     */
    public function updateReport(Request $request, MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'progress' => 'nullable|string',
            'obstacles' => 'nullable|string',
            'smart_goals' => 'nullable|string',
        ]);

        $report = [
            'progress' => $request->progress,
            'obstacles' => $request->obstacles,
            'smart_goals' => $request->smart_goals,
        ];

        $session->update([
            'report_content' => $report,
            'status' => 'completed', // Marquer comme terminée si compte rendu fait
        ]);

        // CRITICAL: Trigger Payout to Mentor now that the report is submitted
        if ($session->is_paid) {
            app(\App\Services\WalletService::class)->payoutMentor($session);
        }

        // Notification email de fin de séance
        $notificationService = app(\App\Services\MentorshipNotificationService::class);
        $notificationService->sendSessionCompleted($session);
        $notificationService->sendReportAvailableNotification($session);

        return redirect()->back()->with('success', 'Compte rendu enregistré. Votre rémunération a été créditée sur votre portefeuille.');
    }

    /**
     * Annuler une séance
     */
    public function cancel(Request $request, MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        // 1. Refund all paid mentees (100% since it's mentor's cancellation)
        if ($session->is_paid) {
            $walletService = app(\App\Services\WalletService::class);
            foreach ($session->mentees as $mentee) {
                $walletService->refundJeune($session, $mentee, 1.0);
            }
        }

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        // Notification email d'annulation
        app(\App\Services\MentorshipNotificationService::class)->sendSessionCancelled($session, Auth::user());

        return redirect()->route('mentor.mentorship.calendar')
            ->with('success', 'Séance annulée. '.($session->is_paid ? 'Les participants ont été intégralement remboursés.' : ''));
    }

    /**
     * Accepter une demande de séance
     */
    public function accept(Request $request, MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:500|required_if:is_paid,1',
        ]);

        // Génération lien Jitsi
        $mentor = Auth::user();
        $roomName = 'Brillio_'.$mentor->id.'_'.Str::random(10).'_'.time();
        $meetingLink = 'https://meet.jit.si/'.$roomName;

        $isPaid = $request->boolean('is_paid');

        $session->update([
            'status' => $isPaid ? 'pending_payment' : 'confirmed', // Si payant, attend paiement. Sinon confirmé direct.
            'meeting_link' => $meetingLink,
            'is_paid' => $isPaid,
            'price' => $isPaid ? $request->price : 0,
        ]);

        // IMPORTANT: Si payant, on remet le statut via pivot à 'pending' pour forcer le paiement
        if ($isPaid) {
            // On le fait pour tous les mentees (normalement 1 seul pour une demande)
            foreach ($session->mentees as $mentee) {
                $session->mentees()->updateExistingPivot($mentee->id, ['status' => 'pending']);
            }
        }

        $message = $isPaid
            ? 'Séance acceptée. Le jeune doit maintenant procéder au paiement.'
            : 'Séance acceptée et confirmée.';

        // Si gratuit (confirmé direct), notifier
        if (! $isPaid) {
            app(\App\Services\MentorshipNotificationService::class)->sendSessionConfirmed($session);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Refuser une demande de séance
     */
    public function refuse(Request $request, MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'refusal_reason' => 'required|string|max:500',
        ]);

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->refusal_reason,
        ]);

        // Notification email de refus au jeune
        app(\App\Services\MentorshipNotificationService::class)->sendSessionRefused($session, $request->refusal_reason);

        return redirect()->back()->with('success', 'Demande de séance refusée.');
    }

    /**
     * Débloquer l'historique complet des séances contre 5 crédits
     */
    public function unlockHistory(Request $request)
    {
        $mentor = Auth::user();
        $profile = $mentor->mentorProfile;

        if (! $profile) {
            return redirect()->back()->with('error', 'Profil introuvable.');
        }

        if ($profile->has_unlocked_session_history) {
            return redirect()->back()->with('info', 'Historique déjà débloqué.');
        }

        $cost = app(\App\Services\WalletService::class)->getFeatureCost('unlock_history', 5);

        if ($mentor->credits_balance < $cost) {
            return redirect()->route('mentor.wallet.index')->with('warning', "Votre solde de crédits est insuffisant ($cost crédits requis). Veuillez recharger votre compte pour continuer.");
        }

        app(\App\Services\WalletService::class)->deductCredits(
            $mentor,
            $cost,
            'feature_unlock',
            "Déblocage de l'historique complet des séances"
        );
        $profile->update(['has_unlocked_session_history' => true]);

        return redirect()->back()->with('success', 'Historique complet débloqué avec succès !');
    }

    /**
     * Télécharger un compte rendu individuel en PDF
     */
    public function downloadReport(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        if ($session->status !== 'completed' || empty($session->report_content)) {
            return redirect()->back()->with('error', "Ce compte rendu n'est pas encore disponible.");
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mentor.reports.session_pdf', compact('session'));

        return $pdf->download('compte_rendu_seance_'.$session->id.'.pdf');
    }

    /**
     * Générer un rapport compilé de plusieurs comptes rendus (coûte 5 crédits)
     */
    public function downloadCompiledReports(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|string',
        ]);

        $mentor = Auth::user();

        $cost = app(\App\Services\WalletService::class)->getFeatureCost('compiled_report', 5);

        if ($mentor->credits_balance < $cost) {
            return redirect()->route('mentor.wallet.index')->with('warning', "Votre solde de crédits est insuffisant ($cost crédits requis). Veuillez recharger votre compte pour continuer.");
        }

        $ids = explode(',', $request->session_ids);

        $sessions = MentoringSession::where('mentor_id', $mentor->id)
            ->whereIn('id', $ids)
            ->where('status', 'completed')
            ->orderBy('scheduled_at') // Chronological order is usually better for combined reports
            ->get();

        if ($sessions->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun compte rendu valide sélectionné.');
        }

        app(\App\Services\WalletService::class)->deductCredits(
            $mentor,
            $cost,
            'feature_use',
            "Génération d'un rapport compilé (".$sessions->count().' séances)'
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mentor.reports.compiled_sessions_pdf', compact('sessions'));

        return $pdf->download('rapport_compile_seances_'.date('Ymd_His').'.pdf');
    }

    /**
     * Télécharger la transcription d'une séance en PDF (5 crédits)
     */
    public function downloadTranscription(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        if (! $session->has_transcription) {
            return redirect()->back()->with('error', "La transcription n'est pas encore disponible pour cette séance.");
        }

        $mentor = Auth::user();
        $cost = app(\App\Services\WalletService::class)->getFeatureCost('transcription_download', 5);

        if ($mentor->credits_balance < $cost) {
            $missing = $cost - $mentor->credits_balance;

            return redirect()->route('mentor.wallet.index')->with('warning', "Votre solde de crédits est insuffisant ($cost crédits requis). Il vous manque $missing crédits pour télécharger cette transcription.");
        }

        app(\App\Services\WalletService::class)->deductCredits(
            $mentor,
            $cost,
            'feature_use',
            "Téléchargement de la transcription de la séance : {$session->title}",
            $session
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('common.reports.transcription_pdf', compact('session'));

        return $pdf->download('transcription_seance_'.$session->id.'.pdf');
    }

    /**
     * Pré-remplir le compte rendu avec l'IA (5 crédits)
     */
    public function prefillReport(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        if (! $session->has_transcription) {
            return redirect()->back()->with('error', "La transcription n'est pas encore disponible. Vous pourrez pré-remplir le rapport une fois le meeting terminé et la transcription générée.");
        }

        $mentor = Auth::user();
        $cost = app(\App\Services\WalletService::class)->getFeatureCost('ai_report_generation', 5);

        if ($mentor->credits_balance < $cost) {
            $missing = $cost - $mentor->credits_balance;

            return redirect()->route('mentor.wallet.index')->with('warning', "Votre solde de crédits est insuffisant ($cost crédits requis). Il vous manque $missing crédits pour utiliser l'IA.");
        }

        $suggestedReport = app(\App\Services\BrillioIAService::class)->summarizeTranscription($session->transcription_raw);

        if (! $suggestedReport) {
            return redirect()->back()->with('error', "L'IA n'a pas pu générer le résumé. Veuillez réessayer ou remplir manuellement.");
        }

        app(\App\Services\WalletService::class)->deductCredits(
            $mentor,
            $cost,
            'feature_use',
            "Pré-remplissage du compte rendu par l'IA : {$session->title}",
            $session
        );

        // On ne sauvegarde pas encore, on renvoie vers la page de show avec les données pré-remplies en session
        // ou on met à jour les champs si le mentor veut juste les voir.
        // L'utilisateur a demandé "pré-remplir ce compte rendu", donc je vais les mettre en session ou les passer en paramètre.
        // Mais comme c'est une action POST/GET, je vais rediriger vers le show avec les inputs.

        return redirect()->route('mentor.mentorship.sessions.show', $session)
            ->with('prefilled_report', $suggestedReport)
            ->with('success', "Le compte rendu a été pré-rempli par l'IA avec succès ($cost crédits déduits).");
    }
}
