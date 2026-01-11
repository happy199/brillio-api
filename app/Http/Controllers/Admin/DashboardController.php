<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicDocument;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\MentorProfile;
use App\Models\PersonalityTest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Controller pour le dashboard principal admin
 */
class DashboardController extends Controller
{
    /**
     * Affiche le dashboard avec les statistiques
     */
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_jeunes' => User::where('user_type', 'jeune')->count(),
            'total_mentors' => User::where('user_type', 'mentor')->count(),
            'published_mentors' => MentorProfile::where('is_published', true)->count(),
            'pending_mentors' => MentorProfile::where('is_published', false)->count(),
            'total_personality_tests' => PersonalityTest::whereNotNull('completed_at')->count(),
            'total_chat_messages' => ChatMessage::count(),
            'total_conversations' => ChatConversation::count(),
            'total_documents' => AcademicDocument::count(),
        ];

        // Utilisateurs récents (7 derniers jours)
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Distribution des types de personnalité
        $personalityDistribution = PersonalityTest::whereNotNull('personality_type')
            ->selectRaw('personality_type, COUNT(*) as count')
            ->groupBy('personality_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'personality_type')
            ->toArray();

        // Mentors en attente de validation
        $pendingMentors = MentorProfile::with('user')
            ->where('is_published', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Évolution des inscriptions (30 derniers jours)
        $registrationTrend = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentUsers',
            'personalityDistribution',
            'pendingMentors',
            'registrationTrend'
        ));
    }
}
