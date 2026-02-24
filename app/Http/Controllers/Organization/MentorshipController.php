<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;

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
