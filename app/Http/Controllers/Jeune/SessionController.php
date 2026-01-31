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

        $upcomingSessions = $user->mentoringSessionsAsMentee()
            ->where('mentoring_sessions.scheduled_at', '>=', now())
            ->where('mentoring_sessions.status', '!=', 'cancelled')
            ->orderBy('mentoring_sessions.scheduled_at', 'asc')
            ->get();

        $pastSessions = $user->mentoringSessionsAsMentee()
            ->where(function ($query) {
                $query->where('mentoring_sessions.scheduled_at', '<', now())
                    ->orWhere('mentoring_sessions.status', 'cancelled');
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
    public function cancel(Request $request, MentoringSession $session)
    {
        $user = auth()->user();

        if (!$session->mentees->contains($user->id)) {
            abort(403);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return redirect()->route('jeune.sessions.index')
            ->with('success', 'La séance a été annulée avec succès.');
    }
}
