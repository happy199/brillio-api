<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorshipController extends Controller
{
    /**
     * Liste des mentorats des jeunes parrainés
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            $mentorships = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        } else {
            $query = Mentorship::query()
                ->whereIn('mentee_id', function ($q) use ($organization) {
                    $q->select('id')->from('users')->where('sponsored_by_organization_id', $organization->id);
                })
                ->with(['mentor', 'mentee']);

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $mentorships = $query->latest()->paginate(12)->withQueryString();
        }

        return view('organization.mentorships.index', compact('organization', 'mentorships'));
    }

    protected $notificationService;

    public function __construct(\App\Services\MentorshipNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Terminer une relation de mentorat
     */
    public function terminate(Request $request, Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification : le menté doit être parrainé par cette organisation
        if ($mentorship->mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        if ($mentorship->status === 'disconnected') {
            return back()->with('error', 'Cette relation est déjà terminée.');
        }

        $request->validate([
            'diction_reason' => 'required|string|max:1000',
        ]);

        $mentorship->update([
            'status' => 'disconnected',
            'diction_reason' => $request->diction_reason,
        ]);

        // Notification générale (envoie au jeune, au mentor, et confirmation à l'org)
        // Note: l'admin de l'org est l'acteur ici
        $actor = $organization->users()->wherePivot('role', 'admin')->first() ?? Auth::user();
        $this->notificationService->sendMentorshipTerminated($mentorship, $actor, $request->diction_reason);

        return back()->with('success', 'La relation de mentorat a été terminée avec succès.');
    }

    /**
     * Détail d'un mentorat
     */
    public function show(Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification : le menté doit être parrainé par cette organisation
        if ($mentorship->mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        if (! $organization->isPro()) {
            return view('organization.mentorships.show', [
                'organization' => $organization,
                'mentorship' => $mentorship,
                // Pass empty collections/nulls if view expects them, though we'll blur it out
                'sessions' => collect(),
            ]);
        }

        $mentorship->load(['mentor', 'mentee']);

        return view('organization.mentorships.show', compact('organization', 'mentorship'));
    }
}
