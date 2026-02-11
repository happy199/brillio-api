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

        //Get basic stats
        $stats = [
            'total_invited' => $organization->invitations()->count(),
            'total_registered' => $organization->sponsoredUsers()->count(),
            'pending_invitations' => $organization->invitations()->where('status', 'pending')->count(),
            'active_users' => $organization->sponsoredUsers()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count(),
        ];

        return view('organization.dashboard', compact('organization', 'stats'));
    }
}