<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicDocument;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\MentorProfile;
use App\Models\PersonalityTest;
use App\Models\RoadmapStep;
use App\Models\User;
use App\Services\PersonalityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Récupère les dates de filtre
     */
    private function getDateRange(Request $request): array
    {
        $preset = $request->get('preset', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Si des dates personnalisées sont fournies
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
                'preset' => 'custom',
            ];
        }

        // Préréglages
        $end = Carbon::now()->endOfDay();
        switch ($preset) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                break;
            case '3days':
                $start = Carbon::now()->subDays(3)->startOfDay();
                break;
            case 'week':
                $start = Carbon::now()->subWeek()->startOfDay();
                break;
            case 'month':
                $start = Carbon::now()->subMonth()->startOfDay();
                break;
            case 'quarter':
                $start = Carbon::now()->subMonths(3)->startOfDay();
                break;
            case 'year':
                $start = Carbon::now()->subYear()->startOfDay();
                break;
            case 'all':
                $start = Carbon::create(2020, 1, 1);
                break;
            default:
                $start = Carbon::now()->subMonth()->startOfDay();
                $preset = 'month';
        }

        return [
            'start' => $start,
            'end' => $end,
            'preset' => $preset,
        ];
    }

    /**
     * Dashboard analytics principal
     */
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Statistiques principales (période sélectionnée)
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'new_users_period' => User::where('is_admin', false)
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'total_tests' => PersonalityTest::whereNotNull('personality_type')->count(),
            'tests_period' => PersonalityTest::whereNotNull('personality_type')
                ->whereBetween('completed_at', [$start, $end])
                ->count(),
            'test_completion_rate' => $this->calculateTestCompletionRate(),
            'total_conversations' => ChatConversation::count(),
            'conversations_period' => ChatConversation::whereBetween('created_at', [$start, $end])->count(),
            'total_messages' => ChatMessage::count(),
            'messages_period' => ChatMessage::whereBetween('created_at', [$start, $end])->count(),
            'active_mentors' => MentorProfile::where('is_published', true)->count(),
            'total_roadmap_steps' => RoadmapStep::count(),
        ];

        // Distribution des types de personnalité (période sélectionnée)
        $stats['personality_distribution'] = PersonalityTest::whereNotNull('personality_type')
            ->whereBetween('completed_at', [$start, $end])
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
            ->whereBetween('created_at', [$start, $end])
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

        // Documents (période sélectionnée)
        $stats['documents'] = [
            'total' => AcademicDocument::count(),
            'period' => AcademicDocument::whereBetween('created_at', [$start, $end])->count(),
            'bulletin' => AcademicDocument::where('document_type', 'bulletin')
                ->whereBetween('created_at', [$start, $end])->count(),
            'releve_notes' => AcademicDocument::where('document_type', 'releve_notes')
                ->whereBetween('created_at', [$start, $end])->count(),
            'diplome' => AcademicDocument::where('document_type', 'diplome')
                ->whereBetween('created_at', [$start, $end])->count(),
        ];

        // Inscriptions récentes (dans la période)
        $stats['recent_signups'] = User::where('is_admin', false)
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(10)
            ->get();

        // Graphique d'évolution quotidienne
        $stats['daily_signups'] = $this->getDailyData(User::where('is_admin', false), $start, $end);
        $stats['daily_tests'] = $this->getDailyData(
            PersonalityTest::whereNotNull('completed_at'),
            $start,
            $end,
            'completed_at'
        );
        $stats['daily_messages'] = $this->getDailyData(ChatMessage::query(), $start, $end);

        // --- NEW: Master Youth Stats ---
        $stats['youth_engagement'] = $this->getYouthEngagementStats($start, $end);

        // Récupération globale des situations/intérêts pour les filtres
        $allSituations = [
            'college' => 'Collège',
            'lycee' => 'Lycée',
            'etudiant' => 'Université',
            'recherche_emploi' => 'En recherche',
            'emploi' => 'En poste',
            'entrepreneur' => 'Entrepreneur',
            'autre' => 'Autre',
        ];

        $allInterests = [
            'tech' => 'Technologie',
            'design' => 'Design',
            'business' => 'Business',
            'marketing' => 'Marketing',
            'communication' => 'Communication',
            'science' => 'Sciences',
            'arts' => 'Arts',
            'health' => 'Santé',
            'law' => 'Droit',
            'finance' => 'Finance',
            'education' => 'Education',
        ];

        // NEW Filters for Smart Sourcing++
        $allCountries = User::whereNotNull('country')->distinct()->pluck('country')->sort()->values();
        $allGoals = [
            'orientation' => 'Orientation scolaire',
            'personnalite' => 'Test de personnalité',
            'mentor' => 'Trouver un mentor',
            'ia' => 'Conseiller IA',
            'documents' => 'Gestion de documents',
        ];
        $allChannels = [
            'social_media' => 'Réseaux Sociaux',
            'friend' => 'Ami / Recommandation',
            'school' => 'École / Université',
            'search' => 'Recherche Google',
            'event' => 'Événement',
            'other' => 'Autre',
        ];
        $allPersonalities = PersonalityTest::whereNotNull('personality_type')->distinct()->pluck('personality_type')->sort()->values();

        return view('admin.analytics.index', [
            'stats' => $stats,
            'specializations' => $this->specializations,
            'dateRange' => $dateRange,
            'allSituations' => $allSituations,
            'allInterests' => $allInterests,
            'allCountries' => $allCountries,
            'allGoals' => $allGoals,
            'allChannels' => $allChannels,
            'allPersonalities' => $allPersonalities,
            'filters' => [
                'situation' => (array) $request->get('situation', []),
                'interest' => (array) $request->get('interest', []),
                'country' => (array) $request->get('country', []),
                'goal' => (array) $request->get('goal', []),
                'channel' => (array) $request->get('channel', []),
                'tuition' => (array) $request->get('tuition', []),
                'salary' => (array) $request->get('salary', []),
            ],
            'personalityLabels' => PersonalityService::TYPE_DESCRIPTIONS,
        ]);
    }

    /**
     * Calcule les statistiques d'onboarding et d'engagement exhaustives
     */
    private function getYouthEngagementStats(Carbon $start, Carbon $end): array
    {
        $youths = User::where('user_type', 'jeune')
            ->whereBetween('created_at', [$start, $end])
            ->with(['detailedProfiles' => function ($q) {
                $q->latest();
            }])
            ->get();

        $stats = [
            'situations' => [],
            'sources' => [],
            'goals' => [],
            'interests' => [],
            'tuition_ranges' => [
                'under_200' => 0,
                '200_500' => 0,
                '500_1m' => 0,
                '1m_2m' => 0,
                'over_2m' => 0,
                'non_renseigne' => 0,
            ],
            'target_salary_ranges' => [
                'under_50' => 0,
                '50_100' => 0,
                '100_250' => 0,
                '250_500' => 0,
                '500_1m' => 0,
                '1m_3m' => 0,
                'over_3m' => 0,
                'non_renseigne' => 0,
            ],
            'actual_salary_ranges' => [
                'under_50' => 0,
                '50_100' => 0,
                '100_250' => 0,
                '250_500' => 0,
                '500_1m' => 0,
                '1m_3m' => 0,
                'over_3m' => 0,
                'non_renseigne' => 0,
            ],
            'mentorship_intent_rate' => 0,
        ];

        if ($youths->isEmpty()) {
            return $stats;
        }

        $interestCount = [];
        $mentorGoalCount = 0;

        foreach ($youths as $user) {
            $onboarding = $user->onboarding_data ?? [];
            $latestDetailed = $user->detailedProfiles->first();

            // On unifie la donnée : Le profil détaillé (modale profilage) est prioritaire sur l'onboarding initial
            $data = $latestDetailed ? array_merge($onboarding, $latestDetailed->data ?? []) : $onboarding;

            // Situation
            $sit = $latestDetailed->status ?? ($onboarding['current_situation'] ?? 'non_renseigne');
            $stats['situations'][$sit] = ($stats['situations'][$sit] ?? 0) + 1;

            // Source
            $src = $onboarding['how_found_us'] ?? 'non_renseigne';
            $stats['sources'][$src] = ($stats['sources'][$src] ?? 0) + 1;

            // Tuition Mapping
            $rawTuition = $data['tuition_range'] ?? 'non_renseigne';
            $tuitionMap = [
                '-200000' => 'under_200',
                '200000-500000' => '200_500',
                '500000-1000000' => '500_1m',
                '1000000-2000000' => '1m_2m',
                '+2000000' => 'over_2m',
                'non_renseigne' => 'non_renseigne',
            ];
            $tuitionKey = $tuitionMap[$rawTuition] ?? 'non_renseigne';
            $stats['tuition_ranges'][$tuitionKey] = ($stats['tuition_ranges'][$tuitionKey] ?? 0) + 1;

            // Salary Mapping (Target vs Actual)
            $rawSalary = $data['salary_range'] ?? 'non_renseigne';
            $salaryMap = [
                '-50000' => 'under_50',
                '50000-100000' => '50_100',
                '100000-250000' => '100_250',
                '250000-500000' => '250_500',
                '500000-1000000' => '500_1m',
                '1000000-3000000' => '1m_3m',
                '+3000000' => 'over_3m',
                'non_renseigne' => 'non_renseigne',
            ];
            $salaryKey = $salaryMap[$rawSalary] ?? 'non_renseigne';

            if (in_array($sit, ['emploi', 'entrepreneur'])) {
                $stats['actual_salary_ranges'][$salaryKey] = ($stats['actual_salary_ranges'][$salaryKey] ?? 0) + 1;
            } elseif ($sit === 'recherche_emploi') {
                $stats['target_salary_ranges'][$salaryKey] = ($stats['target_salary_ranges'][$salaryKey] ?? 0) + 1;
            }

            // Goals
            $goals = $data['goals'] ?? [];
            foreach ($goals as $goal) {
                $stats['goals'][$goal] = ($stats['goals'][$goal] ?? 0) + 1;
                if ($goal === 'mentor') {
                    $mentorGoalCount++;
                }
            }

            // Interests
            $interests = $data['interests'] ?? [];
            foreach ($interests as $interest) {
                $interestCount[$interest] = ($interestCount[$interest] ?? 0) + 1;
            }
        }

        arsort($interestCount);
        $stats['interests'] = array_slice($interestCount, 0, 10);
        $stats['mentorship_intent_rate'] = round(($mentorGoalCount / $youths->count()) * 100, 1);

        return $stats;
    }

    /**
     * Récupère les données quotidiennes pour un graphique
     */
    private function getDailyData($query, Carbon $start, Carbon $end, string $dateColumn = 'created_at'): array
    {
        $data = $query->clone()
            ->selectRaw("DATE($dateColumn) as date, COUNT(*) as count")
            ->whereBetween($dateColumn, [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Remplir les jours manquants
        $result = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $result[$dateStr] = $data[$dateStr] ?? 0;
            $current->addDay();
        }

        return $result;
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
    public function personality(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Distribution des types
        $distribution = PersonalityTest::whereNotNull('personality_type')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw('personality_type, personality_label, COUNT(*) as count')
            ->groupBy('personality_type', 'personality_label')
            ->orderBy('count', 'desc')
            ->get();

        $total = $distribution->sum('count');

        // Évolution des tests complétés
        $dailyTests = $this->getDailyData(
            PersonalityTest::whereNotNull('completed_at'),
            $start,
            $end,
            'completed_at'
        );

        // Taux de complétion par type d'utilisateur
        $completionRate = [
            'jeune' => $this->getCompletionRate('jeune'),
            'mentor' => $this->getCompletionRate('mentor'),
        ];

        return view('admin.analytics.personality', compact(
            'distribution',
            'total',
            'dailyTests',
            'completionRate',
            'dateRange'
        ));
    }

    /**
     * Analytics du chatbot
     */
    public function chat(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Nombre total de messages (période)
        $totalMessages = ChatMessage::whereBetween('created_at', [$start, $end])->count();
        $userMessages = ChatMessage::where('role', 'user')
            ->whereBetween('created_at', [$start, $end])->count();
        $assistantMessages = ChatMessage::where('role', 'assistant')
            ->whereBetween('created_at', [$start, $end])->count();

        // Messages par jour
        $dailyMessages = $this->getDailyData(ChatMessage::query(), $start, $end);

        // Utilisateurs les plus actifs
        $topUsers = ChatMessage::where('role', 'user')
            ->whereBetween('chat_messages.created_at', [$start, $end])
            ->join('chat_conversations', 'chat_messages.conversation_id', '=', 'chat_conversations.id')
            ->join('users', 'chat_conversations.user_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, users.email, COUNT(chat_messages.id) as message_count')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('message_count', 'desc')
            ->take(10)
            ->get();

        // Longueur moyenne des messages
        $avgLength = ChatMessage::where('role', 'user')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('AVG(CHAR_LENGTH(content)) as avg_length')
            ->value('avg_length');

        // Sessions avec conseiller humain
        $humanSessions = ChatConversation::where('needs_human_support', true)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return view('admin.analytics.chat', compact(
            'totalMessages',
            'userMessages',
            'assistantMessages',
            'dailyMessages',
            'topUsers',
            'avgLength',
            'humanSessions',
            'dateRange'
        ));
    }

    /**
     * Export des données en PDF
     */
    public function exportPdf(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $type = $request->get('type', 'general');

        $data = [
            'dateRange' => $dateRange,
            'generatedAt' => now(),
            'type' => $type,
        ];

        switch ($type) {
            case 'general':
                $data['stats'] = $this->getGeneralStats($start, $end);
                $view = 'admin.exports.analytics-general';
                $filename = 'brillio-analytics-general';
                break;

            case 'personality':
                $data['distribution'] = PersonalityTest::whereNotNull('personality_type')
                    ->whereBetween('completed_at', [$start, $end])
                    ->selectRaw('personality_type, personality_label, COUNT(*) as count')
                    ->groupBy('personality_type', 'personality_label')
                    ->orderBy('count', 'desc')
                    ->get();
                $data['total'] = $data['distribution']->sum('count');
                $view = 'admin.exports.analytics-personality';
                $filename = 'brillio-analytics-personnalite';
                break;

            case 'chat':
                $data['stats'] = [
                    'total' => ChatMessage::whereBetween('created_at', [$start, $end])->count(),
                    'user' => ChatMessage::where('role', 'user')
                        ->whereBetween('created_at', [$start, $end])->count(),
                    'assistant' => ChatMessage::where('role', 'assistant')
                        ->whereBetween('created_at', [$start, $end])->count(),
                    'conversations' => ChatConversation::whereBetween('created_at', [$start, $end])->count(),
                    'human_sessions' => ChatConversation::where('needs_human_support', true)
                        ->whereBetween('created_at', [$start, $end])->count(),
                ];
                $data['topUsers'] = ChatMessage::where('role', 'user')
                    ->whereBetween('chat_messages.created_at', [$start, $end])
                    ->join('chat_conversations', 'chat_messages.conversation_id', '=', 'chat_conversations.id')
                    ->join('users', 'chat_conversations.user_id', '=', 'users.id')
                    ->selectRaw('users.name, users.email, COUNT(chat_messages.id) as message_count')
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('message_count', 'desc')
                    ->take(10)
                    ->get();
                $view = 'admin.exports.analytics-chat';
                $filename = 'brillio-analytics-chat';
                break;

            case 'users':
                $userQuery = User::where('is_admin', false)
                    ->whereBetween('created_at', [$start, $end]);

                $userCount = $userQuery->count();
                if ($userCount > 500) {
                    return back()->with('error', "Le volume de données ({$userCount} utilisateurs) est trop important pour un export PDF. Veuillez utiliser l'export CSV (Large volume).");
                }

                $data['users'] = $userQuery->with(['personalityTest', 'mentorProfile'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data['stats'] = [
                    'total' => $data['users']->count(),
                    'jeunes' => $data['users']->where('user_type', 'jeune')->count(),
                    'mentors' => $data['users']->where('user_type', 'mentor')->count(),
                    'with_test' => $data['users']->filter(fn ($u) => $u->personalityTest && $u->personalityTest->completed_at)->count(),
                ];
                $view = 'admin.exports.analytics-users';
                $filename = 'brillio-analytics-utilisateurs';
                break;

            case 'mentors':
                $mentorQuery = MentorProfile::with(['user', 'specializationModel', 'roadmapSteps'])
                    ->whereBetween('created_at', [$start, $end]);

                $mentorCount = $mentorQuery->count();
                if ($mentorCount > 500) {
                    return back()->with('error', "Le volume de données ({$mentorCount} mentors) est trop important pour un export PDF. Veuillez utiliser l'export CSV (Large volume).");
                }

                $data['mentors'] = $mentorQuery->orderBy('created_at', 'desc')
                    ->get();
                $data['stats'] = [
                    'total' => $data['mentors']->count(),
                    'published' => $data['mentors']->where('is_published', true)->count(),
                    'pending' => $data['mentors']->where('is_published', false)->count(),
                    'with_roadmap' => $data['mentors']->filter(fn ($m) => $m->roadmapSteps->count() > 0)->count(),
                ];
                $view = 'admin.exports.analytics-mentors';
                $filename = 'brillio-analytics-mentors';
                break;

            default:
                return back()->with('error', 'Type d\'export non reconnu');
        }

        $filename .= '-'.$start->format('Y-m-d').'-'.$end->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Export des données en CSV (Streaming pour économiser la mémoire)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $dateRange = $this->getDateRange($request);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $type = $request->get('type', 'users');

        $filename = 'brillio-export-'.$type.'-'.$start->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $situations = (array) $request->get('situation', []);
        $interests = (array) $request->get('interest', []);
        $countries = (array) $request->get('country', []);
        $goals = (array) $request->get('goal', []);
        $channels = (array) $request->get('channel', []);
        $personalities = (array) $request->get('personality', []);
        $tuitions = (array) $request->get('tuition', []);
        $salaries = (array) $request->get('salary', []);

        $situations = array_filter($situations);
        $interests = array_filter($interests);
        $countries = array_filter($countries);
        $goals = array_filter($goals);
        $channels = array_filter($channels);
        $personalities = array_filter($personalities);
        $tuitions = array_filter($tuitions);
        $salaries = array_filter($salaries);

        $allSituationsDisplay = [
            'college' => 'Collège',
            'lycee' => 'Lycée',
            'etudiant' => 'Université',
            'recherche_emploi' => 'En recherche',
            'emploi' => 'En poste',
            'entrepreneur' => 'Entrepreneur',
            'autre' => 'Autre',
        ];

        return response()->stream(function () use ($type, $start, $end, $situations, $interests, $countries, $goals, $channels, $personalities, $tuitions, $salaries, $allSituationsDisplay) {
            $handle = fopen('php://output', 'w');

            if ($type === 'users') {

                // Header Master
                fputcsv($handle, [
                    'Nom',
                    'Email',
                    'Situation (Actuelle)',
                    'Niveau',
                    'Établissement/Entreprise',
                    'Scolarité (Range)',
                    'Ville',
                    'MBTI',
                    'Intérêts',
                    'Objectifs Onboarding',
                    'Source Acquisition',
                    'Nb Mentorats Sent',
                    'Nb Mentorats OK',
                    'Nb Sessions OK',
                    'Credits Restants',
                    'Dernière Activité',
                    'Inscription',
                ]);

                // Query préparée pour le "Smart Sourcing"
                $query = User::where('user_type', 'jeune')
                    ->whereBetween('created_at', [$start, $end]);

                if (! empty($situations)) {
                    $query->where(function ($q) use ($situations) {
                        $q->whereIn('onboarding_data->current_situation', $situations)
                            ->orWhereHas('detailedProfiles', function ($sq) use ($situations) {
                                $sq->whereIn('status', $situations);
                            });
                    });
                }

                if (! empty($interests)) {
                    $query->where(function ($q) use ($interests) {
                        foreach ($interests as $interest) {
                            $q->orWhereJsonContains('onboarding_data->interests', $interest);
                        }
                    });
                }

                if (! empty($countries)) {
                    $query->whereIn('country', $countries);
                }

                if (! empty($goals)) {
                    $query->where(function ($q) use ($goals) {
                        foreach ($goals as $goal) {
                            $q->orWhereJsonContains('onboarding_data->goals', $goal);
                        }
                    });
                }

                if (! empty($channels)) {
                    $query->whereIn('onboarding_data->how_found_us', $channels);
                }

                if (! empty($personalities)) {
                    $query->whereHas('personalityTest', function ($q) use ($personalities) {
                        $q->whereIn('personality_type', $personalities);
                    });
                }

                if (! empty($tuitions)) {
                    $query->where(function ($q) use ($tuitions) {
                        $q->whereIn('onboarding_data->tuition_range', $tuitions)
                            ->orWhereHas('detailedProfiles', function ($sq) use ($tuitions) {
                                $sq->whereIn('data->tuition_range', $tuitions);
                            });
                    });
                }

                if (! empty($salaries)) {
                    $query->whereHas('detailedProfiles', function ($sq) use ($salaries) {
                        $sq->whereIn('data->salary_range', $salaries);
                    });
                }

                $users = $query->with(['personalityTest', 'mentorshipsAsMentee', 'mentoringSessionsAsMentee', 'detailedProfiles' => fn ($q) => $q->latest()])
                    ->orderBy('created_at', 'desc')
                    ->cursor();

                foreach ($users as $user) {
                    $onboarding = $user->onboarding_data ?? [];
                    $latestDetailed = $user->detailedProfiles->first();
                    $data = $latestDetailed ? array_merge($onboarding, $latestDetailed->data ?? []) : $onboarding;

                    $sitRaw = $latestDetailed->status ?? ($onboarding['current_situation'] ?? '-');
                    $sitLabel = $allSituationsDisplay[$sitRaw] ?? ucfirst($sitRaw);

                    fputcsv($handle, [
                        $user->name,
                        $user->email,
                        $sitLabel,
                        ucfirst($data['class_level'] ?? ($onboarding['education_level'] ?? '-')),
                        $data['institution'] ?? ($data['company'] ?? '-'),
                        $data['tuition_range'] ?? '-',
                        $data['city'] ?? ($user->city ?? '-'),
                        $user->personalityTest && $user->personalityTest->completed_at ? $user->personalityTest->personality_type : '-',
                        implode(', ', $onboarding['interests'] ?? []),
                        implode(', ', $onboarding['goals'] ?? []),
                        $onboarding['how_found_us'] ?? '-',
                        $user->mentorshipsAsMentee->count(),
                        $user->mentorshipsAsMentee->where('status', 'accepted')->count(),
                        $user->mentoringSessionsAsMentee->where('status', 'completed')->count(),
                        $user->wallet_balance ?? 0,
                        $user->last_login_at ? $user->last_login_at->format('d/m/Y') : 'Jamais',
                        $user->created_at->format('d/m/Y'),
                    ]);
                }
            } elseif ($type === 'mentors') {
                // Header
                fputcsv($handle, ['Nom', 'Email', 'Spécialisation', 'Localisation', 'Inscrit le', 'Publié']);

                $profiles = MentorProfile::with('user')
                    ->whereBetween('created_at', [$start, $end])
                    ->cursor();

                foreach ($profiles as $profile) {
                    fputcsv($handle, [
                        $profile->user->name ?? 'N/A',
                        $profile->user->email ?? 'N/A',
                        $this->specializations[$profile->specialization] ?? $profile->specialization ?? '-',
                        ($profile->user->city ?? '-').', '.($profile->user->country ?? '-'),
                        $profile->created_at->format('d/m/Y'),
                        $profile->is_published ? 'Oui' : 'Non',
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export JSON (garder pour compatibilité)
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'users');
        $dateRange = $this->getDateRange($request);

        switch ($type) {
            case 'users':
                $data = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->get()->toArray();
                break;
            case 'personality':
                $data = PersonalityTest::with('user')
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->get()->toArray();
                break;
            default:
                return back()->with('error', 'Type d\'export non reconnu');
        }

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$type}_export.json");
    }

    /**
     * Statistiques générales pour l'export PDF
     */
    private function getGeneralStats(Carbon $start, Carbon $end): array
    {
        return [
            'users' => [
                'total' => User::where('is_admin', false)->count(),
                'period' => User::where('is_admin', false)
                    ->whereBetween('created_at', [$start, $end])->count(),
                'jeunes' => User::where('user_type', 'jeune')
                    ->whereBetween('created_at', [$start, $end])->count(),
                'mentors' => User::where('user_type', 'mentor')
                    ->whereBetween('created_at', [$start, $end])->count(),
            ],
            'tests' => [
                'total' => PersonalityTest::whereNotNull('personality_type')->count(),
                'period' => PersonalityTest::whereNotNull('personality_type')
                    ->whereBetween('completed_at', [$start, $end])->count(),
                'completion_rate' => $this->calculateTestCompletionRate(),
            ],
            'chat' => [
                'conversations' => ChatConversation::whereBetween('created_at', [$start, $end])->count(),
                'messages' => ChatMessage::whereBetween('created_at', [$start, $end])->count(),
            ],
            'documents' => [
                'total' => AcademicDocument::count(),
                'period' => AcademicDocument::whereBetween('created_at', [$start, $end])->count(),
            ],
            'mentors' => [
                'active' => MentorProfile::where('is_published', true)->count(),
                'pending' => MentorProfile::where('is_published', false)->count(),
            ],
            'countries' => User::select('country', DB::raw('count(*) as total'))
                ->where('is_admin', false)
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            'personality_types' => PersonalityTest::whereNotNull('personality_type')
                ->whereBetween('completed_at', [$start, $end])
                ->select('personality_type', DB::raw('count(*) as count'))
                ->groupBy('personality_type')
                ->orderByDesc('count')
                ->get(),
        ];
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
