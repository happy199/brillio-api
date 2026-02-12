<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

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
        ];

        // Registration Trend (Last 6 months)
        $registrationData = $organization->sponsoredUsers() // Use $organization->sponsoredUsers() directly to avoid issues with previous where clauses on $sponsoredUsersQuery
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Prepare data for Chart.js (ensure all months are present even if 0)
        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $chartLabels[] = $date->translatedFormat('F'); // Mois en français
            $chartData[] = $registrationData[$monthKey] ?? 0;
        }

        return view('organization.dashboard', compact('organization', 'stats', 'chartLabels', 'chartData'));
    }
}