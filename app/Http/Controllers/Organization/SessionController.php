<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\Organization;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Liste des séances des jeunes parrainés
     */
    /**
     * Liste des séances des jeunes parrainés
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (!$organization->isPro()) {
            $sessions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        }
        else {
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
        }

        return view('organization.sessions.index', compact('organization', 'sessions'));
    }

    /**
     * Calendrier des séances
     */
    public function calendar()
    {
        $organization = $this->getCurrentOrganization();

        if (!$organization->isPro()) {
            $sessions = collect();
        }
        else {
            $sessions = MentoringSession::query()
                ->whereHas('mentees', function ($q) use ($organization) {
                $q->where('sponsored_by_organization_id', $organization->id);
            })
                ->with(['mentor', 'mentees'])
                ->get();
        }

        return view('organization.sessions.calendar', compact('organization', 'sessions'));
    }

    /**
     * API pour le calendrier (JSON)
     */
    public function events(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (!$organization->isPro()) {
            return response()->json([]);
        }

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
        $organization = $this->getCurrentOrganization();

        // Vérification : au moins un menté doit être parrainé par cette organisation
        $isAuthorized = $session->mentees()->where('sponsored_by_organization_id', $organization->id)->exists();

        if (!$isAuthorized) {
            abort(403, 'Accès non autorisé');
        }

        if ($organization->isPro()) {
            $session->load(['mentor', 'mentees']);
        }

        return view('organization.sessions.show', compact('organization', 'session'));
    }

    private function getStatusColor($status)
    {
        return match ($status) {
                'confirmed' => '#dcfce7', // bg-green-100
                'completed' => '#e0e7ff', // bg-indigo-100
                'cancelled' => '#fee2e2', // bg-red-100
                'pending_payment' => '#fef3c7', // bg-amber-100
                'proposed' => '#f3f4f6', // bg-gray-100
                default => '#f3f4f6',
            };
    }
}