<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Models\MentorProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SponsoredUsersController extends Controller
{
    /**
     * Liste des utilisateurs parrainés
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        $query = $organization->users()
            ->where('users.user_type', User::TYPE_JEUNE)
            ->with(['personalityTest', 'jeuneProfile']);

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par Test de Personnalité
        if ($request->filled('test_status')) {
            if ($request->test_status === 'completed') {
                $query->whereHas('personalityTest', function ($q) {
                    $q->whereNotNull('completed_at');
                });
            } elseif ($request->test_status === 'pending') {
                $query->whereDoesntHave('personalityTest', function ($q) {
                    $q->whereNotNull('completed_at');
                });
            }
        }

        // Filtre par Activité
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where(function ($q) {
                    $q->where('last_login_at', '>=', now()->subDays(30));
                });
            } elseif ($request->status === 'inactive') {
                // Inactif = jamais connecté OU connecté il y a plus de 30 jours
                $query->where(function ($q) {
                    $q->where('last_login_at', '<', now()->subDays(30))
                        ->orWhereNull('last_login_at');
                });
            }
        }

        if (! $organization->isPro()) {
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        } else {
            $users = $query->latest()->paginate(12)->withQueryString();
        }

        return view('organization.users.index', compact('organization', 'users'));
    }

    /**
     * Détail d'un utilisateur parrainé
     */
    public function show(User $user)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification de sécurité : l'utilisateur doit être lié à cette organisation
        if (! $organization->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'Accès non autorisé');
        }

        if (! $organization->isPro()) {
            // Return view without loading sensitive data, view will handle blur
            return view('organization.users.show', [
                'organization' => $organization,
                'user' => $user,
                'aiConversationsCount' => 0,
                'lastAiActivity' => null,
                'mentorships' => collect(),
                'viewedResources' => collect(),
                'purchasedResources' => collect(),
                'consultedMentors' => collect(),
            ]);
        }

        $user->load(['personalityTest', 'jeuneProfile', 'academicDocuments']);

        // Activité IA
        $aiConversationsCount = $user->chatConversations()->count();
        $lastAiActivity = $user->chatConversations()->latest('updated_at')->first()?->updated_at;

        // Mentorats
        $mentorships = $user->mentorshipsAsMentee()->with(['mentor'])->get();
        foreach ($mentorships as $mentorship) {
            $mentorship->sessions_count = \App\Models\MentoringSession::where('mentor_id', $mentorship->mentor_id)
                ->whereHas('mentees', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->count();
        }

        // Ressources (Vues & Achetées)
        $viewedResources = $user->resourceViews()->with('resource')->latest()->get()->unique('resource_id');
        $purchasedResources = $user->purchases()->where('item_type', \App\Models\Resource::class)->with('item')->latest()->get();

        // Mentors consultés
        $consultedMentors = $user->mentorProfileViews()->with('mentor.mentorProfile')->latest()->get()->pluck('mentor')->unique();

        return view('organization.users.show', compact(
            'organization',
            'user',
            'aiConversationsCount',
            'lastAiActivity',
            'mentorships',
            'viewedResources',
            'purchasedResources',
            'consultedMentors'
        ));
    }

    /**
     * Détail d'un mentor pour l'organisation
     */
    public function mentorShow(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps']);

        // Mentors similaires (meme specialisation)
        $similarMentors = MentorProfile::where('is_published', true)
            ->where('id', '!=', $mentor->id)
            ->where('specialization', $mentor->specialization)
            ->with('user')
            ->limit(3)
            ->get();

        return view('jeune.mentor-show', [
            'mentor' => $mentor,
            'similarMentors' => $similarMentors,
            'existingMentorship' => null,
            'layout' => 'layouts.organization',
        ]);
    }

    /**
     * Exporter le rapport complet d'un jeune (PDF ou CSV)
     */
    public function export(Request $request, User $user)
    {
        $organization = $this->getCurrentOrganization();

        // Sécurité
        if (! $organization->users()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        $format = $request->query('format', 'pdf');
        $user->load(['personalityTest', 'jeuneProfile', 'academicDocuments', 'mentorshipsAsMentee.mentor']);

        // Mentorship sessions
        $sessions = MentoringSession::whereHas('mentees', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->with('mentor')->orderBy('scheduled_at', 'desc')->get();

        // Data Enrichment
        // 1. AI Stats
        $aiStats = [
            'count' => $user->chatConversations()->count(),
            'last_activity' => $user->chatConversations()->max('updated_at'),
        ];

        // 2. Mentors
        $viewedMentors = \App\Models\MentorProfileView::where('user_id', $user->id)
            ->with('mentor')
            ->orderBy('viewed_at', 'desc')
            ->get()
            ->unique('mentor_id');

        $activeMentorships = $user->mentorshipsAsMentee()->with('mentor')->get();

        // 3. Personality
        $personalityTest = $user->personalityTest;

        // 4. Resources
        $resourcesViewedCount = $user->resourceViews()->count();

        // 5. Onboarding Data
        $onboarding = $user->onboarding_data ?? [];
        $profileData = [
            'situation' => $onboarding['situation'] ?? 'Non renseigné',
            'education_level' => $onboarding['education_level'] ?? 'Non renseigné',
            'goals' => isset($onboarding['goals']) ? (is_array($onboarding['goals']) ? implode(', ', $onboarding['goals']) : $onboarding['goals']) : 'Non renseigné',
            'interests' => isset($onboarding['interests']) ? (is_array($onboarding['interests']) ? implode(', ', $onboarding['interests']) : $onboarding['interests']) : 'Non renseigné',
            'challenges' => isset($onboarding['challenges']) ? (is_array($onboarding['challenges']) ? implode(', ', $onboarding['challenges']) : $onboarding['challenges']) : 'Non renseigné',
        ];

        $fileName = 'rapport_'.str_replace(' ', '_', strtolower($user->name)).'_'.now()->format('Ymd');

        if ($format === 'pdf') {
            $title = 'Rapport Individuel - '.$user->name;
            $pdf = Pdf::loadView('reports.mentee_individual', compact(
                'organization',
                'user',
                'sessions',
                'title',
                'aiStats',
                'viewedMentors',
                'activeMentorships',
                'personalityTest',
                'resourcesViewedCount',
                'profileData'
            ));

            return $pdf->download($fileName.'.pdf');
        }

        // CSV Export
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.csv',
            'Pragma' => 'no-cache',
        ];

        $callback = function () use ($user, $sessions, $aiStats, $viewedMentors, $activeMentorships, $personalityTest, $resourcesViewedCount, $profileData) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['RAPPORT INDIVIDUEL - '.strtoupper($user->name)]);
            fputcsv($file, []);

            // SECTION 1: PROFIL
            fputcsv($file, ['INFORMATIONS GÉNÉRALES']);
            fputcsv($file, ['Email', $user->email]);
            fputcsv($file, ['Ville', $user->city ?? 'N/A']);
            fputcsv($file, ['Date de naissance', $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'N/A']);
            fputcsv($file, ['Téléphone', $user->phone ?? 'N/A']);
            fputcsv($file, ['Date Inscription', $user->created_at->format('d/m/Y')]);
            fputcsv($file, ['Complétion Profil', $user->profile_completion_percentage.'%']);
            fputcsv($file, []);

            fputcsv($file, ['PARCOURS & OBJECTIFS']);
            fputcsv($file, ['Situation', $profileData['situation']]);
            fputcsv($file, ['Niveau d\'études', $profileData['education_level']]);
            fputcsv($file, ['Objectifs', $profileData['goals']]);
            fputcsv($file, ['Centres d\'intérêt', $profileData['interests']]);
            fputcsv($file, ['Défis', $profileData['challenges']]);
            fputcsv($file, []);

            // SECTION 2: ACTIVITÉ IA & RESSOURCES
            fputcsv($file, ['ACTIVITÉ IA & RESSOURCES']);
            fputcsv($file, ['Conversations IA', $aiStats['count']]);
            fputcsv($file, ['Dernière activité IA', $aiStats['last_activity'] ? \Carbon\Carbon::parse($aiStats['last_activity'])->format('d/m/Y H:i') : 'Jamais']);
            fputcsv($file, ['Ressources consultées', $resourcesViewedCount]);
            fputcsv($file, []);

            // SECTION 3: PERSONNALITÉ
            fputcsv($file, ['PERSONNALITÉ (MBTI)']);
            if ($personalityTest) {
                fputcsv($file, ['Type', $personalityTest->personality_type.' - '.$personalityTest->personality_label]);
                fputcsv($file, ['Description', strip_tags($personalityTest->personality_description)]); // Basic strip tags suitable for CSV
            } else {
                fputcsv($file, ['Type', 'Test non réalisé']);
            }
            fputcsv($file, []);

            // SECTION 4: MENTORS
            fputcsv($file, ['MENTORS CONSULTÉS ('.$viewedMentors->count().')']);
            if ($viewedMentors->count() > 0) {
                fputcsv($file, ['Nom', 'Vue le']);
                foreach ($viewedMentors as $view) {
                    fputcsv($file, [$view->mentor->name, $view->viewed_at->format('d/m/Y H:i')]);
                }
            } else {
                fputcsv($file, ['Aucun mentor consulté']);
            }
            fputcsv($file, []);

            fputcsv($file, ['MENTORATS ('.$activeMentorships->count().')']);
            if ($activeMentorships->count() > 0) {
                fputcsv($file, ['Mentor', 'Statut', 'Débuté le']);
                foreach ($activeMentorships as $mentorship) {
                    fputcsv($file, [$mentorship->mentor->name, $mentorship->translated_status, $mentorship->created_at->format('d/m/Y')]);
                }
            } else {
                fputcsv($file, ['Aucun mentorat actif']);
            }
            fputcsv($file, []);

            fputcsv($file, ['HISTORIQUE DES SÉANCES ('.$sessions->count().')']);
            fputcsv($file, ['Date', 'Mentor', 'Statut', 'Progrès', 'Obstacles', 'Objectifs SMART']);

            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->scheduled_at->format('d/m/Y H:i'),
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
}
