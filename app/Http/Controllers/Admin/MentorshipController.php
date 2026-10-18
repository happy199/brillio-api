<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\Mentorship;
use Illuminate\Http\Request;

class MentorshipController extends Controller
{
    /**
     * Liste des demandes de mentorat (Relations)
     */
    public function requests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:255',
            'mentor_name' => 'nullable|string|max:255',
            'mentee_name' => 'nullable|string|max:255',
        ]);

        $query = Mentorship::with(['mentor', 'mentee']);

        // Filtres par statut
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filtre Global (Recherche texte)
        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('mentor', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('mentee', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filtres Spécifiques (Mentor / Menté)
        $this->applyMentorFilter($query, $validated['mentor_name'] ?? null);
        if (! empty($validated['mentee_name'])) {
            $menteeName = $validated['mentee_name'];
            $query->whereHas('mentee', function ($q) use ($menteeName) {
                $q->where('name', 'like', "%{$menteeName}%");
            });
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Mentorship::count(),
            'pending' => Mentorship::where('status', 'pending')->count(),
            'accepted' => Mentorship::where('status', 'accepted')->count(),
            'rejected' => Mentorship::where('status', 'rejected')->count(),
        ];

        return view('admin.mentorship.requests.index', compact('requests', 'stats'));
    }

    /**
     * Détails d'une demande de mentorat
     */
    public function showRequest(Mentorship $mentorship)
    {
        $mentorship->load(['mentor', 'mentee']);

        return view('admin.mentorship.requests.show', compact('mentorship'));
    }

    /**
     * Liste des séances de mentorat
     */
    public function sessions(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
            'is_paid' => 'nullable',
            'search' => 'nullable|string|max:255',
            'mentor_name' => 'nullable|string|max:255',
            'mentee_name' => 'nullable|string|max:255',
            'organization_id' => 'nullable|integer',
            'mentor_type' => 'nullable|string|max:50',
        ]);

        $query = MentoringSession::with(['mentor', 'mentees', 'evaluations']);

        // Filtres
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        // Filtre Global (Recherche texte) - Recherche dans titre ou noms
        $this->applySessionSearch($query, $validated['search'] ?? null);

        // Filtres Spécifiques (Mentor / Menté)
        $this->applyMentorFilter($query, $validated['mentor_name'] ?? null);
        if (! empty($validated['mentee_name'])) {
            $menteeName = $validated['mentee_name'];
            $query->whereHas('mentees', function ($q) use ($menteeName) {
                $q->where('name', 'like', "%{$menteeName}%");
            });
        }

        // Filtre par organisation (Séances planifiées par une organisation)
        if (! empty($validated['organization_id'])) {
            $query->where('scheduled_by_organization_id', $validated['organization_id']);
        }

        // Filtre par type d'intervenant (Interne / Invité)
        if (! empty($validated['mentor_type'])) {
            if ($validated['mentor_type'] === 'guest') {
                $query->whereHas('mentor', function ($q) {
                    $q->where('is_guest', true);
                });
            } else {
                $query->whereHas('mentor', function ($q) {
                    $q->where('is_guest', false);
                });
            }
        }

        $sessions = $query->latest('scheduled_at')->paginate(20)->withQueryString();

        // Stats pour les séances
        $stats = [
            'total' => MentoringSession::count(),
            'upcoming' => MentoringSession::where('scheduled_at', '>', now())->whereNotIn('status', ['cancelled', 'completed'])->count(),
            'completed' => MentoringSession::where('status', 'completed')->count(),
            'cancelled' => MentoringSession::where('status', 'cancelled')->count(),
        ];

        return view('admin.mentorship.sessions.index', compact('sessions', 'stats'));
    }

    /**
     * Détails d'une séance
     */
    public function showSession(MentoringSession $session)
    {
        $session->load(['mentor', 'mentees', 'transaction', 'evaluations.mentee']);

        return view('admin.mentorship.sessions.show', compact('session'));
    }

    /**
     * Liste des premières séances de mentorat et évaluation de la qualité par les admins
     */
    public function evaluations(Request $request)
    {
        $validated = $request->validate([
            'first_only' => 'nullable',
            'review_status' => 'nullable|string|max:50',
            'mentor_name' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
        ]);

        $query = MentoringSession::with(['mentor', 'mentees', 'evaluations', 'adminReviewer']);

        // Par défaut, afficher les premières séances sauf si filtre désactivé
        $firstSessionsOnly = $request->has('first_only') ? $request->boolean('first_only') : true;

        if ($firstSessionsOnly) {
            $query->where('is_first_session', true);
        }

        // Filtre statut de révision admin
        if (! empty($validated['review_status'])) {
            if ($validated['review_status'] === 'reviewed') {
                $query->whereNotNull('admin_reviewed_at');
            } elseif ($validated['review_status'] === 'pending') {
                $query->whereNull('admin_reviewed_at');
            } else {
                $query->where('admin_evaluation_status', $validated['review_status']);
            }
        }

        // Filtre spécifique mentor (Nom)
        $this->applyMentorFilter($query, $validated['mentor_name'] ?? null);

        // Recherche texte (nom du mentor ou du menté ou titre)
        $this->applySessionSearch($query, $validated['search'] ?? null);

        $sessions = $query->latest('scheduled_at')->paginate(15)->withQueryString();

        $stats = [
            'total_first' => MentoringSession::where('is_first_session', true)->count(),
            'pending_review' => MentoringSession::where('is_first_session', true)->whereNull('admin_reviewed_at')->count(),
            'reviewed' => MentoringSession::where('is_first_session', true)->whereNotNull('admin_reviewed_at')->count(),
            'with_video' => MentoringSession::where('is_first_session', true)->whereNotNull('video_recording_url')->count(),
        ];

        return view('admin.mentorship.evaluations.index', compact('sessions', 'stats', 'firstSessionsOnly'));
    }

    /**
     * Afficher le détail de la séance à évaluer par l'admin
     */
    public function showEvaluation(MentoringSession $session)
    {
        $session->load(['mentor', 'mentees', 'evaluations.mentee', 'adminReviewer']);

        // Trouver la relation de mentorat active/existante entre le mentor et le menté principal
        $mentee = $session->mentees->first();
        $mentorship = null;
        if ($mentee) {
            $mentorship = Mentorship::where('mentor_id', $session->mentor_id)
                ->where('mentee_id', $mentee->id)
                ->first();
        }

        return view('admin.mentorship.evaluations.show', compact('session', 'mentorship', 'mentee'));
    }

    /**
     * Enregistrer l'observation/évaluation admin sur une séance
     */
    public function storeAdminObservation(Request $request, MentoringSession $session)
    {
        $request->validate([
            'admin_observation' => 'required|string|max:2000',
            'admin_evaluation_status' => 'required|string|in:validated,warning_issued,needs_revision,terminated',
        ]);

        $session->update([
            'admin_observation' => $request->admin_observation,
            'admin_evaluation_status' => $request->admin_evaluation_status,
            'admin_reviewed_at' => now(),
            'admin_reviewer_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Votre observation et statut d\'évaluation ont été enregistrés avec succès.');
    }

    /**
     * Arrêter la relation de mentorat entre un mentor et un jeune (action Admin)
     */
    public function stopMentorshipRelationship(Request $request, MentoringSession $session)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'mentee_id' => 'required|exists:users,id',
        ]);

        $mentorship = Mentorship::where('mentor_id', $session->mentor_id)
            ->where('mentee_id', $request->mentee_id)
            ->first();

        if ($mentorship) {
            $mentorship->update([
                'status' => 'disconnected',
                'diction_reason' => 'Interruption par l\'administration : '.$request->reason,
            ]);
        }

        // Marquer le statut d'évaluation de la séance comme arrêté/terminé
        $session->update([
            'admin_evaluation_status' => 'terminated',
            'admin_observation' => ($session->admin_observation ? $session->admin_observation."\n\n" : '').'[Arrêt relation] '.$request->reason,
            'admin_reviewed_at' => now(),
            'admin_reviewer_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'La relation de mentorat entre ce mentor et ce jeune a été interrompue.');
    }

    private function applySessionSearch($query, ?string $search): void
    {
        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('mentor', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('mentees', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }
    }

    private function applyMentorFilter($query, ?string $mentorName): void
    {
        if (filled($mentorName)) {
            $query->whereHas('mentor', fn ($q) => $q->where('name', 'like', "%{$mentorName}%"));
        }
    }
}
