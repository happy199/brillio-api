<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PersonalityTest;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use App\Models\AcademicDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controller pour les analytics dans le dashboard admin
 */
class AnalyticsController extends Controller
{
    /**
     * Liste des spécialisations
     */
    protected array $specializations = [
        'tech' => 'Technologie',
        'business' => 'Business & Management',
        'health' => 'Santé',
        'education' => 'Éducation',
        'arts' => 'Arts & Culture',
        'engineering' => 'Ingénierie',
        'law' => 'Droit',
        'finance' => 'Finance',
        'marketing' => 'Marketing',
        'other' => 'Autre',
    ];

    /**
     * Dashboard analytics principal
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 30);

        // Statistiques principales
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'new_users_week' => User::where('created_at', '>=', Carbon::now()->subWeek())->where('is_admin', false)->count(),
            'total_tests' => PersonalityTest::whereNotNull('personality_type')->count(),
            'test_completion_rate' => $this->calculateTestCompletionRate(),
            'total_conversations' => ChatConversation::count(),
            'total_messages' => ChatMessage::count(),
            'active_mentors' => MentorProfile::where('is_published', true)->count(),
            'total_roadmap_steps' => RoadmapStep::count(),
        ];

        // Distribution des types de personnalité
        $stats['personality_distribution'] = PersonalityTest::whereNotNull('personality_type')
            ->select('personality_type', DB::raw('count(*) as count'))
            ->groupBy('personality_type')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'personality_type')
            ->toArray();

        // Utilisateurs par pays
        $stats['users_by_country'] = User::select('country', DB::raw('count(*) as total'))
            ->where('is_admin', false)
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Mentors par spécialisation
        $stats['mentors_by_specialization'] = MentorProfile::where('is_published', true)
            ->select('specialization', DB::raw('count(*) as total'))
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->get();

        // Documents
        $stats['documents'] = [
            'total' => AcademicDocument::count(),
            'bulletin' => AcademicDocument::where('document_type', 'bulletin')->count(),
            'releve_notes' => AcademicDocument::where('document_type', 'releve_notes')->count(),
            'diplome' => AcademicDocument::where('document_type', 'diplome')->count(),
        ];

        // Inscriptions récentes
        $stats['recent_signups'] = User::where('is_admin', false)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.analytics.index', [
            'stats' => $stats,
            'specializations' => $this->specializations,
        ]);
    }

    /**
     * Calcule le taux de complétion des tests
     */
    private function calculateTestCompletionRate(): float
    {
        $totalJeunes = User::where('user_type', 'jeune')->count();
        if ($totalJeunes === 0) {
            return 0;
        }

        $completedTests = PersonalityTest::whereNotNull('personality_type')
            ->whereHas('user', function ($q) {
                $q->where('user_type', 'jeune');
            })
            ->count();

        return round(($completedTests / $totalJeunes) * 100, 1);
    }

    /**
     * Analytics des tests de personnalité
     */
    public function personality()
    {
        // Distribution des types
        $distribution = PersonalityTest::whereNotNull('personality_type')
            ->selectRaw('personality_type, personality_label, COUNT(*) as count')
            ->groupBy('personality_type', 'personality_label')
            ->orderBy('count', 'desc')
            ->get();

        $total = $distribution->sum('count');

        // Évolution des tests complétés par mois
        $monthlyTests = PersonalityTest::whereNotNull('completed_at')
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->pluck('count', 'month')
            ->toArray();

        // Taux de complétion par type d'utilisateur
        $completionRate = [
            'jeune' => $this->getCompletionRate('jeune'),
            'mentor' => $this->getCompletionRate('mentor'),
        ];

        return view('admin.analytics.personality', compact(
            'distribution',
            'total',
            'monthlyTests',
            'completionRate'
        ));
    }

    /**
     * Analytics du chatbot
     */
    public function chat()
    {
        // Nombre total de messages
        $totalMessages = ChatMessage::count();
        $userMessages = ChatMessage::where('role', 'user')->count();
        $assistantMessages = ChatMessage::where('role', 'assistant')->count();

        // Messages par jour (30 derniers jours)
        $dailyMessages = ChatMessage::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Utilisateurs les plus actifs
        $topUsers = ChatMessage::where('role', 'user')
            ->join('chat_conversations', 'chat_messages.conversation_id', '=', 'chat_conversations.id')
            ->join('users', 'chat_conversations.user_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, users.email, COUNT(chat_messages.id) as message_count')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('message_count', 'desc')
            ->take(10)
            ->get();

        // Longueur moyenne des messages
        $avgLength = ChatMessage::where('role', 'user')
            ->selectRaw('AVG(CHAR_LENGTH(content)) as avg_length')
            ->value('avg_length');

        return view('admin.analytics.chat', compact(
            'totalMessages',
            'userMessages',
            'assistantMessages',
            'dailyMessages',
            'topUsers',
            'avgLength'
        ));
    }

    /**
     * Export des données
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'users');

        switch ($type) {
            case 'users':
                $data = User::all()->toArray();
                break;
            case 'personality':
                $data = PersonalityTest::with('user')->get()->toArray();
                break;
            default:
                return back()->with('error', 'Type d\'export non reconnu');
        }

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$type}_export.json");
    }

    /**
     * Calcule le taux de complétion du test pour un type d'utilisateur
     */
    private function getCompletionRate(string $userType): array
    {
        $total = User::where('user_type', $userType)->count();
        $completed = User::where('user_type', $userType)
            ->whereHas('personalityTest', function ($q) {
                $q->whereNotNull('completed_at');
            })
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
        ];
    }
}
