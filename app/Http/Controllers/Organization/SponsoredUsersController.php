<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
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

        // Activité IA (simulée pour l'instant car pas de relation directe simple)
        $aiConversationsCount = $user->chatConversations()->count();
        $lastAiActivity = $user->chatConversations()->latest('updated_at')->first()?->updated_at;

        return view('organization.users.show', compact('organization', 'user', 'aiConversationsCount', 'lastAiActivity'));
    }
}