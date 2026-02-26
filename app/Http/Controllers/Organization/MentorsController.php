<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MentorsController extends Controller
{
    /**
     * Get current organization
     */

    /**
     * List mentors linked to the organization
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        // Mentors who have sessions with organization's sponsored youths
        $activeMentorsIds = MentoringSession::whereHas('mentees', function ($q) use ($organization) {
            $q->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                ->where('organization_user.organization_id', $organization->id);
        })
            ->pluck('mentor_id')
            ->unique();

        $type = $request->get('type', 'internal'); // Default to internal

        if ($type === 'external') {
            // External mentors: Not linked to organization BUT have sessions with organization's sponsored youths
            $query = User::where('user_type', 'mentor')
                ->whereDoesntHave('organizations', function ($sq) use ($organization) {
                    $sq->where('organizations.id', $organization->id);
                })
                ->whereIn('id', $activeMentorsIds)
                ->with(['mentorProfile']);
        } else {
            // Internal mentors: Linked directly via organization_user
            $query = $organization->mentors()->with(['mentorProfile']);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! $organization->isPro()) {
            $mentors = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        } else {
            $mentors = $query->latest()->paginate(12)->withQueryString();
        }

        return view('organization.mentors.index', compact('organization', 'mentors', 'type'));
    }

    /**
     * Show mentor profile and sessions
     */
    public function show(MentorProfile $mentor)
    {
        $mentorUser = $mentor->user;
        $organization = $this->getCurrentOrganization();

        // Check if mentor is linked to organization or has sessions with its youths
        $isLinked = $organization->mentors()->where('users.id', $mentorUser->id)->exists();
        $hasSessionsWithYouths = MentoringSession::where('mentor_id', $mentorUser->id)
            ->whereHas('mentees', function ($q) use ($organization) {
                $q->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                    ->where('organization_user.organization_id', $organization->id);
            })->exists();

        if (! $isLinked && ! $hasSessionsWithYouths) {
            abort(403, 'Accès non autorisé');
        }

        if (! $organization->isPro()) {
            return view('organization.mentors.show', [
                'organization' => $organization,
                'mentor' => $mentorUser,
                'sessions' => collect(),
                'youthsCount' => 0,
            ]);
        }

        $mentorUser->load(['mentorProfile']);

        $isInternal = $isLinked;

        // Only sessions with organization's youths
        $sessions = MentoringSession::where('mentor_id', $mentorUser->id)
            ->whereHas('mentees', function ($q) use ($organization) {
                $q->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                    ->where('organization_user.organization_id', $organization->id);
            })
            ->with(['mentees' => function ($q) use ($organization) {
                $q->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                    ->where('organization_user.organization_id', $organization->id);
            }])
            ->latest('scheduled_at')
            ->get();

        $youthsCount = $sessions->flatMap->mentees->pluck('id')->unique()->count();

        return view('organization.mentors.show', [
            'organization' => $organization,
            'mentor' => $mentorUser,
            'sessions' => $sessions,
            'youthsCount' => $youthsCount,
            'isInternal' => $isInternal,
        ]);
    }

    /**
     * Export mentor profile to PDF
     */
    public function exportPdf(MentorProfile $mentor)
    {
        $mentorUser = $mentor->user;
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            abort(403, 'Plan Pro requis pour l\'export');
        }

        $mentorUser->load(['mentorProfile']);

        $pdf = Pdf::loadView('organization.mentors.export-pdf', ['mentor' => $mentorUser, 'organization' => $organization]);

        return $pdf->download("profil-mentor-{$mentorUser->id}.pdf");
    }

    /**
     * Export mentor profile to CSV (General Info)
     */
    public function exportCsv(MentorProfile $mentor)
    {
        $mentorUser = $mentor->user;
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            abort(403, 'Plan Pro requis pour l\'export');
        }

        $mentorUser->load(['mentorProfile']);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=profil-mentor-{$mentorUser->id}.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['ID', 'Nom', 'Email', 'Position', 'Entreprise', 'Specialisation', 'Experience', 'Ville', 'Pays', 'LinkedIn'];

        $callback = function () use ($mentorUser, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                $mentorUser->id,
                $mentorUser->name,
                $mentorUser->email,
                $mentorUser->mentorProfile->current_position ?? '',
                $mentorUser->mentorProfile->current_company ?? '',
                $mentorUser->mentorProfile->specialization ?? '',
                $mentorUser->mentorProfile->years_of_experience ?? '',
                $mentorUser->city ?? '',
                $mentorUser->country ?? '',
                $mentorUser->mentorProfile->linkedin_url ?? '',
            ]);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
