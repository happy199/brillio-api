<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorshipController extends Controller
{
    /**
     * Liste des mentorats des jeunes parrainés
     */
    public function index(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            $mentorships = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $orgMentorIds = [];
        } else {
            $query = Mentorship::query()
                ->whereIn('mentee_id', function ($q) use ($organization) {
                    $q->select('id')->from('users')->where('sponsored_by_organization_id', $organization->id);
                })
                ->with(['mentor', 'mentee']);

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $mentorships = $query->latest()->paginate(12)->withQueryString();
            $orgMentorIds = $organization->mentors()->pluck('users.id')->toArray();
        }

        return view('organization.mentorships.index', compact('organization', 'mentorships', 'orgMentorIds'));
    }

    protected $notificationService;

    public function __construct(\App\Services\MentorshipNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Terminer une relation de mentorat
     */
    public function terminate(Request $request, Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification : le menté doit être parrainé par cette organisation
        if ($mentorship->mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        if ($mentorship->status === 'disconnected') {
            return back()->with('error', 'Cette relation est déjà terminée.');
        }

        $request->validate([
            'diction_reason' => 'required|string|max:1000',
        ]);

        $mentorship->update([
            'status' => 'disconnected',
            'diction_reason' => $request->diction_reason,
        ]);

        // Notification générale (envoie au jeune, au mentor, et confirmation à l'org)
        // Note: l'admin de l'org est l'acteur ici
        $actor = $organization->users()->wherePivot('role', 'admin')->first() ?? Auth::user();
        $this->notificationService->sendMentorshipTerminated($mentorship, $actor, $request->diction_reason);

        return back()->with('success', 'La relation de mentorat a été terminée avec succès.');
    }

    /**
     * Formulaire pour créer une nouvelle relation de mentorat
     */
    public function create()
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            abort(403, 'Fonctionnalité réservée aux membres Pro.');
        }

        // Liste des jeunes parrainés sans mentor actif
        $jeunes = User::where('sponsored_by_organization_id', $organization->id)
            ->where('user_type', User::TYPE_JEUNE)
            ->whereDoesntHave('mentorshipsAsMentee', function ($q) {
                $q->whereIn('status', ['pending', 'accepted']);
            })
            ->orderBy('name')
            ->get();

        // Liste des mentors de l'organisation
        $mentors = $organization->mentors()
            ->with('mentorProfile')
            ->orderBy('name')
            ->get();

        return view('organization.mentorships.create', compact('organization', 'jeunes', 'mentors'));
    }

    /**
     * Enregistrer une nouvelle relation de mentorat (créée par l'organisation)
     */
    public function store(Request $request)
    {
        $organization = $this->getCurrentOrganization();

        if (! $organization->isPro()) {
            abort(403, 'Fonctionnalité réservée aux membres Pro.');
        }

        $request->validate([
            'mentee_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
        ]);

        // Vérifications de sécurité
        $mentee = User::findOrFail($request->mentee_id);
        $mentor = User::findOrFail($request->mentor_id);

        if ($mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, "Le jeune n'est pas parrainé par votre organisation.");
        }

        if (! $organization->mentors()->where('users.id', $mentor->id)->exists()) {
            abort(403, "Le mentor n'est pas lié à votre organisation.");
        }

        // Vérifier si une relation existe déjà
        $existing = Mentorship::where('mentee_id', $mentee->id)
            ->where('mentor_id', $mentor->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Une relation active existe déjà entre ce jeune et ce mentor.');
        }

        // Créer la relation
        $mentorship = Mentorship::create([
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentor->id,
            'status' => 'accepted',
            'request_message' => "Relation créée manuellement par l'organisation {$organization->name}.",
        ]);

        // Notification
        $actor = Auth::user();
        $this->notificationService->sendMentorshipCreatedByOrg($mentorship, $actor);

        return redirect()->route('organization.mentorships.index')
            ->with('success', "La relation de mentorat entre {$mentee->name} et {$mentor->name} a été créée avec succès.");
    }

    /**
     * Valider une demande de mentorat en attente
     */
    public function validateMentorship(Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification : le menté doit être parrainé par cette organisation
        if ($mentorship->mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        // Vérification : le mentor doit être lié à cette organisation
        if (! $organization->mentors()->where('users.id', $mentorship->mentor_id)->exists()) {
            return back()->with('error', "Vous ne pouvez pas valider cette demande car le mentor n'est pas lié à votre organisation.");
        }

        if ($mentorship->status !== 'pending') {
            return back()->with('error', 'Cette demande ne peut pas être validée car elle n\'est pas en attente.');
        }

        $mentorship->update(['status' => 'accepted']);

        // Notification (optionnel, mais recommandé si on veut informer le mentor/jeune)
        // Pour l'instant on utilise le service existant si possible ou on envoie un mail simple
        $this->notificationService->sendMentorshipAccepted($mentorship);

        return back()->with('success', "La relation de mentorat entre {$mentorship->mentee->name} et {$mentorship->mentor->name} a été validée.");
    }

    /**
     * Détail d'un mentorat
     */
    public function show(Mentorship $mentorship)
    {
        $organization = $this->getCurrentOrganization();

        // Vérification : le menté doit être parrainé par cette organisation
        if ($mentorship->mentee->sponsored_by_organization_id !== $organization->id) {
            abort(403, 'Accès non autorisé');
        }

        if (! $organization->isPro()) {
            return view('organization.mentorships.show', [
                'organization' => $organization,
                'mentorship' => $mentorship,
                // Pass empty collections/nulls if view expects them, though we'll blur it out
                'sessions' => collect(),
            ]);
        }

        $mentorship->load(['mentor', 'mentee']);

        return view('organization.mentorships.show', compact('organization', 'mentorship'));
    }
}
