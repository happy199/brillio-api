<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Controller pour la gestion des utilisateurs dans le dashboard admin
 */
class UserController extends Controller
{
    /**
     * Liste tous les utilisateurs avec filtres
     */
    public function index(Request $request)
    {
        $query = User::with(['personalityTest', 'mentorProfile']);

        // Filtre par type
        if ($type = $request->get('type')) {
            $query->where('user_type', $type);
        }

        // Filtre par pays
        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        // Recherche par nom ou email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut d'archivage
        if ($request->has('archived')) {
            $query->where('is_archived', true);
        } else {
            // Par défaut, on ne montre pas les archivés sauf si demandé explicitement
            // OU on peut décider de tout montrer et utiliser un badget.
            // Pour l'instant, faisons un filtre explicite : ?archived=1 pour voir les archives
            // $query->where('is_archived', false); 
            // ^ Si on décommente ça, ils sont masqués par défaut. 
            // Mais l'utilisateur veut un onglet séparé, donc le filtre est logique.
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(25);

        // Liste des pays pour le filtre
        $countries = User::whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort();

        return view('admin.users.index', compact('users', 'countries'));
    }

    /**
     * Affiche le détail d'un utilisateur
     */
    public function show(User $user)
    {
        $user->load([
            'personalityTest',
            'mentorProfile.roadmapSteps',
            'chatConversations.messages',
            'academicDocuments',
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Supprime un utilisateur définitivement
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été supprimé définitivement");
    }

    /**
     * Réactive un compte archivé
     */
    public function reactivate(User $user)
    {
        if (!$user->is_archived) {
            return back()->with('error', 'Ce compte n\'est pas archivé');
        }

        $user->is_archived = false;
        $user->archived_at = null;
        $user->archived_reason = null;
        $user->save();

        return back()->with('success', "Le compte de {$user->name} a été réactivé avec succès");
    }

    /**
     * Bascule le statut admin d'un utilisateur
     */
    public function toggleAdmin(User $user)
    {
        // Empêcher de retirer ses propres droits admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier vos propres droits admin');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $message = $user->is_admin
            ? "{$user->name} est maintenant administrateur"
            : "{$user->name} n'est plus administrateur";

        return back()->with('success', $message);
    }
}
