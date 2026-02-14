<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\MentorProfile;
use App\Models\MentoringSession;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class SponsoredUsersController extends Controller
{
    /**
     * Liste des utilisateurs parrainés
     */
    public function index(Request $request)
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();
        
        $query = $organization->sponsoredUsers()->with(['personalityTest', 'jeuneProfile']);

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par Test de Personnalité
        if ($request->filled('test_status')) {
            if ($request->test_status === 'completed') {
                $query->whereHas('personalityTest', function($q) {
                    $q->whereNotNull('completed_at');
                });
            } elseif ($request->test_status === 'pending') {
                $query->whereDoesntHave('personalityTest', function($q) {
                    $q->whereNotNull('completed_at');
                });
            }
        }

        // Filtre par Activité
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('last_login_at', '>=', now()->subDays(30));
            } elseif ($request->status === 'inactive') {
                // Inactif = jamais connecté OU connecté il y a plus de 30 jours
                $query->where(function($q) {
                    $q->where('last_login_at', '<', now()->subDays(30))
                      ->orWhereNull('last_login_at');
                });
            }
        }

        $users = $query->latest()->paginate(12)->withQueryString();

        return view('organization.users.index', compact('organization', 'users'));
    }

    /**
     * Détail d'un utilisateur parrainé
     */
    public function show(User $user)
    {
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        // Vérification de sécurité : l'utilisateur doit être parrainé par cette organisation
        if ($user->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
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
        $viewedResources = $user->resourceViews()->with('resource')->latest()->get()->pluck('resource')->unique();
        $purchasedResources = $user->purchases()->where('item_type', \App\Models\Resource::class)->with('item')->latest()->get()->pluck('item');

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
        $organization = Organization::where('contact_email', auth()->user()->email)->firstOrFail();

        // Sécurité
        if ($user->sponsored_by_organization_id !== $organization->id) {
            abort(403);
        }

        $format = $request->query('format', 'pdf');
        $user->load(['personalityTest', 'jeuneProfile', 'academicDocuments', 'mentorshipsAsMentee.mentor']);
        
        // Mentorship sessions
        $sessions = MentoringSession::whereHas('mentees', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->with('mentor')->orderBy('scheduled_at', 'desc')->get();

        $fileName = "rapport_" . str_replace(' ', '_', strtolower($user->name)) . "_" . now()->format('Ymd');

        if ($format === 'pdf') {
            $title = "Rapport Individuel - " . $user->name;
            $pdf = Pdf::loadView('reports.mentee_individual', compact('organization', 'user', 'sessions', 'title'));
            return $pdf->download($fileName . ".pdf");
        }

        // CSV Export
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=" . $fileName . ".csv",
            "Pragma" => "no-cache"
        ];

        $callback = function () use ($user, $sessions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['RAPPORT INDIVIDUEL - ' . strtoupper($user->name)]);
            fputcsv($file, []);
            fputcsv($file, ['Email', $user->email]);
            fputcsv($file, ['Ville', $user->city ?? 'N/A']);
            fputcsv($file, ['Complétion Profil', $user->profile_completion_percentage . '%']);
            fputcsv($file, ['Date Inscription', $user->created_at->format('d/m/Y')]);
            fputcsv($file, []);
            fputcsv($file, ['HISTORIQUE DES SÉANCES']);
            fputcsv($file, ['Date', 'Mentor', 'Statut', 'Progrès', 'Obstacles', 'Objectifs SMART']);

            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->scheduled_at->format('d/m/Y H:i'),
                    $session->mentor->name,
                    $session->translated_status,
                    $session->report_content['progress'] ?? '-',
                    $session->report_content['obstacles'] ?? '-',
                    $session->report_content['smart_goals'] ?? '-'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}