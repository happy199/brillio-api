<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Display the export configuration page.
     */
    public function index()
    {
        $organization = $this->getCurrentOrganization();

        return view('organization.exports.index', compact('organization'));
    }


    /**
     * Generate and download the requested CSV report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:general,jeunes,sessions,mentors',
            'format' => 'required|in:csv,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $organization = $this->getCurrentOrganization();
        $organizationId = $organization->id;
        $type = $request->type;
        $format = $request->format;

        // Use filled() to ensure we only get a value if it's not empty
        $startDate = $request->filled('start_date') ? $request->start_date.' 00:00:00' : null;
        $endDate = $request->filled('end_date') ? $request->end_date.' 23:59:59' : null;

        $fileName = "export_{$type}_".now()->format('Y-m-d_His');
        $extension = $format === 'csv' ? '.csv' : '.pdf';
        $fullFileName = $fileName.$extension;

        if ($format === 'csv') {
            switch ($type) {
                case 'general':
                    return $this->exportGeneralCsv($organizationId, $startDate, $endDate, $fullFileName);
                case 'jeunes':
                    return $this->exportJeunesCsv($organizationId, $fullFileName);
                case 'sessions':
                    return $this->exportSessionsCsv($organizationId, $startDate, $endDate, $fullFileName);
                case 'mentors':
                    return $this->exportMentorsCsv($organization, $fullFileName);
            }
        } else {
            switch ($type) {
                case 'general':
                    return $this->exportGeneralPdf($organization, $startDate, $endDate, $fullFileName);
                case 'jeunes':
                    return $this->exportJeunesPdf($organization, $fullFileName);
                case 'sessions':
                    return $this->exportSessionsPdf($organization, $startDate, $endDate, $fullFileName);
                case 'mentors':
                    return $this->exportMentorsPdf($organization, $fullFileName);
            }
        }

        return back()->with('error', 'Configuration d\'export invalide.');
    }

    private function exportGeneralCsv($organizationId, $startDate, $endDate, $fileName)
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($organizationId, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Métrique', 'Valeur', 'Description']);

            $query = User::where('sponsored_by_organization_id', $organizationId);

            $totalInvited = \App\Models\OrganizationInvitation::where('organization_id', $organizationId)
                ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                ->count();

            $totalRegistered = (clone $query)
                ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
                ->count();

            $activeUsers = (clone $query)
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();

            $sessionsCount = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
                $q->where('sponsored_by_organization_id', $organizationId);
            }
            )
                ->where('status', 'completed')
                ->when($startDate, fn ($q) => $q->where('scheduled_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('scheduled_at', '<=', $endDate))
                ->count();

            fputcsv($file, ['Invitations envoyées', $totalInvited, 'Nombre total d\'invitations créées']);
            fputcsv($file, ['Jeunes inscrits', $totalRegistered, 'Nombre de jeunes ayant rejoint la plateforme']);
            fputcsv($file, ['Utilisateurs actifs', $activeUsers, 'Utilisateurs connectés au cours des 30 derniers jours']);
            fputcsv($file, ['Sessions réalisées', $sessionsCount, 'Nombre total de sessions de mentorat terminées']);

            // Mentor Metrics
            $organization = \App\Models\Organization::find($organizationId);
            $internalMentorsIds = $organization->mentors()->pluck('users.id');

            $activeMentorsIds = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
                $q->where('sponsored_by_organization_id', $organizationId);
            })
                ->pluck('mentor_id')
                ->unique();

            $externalMentorsCount = User::where('user_type', 'mentor')
                ->whereNotIn('id', $internalMentorsIds)
                ->whereIn('id', $activeMentorsIds)
                ->count();

            $totalMentorsCount = $internalMentorsIds->count() + $externalMentorsCount;

            fputcsv($file, ['Total Mentors', $totalMentorsCount, 'Nombre total de mentors (internes et externes)']);
            fputcsv($file, ['Mentors Internes', $internalMentorsIds->count(), 'Mentors liés directement à votre organisation']);
            fputcsv($file, ['Mentors Externes', $externalMentorsCount, 'Mentors externes accompagnant vos bénéficiaires']);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportJeunesCsv($organizationId, $fileName)
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($organizationId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ID', 'Nom', 'Email', 'Date d\'inscription', 'Ville', 'Complétion Profil (%)', 'Mentor Assigné', 'Statut Mentorat']);

            $users = User::where('sponsored_by_organization_id', $organizationId)
                ->with(['jeuneProfile', 'mentorshipsAsMentee.mentor'])
                ->get();

            foreach ($users as $user) {
                $mentorship = $user->mentorshipsAsMentee->first();
                $mentorName = $mentorship ? $mentorship->mentor->name : 'Non assigné';
                $mentorshipStatus = $mentorship ? $mentorship->status : '-';

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('d/m/Y'),
                    $user->city ?? '-',
                    $user->profile_completion_percentage,
                    $mentorName,
                    $mentorshipStatus,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportSessionsCsv($organizationId, $startDate, $endDate, $fileName)
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($organizationId, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ID Séance', 'Date & Heure', 'Jeune', 'Mentor', 'Statut', 'Progrès', 'Obstacles', 'Objectifs SMART']);

            $sessions = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
                $q->where('sponsored_by_organization_id', $organizationId);
            }
            )
                ->with(['mentees', 'mentor'])
                ->when($startDate, fn ($q) => $q->where('scheduled_at', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->where('scheduled_at', '<=', $endDate))
                ->orderBy('scheduled_at', 'desc')
                ->get();

            foreach ($sessions as $session) {
                $menteeName = $session->mentees->first()?->name ?? 'N/A';
                fputcsv($file, [
                    $session->id,
                    $session->scheduled_at->format('d/m/Y H:i'),
                    $menteeName,
                    $session->mentor->name,
                    $session->translated_status,
                    $session->report_content['progress'] ?? '-',
                    $session->report_content['obstacles'] ?? '-',
                    $session->report_content['smart_goals'] ?? '-',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportGeneralPdf($organization, $startDate, $endDate, $fileName)
    {
        $organizationId = $organization->id;
        $totalInvited = \App\Models\OrganizationInvitation::where('organization_id', $organizationId)
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->count();

        $totalRegistered = User::where('sponsored_by_organization_id', $organizationId)
            ->when($startDate, fn ($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('created_at', '<=', $endDate))
            ->count();

        $activeUsers = User::where('sponsored_by_organization_id', $organizationId)
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        $sessionsCount = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
            $q->where('sponsored_by_organization_id', $organizationId);
        }
        )
            ->where('status', 'completed')
            ->when($startDate, fn ($q) => $q->where('scheduled_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('scheduled_at', '<=', $endDate))
            ->count();

        // Mentor Metrics
        $internalMentorsIds = $organization->mentors()->pluck('users.id');
        $activeMentorsIds = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
            $q->where('sponsored_by_organization_id', $organizationId);
        })
            ->pluck('mentor_id')
            ->unique();

        $externalMentorsCount = User::where('user_type', 'mentor')
            ->whereNotIn('id', $internalMentorsIds)
            ->whereIn('id', $activeMentorsIds)
            ->count();

        // Demographics: Top Cities
        $cityStats = User::where('sponsored_by_organization_id', $organizationId)
            ->whereNotNull('city')
            ->select('city', DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Demographics: Age Distribution
        $usersForAge = User::where('sponsored_by_organization_id', $organizationId)
            ->whereNotNull('date_of_birth')
            ->get();
        $ageStats = ['18-24' => 0, '25-30' => 0, '30+' => 0];
        foreach ($usersForAge as $user) {
            $age = \Carbon\Carbon::parse($user->date_of_birth)->age;
            if ($age >= 18 && $age <= 24) {
                $ageStats['18-24']++;
            } elseif ($age >= 25 && $age <= 30) {
                $ageStats['25-30']++;
            } elseif ($age > 30) {
                $ageStats['30+']++;
            }
        }

        // Document Types
        $documentStats = User::where('sponsored_by_organization_id', $organizationId)
            ->join('academic_documents', 'users.id', '=', 'academic_documents.user_id')
            ->select('academic_documents.document_type as type', DB::raw('count(*) as count'))
            ->groupBy('academic_documents.document_type')
            ->orderByDesc('count')
            ->get();

        $data = [
            'totalInvited' => $totalInvited,
            'totalRegistered' => $totalRegistered,
            'activeUsers' => $activeUsers,
            'sessionsCount' => $sessionsCount,
            'totalMentors' => $internalMentorsIds->count() + $externalMentorsCount,
            'internalMentors' => $internalMentorsIds->count(),
            'externalMentors' => $externalMentorsCount,
            'cityStats' => $cityStats,
            'ageStats' => $ageStats,
            'documentStats' => $documentStats,
        ];

        $pdf = Pdf::loadView('reports.general', [
            'organization' => $organization,
            'title' => 'Statistiques Générales',
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->download($fileName);
    }

    private function exportJeunesPdf($organization, $fileName)
    {
        $users = User::where('sponsored_by_organization_id', $organization->id)
            ->with(['jeuneProfile', 'mentorshipsAsMentee.mentor'])
            ->get();

        $pdf = Pdf::loadView('reports.mentees', [
            'organization' => $organization,
            'title' => 'Liste des Jeunes Parrainés',
            'users' => $users,
        ]);

        return $pdf->download($fileName);
    }

    private function exportSessionsPdf($organization, $startDate, $endDate, $fileName)
    {
        $organizationId = $organization->id;
        $sessions = MentoringSession::whereHas('mentees', function ($q) use ($organizationId) {
            $q->where('sponsored_by_organization_id', $organizationId);
        }
        )
            ->with(['mentees', 'mentor'])
            ->when($startDate, fn ($q) => $q->where('scheduled_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('scheduled_at', '<=', $endDate))
            ->orderBy('scheduled_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.sessions', [
            'organization' => $organization,
            'title' => 'Historique des Séances',
            'sessions' => $sessions,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->download($fileName);
    }

    private function exportMentorsCsv($organization, $fileName)
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($organization) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Nom', 'Email', 'Type', 'Ville', 'Entreprise', 'Titre']);

            $internalMentorsIds = $organization->mentors()->pluck('users.id');
            $activeMentorsIds = MentoringSession::whereHas('mentees', function ($q) use ($organization) {
                $q->where('sponsored_by_organization_id', $organization->id);
            })->pluck('mentor_id')->unique();

            $mentors = User::where('user_type', 'mentor')
                ->where(function ($query) use ($internalMentorsIds, $activeMentorsIds) {
                    $query->whereIn('id', $internalMentorsIds)
                        ->orWhereIn('id', $activeMentorsIds);
                })
                ->with(['mentorProfile'])
                ->get();

            foreach ($mentors as $mentor) {
                $type = $internalMentorsIds->contains($mentor->id) ? 'Interne' : 'Externe';
                fputcsv($file, [
                    $mentor->name,
                    $mentor->email,
                    $type,
                    $mentor->city ?? '-',
                    $mentor->mentorProfile->company ?? '-',
                    $mentor->mentorProfile->job_title ?? '-',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportMentorsPdf($organization, $fileName)
    {
        $internalMentorsIds = $organization->mentors()->pluck('users.id');
        $activeMentorsIds = MentoringSession::whereHas('mentees', function ($q) use ($organization) {
            $q->where('sponsored_by_organization_id', $organization->id);
        })->pluck('mentor_id')->unique();

        $mentors = User::where('user_type', 'mentor')
            ->where(function ($query) use ($internalMentorsIds, $activeMentorsIds) {
                $query->whereIn('id', $internalMentorsIds)
                    ->orWhereIn('id', $activeMentorsIds);
            })
            ->with(['mentorProfile'])
            ->get();

        foreach ($mentors as $mentor) {
            $mentor->is_internal = $internalMentorsIds->contains($mentor->id);
        }

        $pdf = Pdf::loadView('reports.mentors', [
            'organization' => $organization,
            'title' => 'Liste des Mentors',
            'mentors' => $mentors,
        ]);

        return $pdf->download($fileName);
    }
}