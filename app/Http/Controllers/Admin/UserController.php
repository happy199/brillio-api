<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Admin\AccountBlockedMail;
use App\Mail\Admin\AccountDeletedMail;
use App\Mail\Admin\PromotionProposalMail;
use App\Models\JeuneProfile;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
// nosemgrep
        if ($type = $request->get('type')) {
            $query->where('user_type', $type);
        }

        // Filtre par pays
// nosemgrep
        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        // Filtre par numéro de téléphone
// nosemgrep
        if ($phoneStatus = $request->get('phone_status')) {
            if ($phoneStatus === 'with_phone') {
                $query->whereNotNull('phone');
            } elseif ($phoneStatus === 'without_phone') {
                $query->whereNull('phone');
            }
        }

        // Recherche par nom ou email
// nosemgrep
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut d'archivage / blocage
        if ($request->has('blocked')) {
            $query->where('is_blocked', true);
        } elseif ($request->has('archived')) {
            $query->where('is_archived', true)->where('is_blocked', false);
        } else {
            $query->where('is_archived', false)->where('is_blocked', false);
        }

        // Tri
// nosemgrep
        $sortBy = $request->get('sort', 'created_at');
// nosemgrep
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
     * Show form for creating a new user (demo account)
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user (demo account)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'user_type' => 'required|in:jeune,mentor,admin,organization',
        ]);

        $password = Str::random(12);

        $userType = $request->user_type;
        $isAdmin = false;

        if ($userType === 'admin') {
            $isAdmin = true;
            $userType = 'mentor'; // Admins are technically mentors in roles but with is_admin flag
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'user_type' => $userType,
            'is_admin' => $isAdmin,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        // Create basic profile
        if ($userType === 'mentor') {
            MentorProfile::create([
                'user_id' => $user->id,
                'is_validated' => true,
                'bio' => 'Compte de démonstration.',
            ]);
        } elseif ($userType === 'jeune') {
            if (class_exists('App\Models\JeuneProfile')) {
                JeuneProfile::create([
                    'user_id' => $user->id,
                    'bio' => 'Compte de démonstration.',
                ]);
            }
        } elseif ($userType === 'organization') {
            Organization::create([
                'name' => $user->name,
                'contact_email' => $user->email,
                'status' => 'active',
                'subscription_plan' => 'free',
            ]);
            // Link the user to the organization they just created as a member/admin
            $org = Organization::where('contact_email', $user->email)->first();
            if ($org) {
                $user->organizations()->attach($org->id);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.')
            ->with('generated_password', $password)
            ->with('generated_email', $user->email);
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
            'organizations',
        ]);

        $organizations = Organization::orderBy('name')->get();

        return view('admin.users.show', compact('user', 'organizations'));
    }

    /**
     * Link user to an organization
     */
    public function linkOrganization(Request $request, User $user)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user->organizations()->syncWithoutDetaching([$request->organization_id]);

        return back()->with('success', "L'utilisateur a été lié à l'organisation.");
    }

    /**
     * Unlink user from an organization
     */
    public function unlinkOrganization(User $user, Organization $organization)
    {
        $user->organizations()->detach($organization->id);

        return back()->with('success', "L'utilisateur a été détaché de l'organisation.");
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

        try {
            Mail::to($user->email)
                ->send(new AccountDeletedMail($user->name));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification suppression compte: '.$e->getMessage());
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été supprimé définitivement");
    }

    /**
     * Archive un compte utilisateur (exclu des campagnes de prospection)
     */
    public function archive(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas archiver votre propre compte.');
        }

        if ($user->is_archived) {
            return back()->with('error', 'Ce compte est déjà archivé.');
        }

        if ($user->is_blocked) {
            return back()->with('error', 'Débloquez d\'abord ce compte avant de l\'archiver.');
        }

// nosemgrep
        $reason = $request->input('reason', 'Archivé par un administrateur.');

        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => $reason,
        ]);

        return back()->with('success', "Le compte de {$user->name} a été archivé.");
    }

    /**
     * Réactive un compte archivé
     */
    public function reactivate(User $user)
    {
        if (! $user->is_archived) {
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

        $user->is_admin = ! $user->is_admin;
        $user->save();

        $message = $user->is_admin
            ? "{$user->name} est maintenant administrateur"
            : "{$user->name} n'est plus administrateur";

        return back()->with('success', $message);
    }

    /**
     * Propose une promotion à un jeune pour devenir mentor
     */
    public function proposePromotion(User $user)
    {
        if (! $user->isJeune()) {
            return back()->with('error', "Cet utilisateur n'est pas un étudiant.");
        }

        // 1. Archiver le compte Jeune immédiatement pour libérer l'accès
        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => 'Promotion administrative initiée vers le statut Mentor.',
        ]);

        $acceptUrl = URL::signedRoute('auth.accept-promotion', ['user' => $user->id]);

        try {
            Mail::to($user->email)->send(new PromotionProposalMail($user, $acceptUrl));

            return back()->with('success', "Le compte de {$user->name} a été archivé et la proposition de promotion a été envoyée.");
        } catch (\Exception $e) {
            Log::error('Erreur envoi proposition promotion: '.$e->getMessage());

            return back()->with('error', "Le compte a été archivé mais une erreur est survenue lors de l'envoi de l'e-mail.");
        }
    }

    /**
     * Bloque un utilisateur
     */
    public function block(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous bloquer vous-même.');
        }

// nosemgrep
        $reason = $request->input('reason', 'Non-respect des règles de la plateforme.');

        $user->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'blocked_reason' => $reason,
        ]);

        try {
            Mail::to($user->email)
                ->send(new AccountBlockedMail($user->name, $reason));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification blocage compte: '.$e->getMessage());
        }

        return back()->with('success', "L'utilisateur {$user->name} a été bloqué.");
    }

    /**
     * Débloque un utilisateur et le place en comptes archivés
     */
    public function unblock(User $user)
    {
        $user->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'blocked_reason' => null,
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => 'Compte débloqué par un administrateur. Réactivation automatique à la prochaine connexion.',
        ]);

        return back()->with('success', "L'utilisateur {$user->name} a été débloqué et déplacé vers les comptes archivés.");
    }
}
