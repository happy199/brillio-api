<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MentorshipController extends Controller
{
    /**
     * Liste des mentors du jeune (Actifs et En attente)
     */
    public function index()
    {
        $user = auth()->user();

        // Si le profil n'est pas public, on pourrait aussi gérer ici, mais le middleware est plus propre
        // On garde l'index standard ici, le middleware fera son travail

        // Récupérer les mentorats par statut
        $activeMentorships = $user->mentorshipsAsMentee()
            ->where('status', 'accepted')
            ->with(['mentor.mentorProfile'])
            ->latest()
            ->get();

        $pendingRequests = $user->mentorshipsAsMentee()
            ->where('status', 'pending')
            ->with(['mentor.mentorProfile'])
            ->latest()
            ->get();

        $history = $user->mentorshipsAsMentee()
            ->whereIn('status', ['refused', 'disconnected', 'cancelled'])
            ->with(['mentor.mentorProfile'])
            ->latest()
            ->get();

        return view('jeune.mentorship.index', compact('activeMentorships', 'pendingRequests', 'history'));
    }

    /**
     * Page de verrouillage si profil non publié
     */
    public function lockedIndex()
    {
        // Si le profil est déjà public, on redirige vers l'index
        if (auth()->user()->jeuneProfile?->is_public) {
            return redirect()->route('jeune.mentorship.index');
        }

        return view('jeune.mentorship.locked');
    }

    /**
     * Envoyer une demande de mentorat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mentor_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $jeuneProfile = $user->jeuneProfile;

        // Si le profil n'est pas public, le publier de manière transparente
        if ($jeuneProfile && !$jeuneProfile->is_public) {
            $jeuneProfile->update([
                'is_public' => true,
                'published_at' => now(),
                'public_slug' => $jeuneProfile->public_slug ?? \Str::slug($user->name) . '-' . \Str::random(6)
            ]);
        }

        $mentorId = $validated['mentor_id'];

        // Vérifier si le mentor existe et est bien un mentor
        $mentor = User::where('id', $mentorId)->where('user_type', User::TYPE_MENTOR)->firstOrFail();

        // Vérifier si une demande existe déjà
        $existing = Mentorship::where('mentee_id', $user->id)
            ->where('mentor_id', $mentorId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous avez déjà une demande en cours ou active avec ce mentor.');
        }

        $mentorship = Mentorship::create([
            'mentee_id' => $user->id,
            'mentor_id' => $mentorId,
            'status' => 'pending',
            'request_message' => $validated['message'] ?? null,
        ]);

        // Notification email au mentor
        app(\App\Services\MentorshipNotificationService::class)->sendMentorshipRequest($mentorship);

        return back()->with('success', 'Votre demande de mentorat a été envoyée avec succès.');
    }

    /**
     * Annuler une demande de mentorat
     */
    public function cancel(Request $request, Mentorship $mentorship)
    {
        $user = auth()->user();

        // Vérifier que la demande appartient à l'utilisateur et est en attente
        if ($mentorship->mentee_id !== $user->id) {
            abort(403);
        }

        if ($mentorship->status !== 'pending') {
            return back()->with('error', 'Vous ne pouvez annuler qu\'une demande en attente.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $mentorship->update([
            'status' => 'cancelled',
            'cancellation_reason' => request('cancellation_reason'),
        ]);

        return back()->with('success', 'Demande annulée avec succès.');
    }
}