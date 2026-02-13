<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\MentoringSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Liste des séances des jeunes parrainés
     */
    public function index(Request $request)
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        $query = MentoringSession::query()
            ->whereHas('mentees', function ($q) use ($organization) {
            $q->where('sponsored_by_organization_id', $organization->id);
        })
            ->with(['mentor', 'mentees']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->orderBy('scheduled_at', 'desc')->paginate(12)->withQueryString();

        return view('organization.sessions.index', compact('organization', 'sessions'));
    }

    /**
     * Calendrier des séances
     */
    public function calendar()
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        return view('organization.sessions.calendar', compact('organization'));
    }

    /**
     * API pour le calendrier (JSON)
     */
    public function events(Request $request)
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        $sessions = MentoringSession::query()
            ->whereHas('mentees', function ($q) use ($organization) {
            $q->where('sponsored_by_organization_id', $organization->id);
        })
            ->with(['mentor', 'mentees'])
            ->get()
            ->map(function ($session) {
            $menteeNames = $session->mentees->pluck('name')->implode(', ');
            return [
            'id' => $session->id,
            'title' => $session->title . " ($menteeNames)",
            'start' => $session->scheduled_at->toIso8601String(),
            'end' => $session->scheduled_at->addMinutes($session->duration_minutes)->toIso8601String(),
            'url' => route('organization.sessions.show', $session),
            'color' => $this->getStatusColor($session->status),
            ];
        });

        return response()->json($sessions);
    }

    /**
     * Détail d'une séance
     */
    public function show(MentoringSession $session)
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        // Vérification : au moins un menté doit être parrainé par cette organisation
        $isAuthorized = $session->mentees()->where('sponsored_by_organization_id', $organization->id)->exists();

        if (!$isAuthorized) {
            abort(403, 'Accès non autorisé');
        }

        $session->load(['mentor', 'mentees']);

        return view('organization.sessions.show', compact('organization', 'session'));
    }

    private function getStatusColor($status)
    {
        return match ($status) {
                'confirmed' => '#10b981', // green-500
                'completed' => '#6366f1', // indigo-500
                'cancelled' => '#ef4444', // red-500
                default => '#f59e0b', // amber-500
            };
    }
}