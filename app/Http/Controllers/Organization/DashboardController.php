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
    public function index(Request $request)
    {
        $user = auth()->user();
        $organization = Organization::where('contact_email', $user->email)->firstOrFail();

        // --- FILTERING LOGIC ---
        $period = $request->get('period', '30_days');
        $startDate = now()->subDays(30)->startOfDay();
        $endDate = now()->endOfDay();

        switch ($period) {
            case '7_days':
                $startDate = now()->subDays(7);
                break;
            case 'this_month':
                $startDate = now()->startOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                break;
            case 'custom':
                if ($request->has('start_date')) {
                    $startDate = \Carbon\Carbon::parse($request->get('start_date'))->startOfDay();
                }
                if ($request->has('end_date')) {
                    $endDate = \Carbon\Carbon::parse($request->get('end_date'))->endOfDay();
                }
                break;
            case '30_days':
            default:
                $startDate = now()->subDays(30);
                break;
        }

        // Get basic stats (Global for organization)
        $stats = [
            'total_invited' => $organization->invitations()->count(),
            'total_registered' => $organization->sponsoredUsers()->count(),
            'pending_invitations' => $organization->invitations()->where('status', 'pending')->count(),
            'active_users' => $organization->sponsoredUsers()->where('last_login_at', '>=', now()->subDays(30))->count(),
            'personality_tests_completed' => $organization->sponsoredUsers()->whereHas('personalityTest', function ($q) {
            $q->whereNotNull('completed_at');
        })->count(),
            'users_with_mentors' => $organization->sponsoredUsers()
            ->whereHas('mentorshipsAsMentee', function ($query) {
            $query->where('status', 'accepted');
        })->count(),
            'mentoring_sessions_count' => $organization->sponsoredUsers()
            ->join('mentoring_session_user', 'users.id', '=', 'mentoring_session_user.user_id')
            ->join('mentoring_sessions', 'mentoring_session_user.mentoring_session_id', '=', 'mentoring_sessions.id')
            ->where('mentoring_sessions.status', 'completed')
            ->count(),
            'documents_count' => $organization->sponsoredUsers()->withCount('academicDocuments')->get()->sum('academic_documents_count'),
        ];

        // Onboarding completion (Global)
        $allSponsoredUsers = $organization->sponsoredUsers()->with(['jeuneProfile', 'personalityTest'])->get();
        $stats['onboarding_completed_count'] = $allSponsoredUsers->filter(function ($u) {
            return $u->profile_completion_percentage === 100;
        })->count();

        $isPro = $organization->isPro();

        // Default empty data
        $personalityStats = [];
        $activityLabels = [];
        $activityData = ['signups' => [], 'tests' => [], 'sessions' => [], 'connections' => []];
        $cityStats = [];
        $ageStats = ['18-24' => 0, '25-30' => 0, '30+' => 0];
        $documentStats = [];

        // Registration Trend (Teaser/Global)
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

        // --- FILTERED ANALYTICS (PRO FEATURES) ---
        if ($isPro) {
            // Personality Types (Filtered)
            $personalityStats = $organization->sponsoredUsers()
                ->join('personality_tests', 'users.id', '=', 'personality_tests.user_id')
                ->where('personality_tests.is_current', true)
                ->whereNotNull('personality_tests.completed_at')
                ->whereBetween('personality_tests.completed_at', [$startDate, $endDate])
                ->select('personality_tests.personality_type', DB::raw('count(*) as count'))
                ->groupBy('personality_tests.personality_type')
                ->orderByDesc('count')
                ->limit(3)
                ->get();

            $stats['top_personalities'] = $personalityStats;

            // Activity History for Multi-line Chart
            $carbonPeriod = \Carbon\CarbonPeriod::create($startDate, $endDate);

            // Fetch grouped data
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

            $dailyConnectionsData = \App\Models\UserLogin::where('organization_id', $organization->id)
                ->whereBetween('login_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('login_date, count(*) as count')
                ->groupBy('login_date')
                ->pluck('count', 'login_date')->toArray();

            // Fill missing dates with 0
            foreach ($carbonPeriod as $date) {
                $formattedDate = $date->format('Y-m-d');
                $activityLabels[] = $date->format('d/m');
                $activityData['signups'][] = $dailySignupsData[$formattedDate] ?? 0;
                $activityData['tests'][] = $dailyTestsData[$formattedDate] ?? 0;
                $activityData['sessions'][] = $dailySessionsData[$formattedDate] ?? 0;
                $activityData['connections'][] = $dailyConnectionsData[$formattedDate] ?? 0;
            }

            // Demographics: Top Cities (Filtered by registration date)
            $cityStats = $organization->sponsoredUsers()
                ->whereNotNull('city')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('city', DB::raw('count(*) as count'))
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Demographics: Age Distribution (Static but for sponsored users)
            $users = $organization->sponsoredUsers()->whereNotNull('date_of_birth')->get();
            $ageStats = ['18-24' => 0, '25-30' => 0, '30+' => 0];
            foreach ($users as $user) {
                $age = \Carbon\Carbon::parse($user->date_of_birth)->age;
                if ($age >= 18 && $age <= 24)
                    $ageStats['18-24']++;
                elseif ($age >= 25 && $age <= 30)
                    $ageStats['25-30']++;
                elseif ($age > 30)
                    $ageStats['30+']++;
            }

            // Document Types (Filtered)
            $documentStats = $organization->sponsoredUsers()
                ->join('academic_documents', 'users.id', '=', 'academic_documents.user_id')
                ->whereBetween('academic_documents.created_at', [$startDate, $endDate])
                ->select('academic_documents.document_type as type', DB::raw('count(*) as count'))
                ->groupBy('academic_documents.document_type')
                ->orderByDesc('count')
                ->get();
        }

        return view('organization.dashboard', compact(
            'organization', 'stats',
            'chartLabels', 'chartData',
            'activityLabels', 'activityData',
            'cityStats', 'ageStats', 'documentStats',
            'isPro', 'period', 'startDate', 'endDate'
        ));
    }}