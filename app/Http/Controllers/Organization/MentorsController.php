<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
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
        }
        else {
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

        if (!$organization->isPro()) {
            $mentors = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        }
        else {
            $mentors = $query->latest()->paginate(12)->withQueryString();
        }

        return view('organization.mentors.index', compact('organization', 'mentors', 'type'));
    }

    /**
     * Show mentor profile and sessions
     */
    public function show(User $mentor)
    {
        $organization = $this->getCurrentOrganization();

        // Check if mentor is linked to organization or has sessions with its youths
        $isLinked = $organization->mentors()->where('users.id', $mentor->id)->exists();
        $hasSessionsWithYouths = MentoringSession::where('mentor_id', $mentor->id)
            ->whereHas('mentees', function ($q) use ($organization) {
            $q->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                ->where('organization_user.organization_id', $organization->id);
        })->exists();

        if (!$isLinked && !$hasSessionsWithYouths) {
            abort(403, 'Accès non autorisé');
        }

        if (!$organization->isPro()) {
            return view('organization.mentors.show', [
                'organization' => $organization,
                'mentor' => $mentor,
                'sessions' => collect(),
                'youthsCount' => 0,
            ]);
        }

        $mentor->load(['mentorProfile']);

        $isInternal = $isLinked;

        // Only sessions with organization's youths
        $sessions = MentoringSession::where('mentor_id', $mentor->id)
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

        return view('organization.mentors.show', compact('organization', 'mentor', 'sessions', 'youthsCount', 'isInternal'));
    }

    /**
     * Export mentor profile to PDF
     */
    public function exportPdf(User $mentor)
    {
        $organization = $this->getCurrentOrganization();

        if (!$organization->isPro()) {
            abort(403, 'Plan Pro requis pour l\'export');
        }

        $mentor->load(['mentorProfile']);

        $pdf = Pdf::loadView('organization.mentors.export-pdf', compact('mentor', 'organization'));

        return $pdf->download("profil-mentor-{$mentor->id}.pdf");
    }

    /**
     * Export mentor profile to CSV (General Info)
     */
    public function exportCsv(User $mentor)
    {
        $organization = $this->getCurrentOrganization();

        if (!$organization->isPro()) {
            abort(403, 'Plan Pro requis pour l\'export');
        }

        $mentor->load(['mentorProfile']);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=profil-mentor-{$mentor->id}.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['ID', 'Nom', 'Email', 'Position', 'Entreprise', 'Specialisation', 'Experience', 'Ville', 'Pays', 'LinkedIn'];

        $callback = function () use ($mentor, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, [
                $mentor->id,
                $mentor->name,
                $mentor->email,
                $mentor->mentorProfile->current_position ?? '',
                $mentor->mentorProfile->current_company ?? '',
                $mentor->mentorProfile->specialization ?? '',
                $mentor->mentorProfile->years_of_experience ?? '',
                $mentor->city ?? '',
                $mentor->country ?? '',
                $mentor->mentorProfile->linkedin_url ?? '',
            ]);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}