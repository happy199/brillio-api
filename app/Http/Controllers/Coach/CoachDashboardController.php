<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\Request;

class CoachDashboardController extends Controller
{
    /**
     * Dashboard du coach
     */
    public function index()
    {
        $user = auth()->user();

        // Statistiques pour le coach
        // Pour l'instant on se base sur les conversations de support humain
        $stats = [
            'pending_support' => ChatConversation::where('needs_human_support', true)
            ->where('human_support_active', false)
            ->count(),
            'active_support' => ChatConversation::where('human_support_active', true)
            ->where('human_support_admin_id', $user->id)
            ->count(),
        ];

        return view('coach.dashboard', compact('stats'));
    }
}