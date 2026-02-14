<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\MentorProfile;
use Illuminate\Http\Request;

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
}