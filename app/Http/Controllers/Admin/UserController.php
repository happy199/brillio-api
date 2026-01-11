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
     * Supprime un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été supprimé");
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
