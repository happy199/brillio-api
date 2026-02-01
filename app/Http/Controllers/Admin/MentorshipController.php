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
        $query = Mentorship::with(['mentor', 'mentee']);

        // Filtres par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre Global (Recherche texte)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('mentor', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('mentee', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filtres Spécifiques (Mentor / Menté)
        if ($request->filled('mentor_name')) {
            $mentorName = $request->mentor_name;
            $query->whereHas('mentor', function ($q) use ($mentorName) {
                $q->where('name', 'like', "%{$mentorName}%");
            });
        }
        if ($request->filled('mentee_name')) {
            $menteeName = $request->mentee_name;
            $query->whereHas('mentee', function ($q) use ($menteeName) {
                $q->where('name', 'like', "%{$menteeName}%");
            });
        }

        $requests = $query->latest()->paginate(20);

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
        $query = MentoringSession::with(['mentor', 'mentees']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        // Filtre Global (Recherche texte) - Recherche dans titre ou noms
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('mentor', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('mentees', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filtres Spécifiques
        if ($request->filled('mentor_name')) {
            $mentorName = $request->mentor_name;
            $query->whereHas('mentor', function ($q) use ($mentorName) {
                $q->where('name', 'like', "%{$mentorName}%");
            });
        }
        if ($request->filled('mentee_name')) {
            $menteeName = $request->mentee_name;
            $query->whereHas('mentees', function ($q) use ($menteeName) {
                $q->where('name', 'like', "%{$menteeName}%");
            });
        }

        $sessions = $query->latest('scheduled_at')->paginate(20);

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
        $session->load(['mentor', 'mentees', 'transaction']);
        return view('admin.mentorship.sessions.show', compact('session'));
    }
}
