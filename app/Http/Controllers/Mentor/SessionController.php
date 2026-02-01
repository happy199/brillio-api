<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\MentorAvailability;
use App\Models\Mentorship;
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

        // Récupérer historique (passé)
        $pastSessions = $mentor->mentoringSessionsAsMentor()
            ->where('scheduled_at', '<', now())
            ->orderByDesc('scheduled_at')
            ->take(10)
            ->get();

        return view('mentor.mentorship.calendar', compact('upcomingSessions', 'availabilities', 'pastSessions'));
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
                    'specific_date' => !$slot['is_recurring'] ? $slot['specific_date'] : null,
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
        $roomName = 'Brillio_' . $mentor->id . '_' . Str::random(10) . '_' . time();
        $meetingLink = 'https://meet.jit.si/' . $roomName;

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
        foreach ($request->mentee_ids as $menteeId) {
            $session->mentees()->attach($menteeId, ['status' => 'accepted']);
        }

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

        return view('mentor.mentorship.sessions.edit', compact('session'));
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
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'nullable|numeric|min:0',
        ]);

        $session->update([
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'price' => $session->is_paid ? $request->price : 0, // Update price ONLY if already paid type
            // Note: We do NOT update 'status' or 'is_paid' here to preserve payment state as requested.
        ]);

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

        return redirect()->back()->with('success', 'Compte rendu enregistré.');
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

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        // Remboursement éventuel à gérer ici si déjà payé via Wallet (TODO)

        return redirect()->route('mentor.mentorship.calendar')
            ->with('success', 'Séance annulée.');
    }
    /**
     * Accepter une demande de séance
     */
    public function accept(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        // Génération lien Jitsi
        $mentor = Auth::user();
        $roomName = 'Brillio_' . $mentor->id . '_' . Str::random(10) . '_' . time();
        $meetingLink = 'https://meet.jit.si/' . $roomName;

        $session->update([
            'status' => 'confirmed',
            'meeting_link' => $meetingLink,
        ]);

        return redirect()->back()->with('success', 'Séance acceptée et planifiée.');
    }

    /**
     * Refuser une demande de séance
     */
    public function refuse(MentoringSession $session)
    {
        if ($session->mentor_id !== Auth::id()) {
            abort(403);
        }

        $session->update([
            'status' => 'cancelled',
            'cancel_reason' => 'Refusée par le mentor', // Ou demander une raison spécifique via formulaire
        ]);

        return redirect()->back()->with('success', 'Demande de séance refusée.');
    }
}
