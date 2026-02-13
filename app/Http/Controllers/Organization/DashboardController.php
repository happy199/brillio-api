<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display organization dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get the organization for this user
        // For now, we'll find by email match, later we can add organization_user relationship
        $organization = Organization::where('contact_email', $user->email)->firstOrFail();

        // Get basic stats
        $sponsoredUsersQuery = $organization->sponsoredUsers();

        $stats = [
            'total_invited' => $organization->invitations()->count(),
            'total_registered' => $sponsoredUsersQuery->count(),
            'pending_invitations' => $organization->invitations()->where('status', 'pending')->count(),
            'active_users' => $sponsoredUsersQuery->where('last_login_at', '>=', now()->subDays(30))->count(),
            'personality_tests_completed' => $organization->sponsoredUsers()->whereHas('personalityTest', function ($q) {
            $q->whereNotNull('completed_at');
        })->count(),
            'users_with_mentors' => $organization->sponsoredUsers()
            ->whereHas('mentorshipsAsMentee', function ($query) {
            $query->where('status', 'accepted');
        })
            ->count(),
            'mentoring_sessions_count' => $organization->sponsoredUsers()
            ->join('mentoring_session_user', 'users.id', '=', 'mentoring_session_user.user_id')
            ->join('mentoring_sessions', 'mentoring_session_user.mentoring_session_id', '=', 'mentoring_sessions.id')
            ->where('mentoring_sessions.status', 'completed')
            ->count(),
            'documents_count' => $organization->sponsoredUsers()->withCount('academicDocuments')->get()->sum('academic_documents_count'),
            'onboarding_completed_count' => $sponsoredUsersQuery->where('onboarding_completed', true)->count(),
        ];

        // Personality Types Distribution (Top 3)
        $personalityStats = $organization->sponsoredUsers()
            ->join('personality_tests', 'users.id', '=', 'personality_tests.user_id')
            ->where('personality_tests.is_current', true)
            ->whereNotNull('personality_tests.completed_at')
            ->select('personality_tests.personality_type', \DB::raw('count(*) as count'))
            ->groupBy('personality_tests.personality_type')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        $stats['top_personalities'] = $personalityStats;

        // Registration Trend (Last 6 months)
        $registrationData = $organization->sponsoredUsers()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $chartLabels[] = $date->translatedFormat('F');
            $chartData[] = $registrationData[$monthKey] ?? 0;
        }

        // --- NEW ANALYTICS ---

        // 1. Daily Activity (Last 30 days) for Multi-line Chart
        $endDate = now();
        $startDate = now()->subDays(30);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        $dailyActivity = [];
        $dailySignupsData = $organization->sponsoredUsers()
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        $dailyTestsData = $organization->sponsoredUsers()
            ->join('personality_tests', 'users.id', '=', 'personality_tests.user_id')
            ->whereNotNull('personality_tests.completed_at')
            ->whereBetween('personality_tests.completed_at', [$startDate, $endDate])
            ->selectRaw('DATE(personality_tests.completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        $dailySessionsData = $organization->sponsoredUsers()
            ->join('mentoring_session_user', 'users.id', '=', 'mentoring_session_user.user_id')
            ->join('mentoring_sessions', 'mentoring_session_user.mentoring_session_id', '=', 'mentoring_sessions.id')
            ->where('mentoring_sessions.status', 'completed')
            ->whereBetween('mentoring_sessions.scheduled_at', [$startDate, $endDate])
            ->selectRaw('DATE(mentoring_sessions.scheduled_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        // Fill missing dates with 0
        $activityLabels = [];
        $activityData = [
            'signups' => [],
            'tests' => [],
            'sessions' => []
        ];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $activityLabels[] = $date->format('d/m');
            $activityData['signups'][] = $dailySignupsData[$formattedDate] ?? 0;
            $activityData['tests'][] = $dailyTestsData[$formattedDate] ?? 0;
            $activityData['sessions'][] = $dailySessionsData[$formattedDate] ?? 0;
        }

        // 2. Demographics: Top Cities
        $cityStats = $organization->sponsoredUsers()
            ->whereNotNull('city')
            ->select('city', \DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 3. Demographics: Age Distribution
        $users = $organization->sponsoredUsers()->whereNotNull('date_of_birth')->get();
        $ageStats = [
            '18-24' => 0,
            '25-30' => 0,
            '30+' => 0
        ];
        foreach ($users as $user) {
            $age = \Carbon\Carbon::parse($user->date_of_birth)->age;
            if ($age >= 18 && $age <= 24)
                $ageStats['18-24']++;
            elseif ($age >= 25 && $age <= 30)
                $ageStats['25-30']++;
            elseif ($age > 30)
                $ageStats['30+']++;
        }

        // 4. Document Types
        $documentStats = $organization->sponsoredUsers()
            ->join('academic_documents', 'users.id', '=', 'academic_documents.user_id')
            ->select('academic_documents.document_type as type', \DB::raw('count(*) as count'))
            ->groupBy('academic_documents.document_type')
            ->orderByDesc('count')
            ->get();

        return view('organization.dashboard', compact(
            'organization',
            'stats',
            'chartLabels', 'chartData', // Old chart data
            'activityLabels', 'activityData',
            'cityStats',
            'ageStats',
            'documentStats'
        ));
    }
}