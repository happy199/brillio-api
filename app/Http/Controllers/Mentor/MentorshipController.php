<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorshipController extends Controller
{
    /**
     * Liste des mentés (Dashboard Mentorat / Mes Mentés)
     */
    public function index()
    {
        $mentor = Auth::user();

        // Récupérer les demandes en attente
        $pendingRequests = $mentor->mentorshipsAsMentor()
            ->where('status', 'pending')
            ->with('mentee.jeuneProfile') // Assumer relations
            ->orderByDesc('created_at')
            ->get();

        // Récupérer les mentés actifs
        $activeMentees = $mentor->mentorshipsAsMentor()
            ->where('status', 'accepted')
            ->with('mentee.jeuneProfile')
            ->orderByDesc('updated_at')
            ->get();

        // Récupérer les refusés/déconnectés pour historique (optionnel)
        $history = $mentor->mentorshipsAsMentor()
            ->whereIn('status', ['refused', 'disconnected', 'cancelled'])
            ->with('mentee')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        return view('mentor.mentorship.mentees.index', compact('pendingRequests', 'activeMentees', 'history'));
    }

    /**
     * Accepter une demande de mentorat
     */
    public function accept(Mentorship $mentorship)
    {
        // $this->authorize('update', $mentorship);

        if ($mentorship->mentor_id !== Auth::id()) {
            abort(403);
        }

        $mentorship->update([
            'status' => 'accepted',
            'updated_at' => now(),
        ]);

        // Notification logique ici (TODO)

        return redirect()->back()->with('success', 'Demande de mentorat acceptée avec succès.');
    }

    /**
     * Refuser une demande de mentorat
     */
    public function refuse(Request $request, Mentorship $mentorship)
    {
        if ($mentorship->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'refusal_reason' => 'required|string|max:500',
        ]);

        $mentorship->update([
            'status' => 'refused',
            'refusal_reason' => $request->refusal_reason,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Demande refusée.');
    }

    /**
     * Se déconnecter d'un menté (Arrêter le mentorat)
     */
    public function disconnect(Request $request, Mentorship $mentorship)
    {
        if ($mentorship->mentor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'diction_reason' => 'required|string|max:500',
        ]);

        $mentorship->update([
            'status' => 'disconnected',
            'diction_reason' => $request->diction_reason,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Mentorat terminé.');
    }
}
