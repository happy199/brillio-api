<?php

namespace App\Http\Controllers;

use App\Models\MentoringSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GuestAccessController extends Controller
{
    /**
     * Affiche la page de confirmation d'identité pour l'invité
     */
    public function confirm(MentoringSession $session, $token)
    {
        // 1. Vérifier si le token correspond
        if ($session->guest_token !== $token) {
            abort(403, 'Lien invalide ou expiré.');
        }

        // 2. Vérifier si la session est dans le futur ou en cours
        if ($session->scheduled_at->addMinutes($session->duration_minutes)->isPast()) {
            return view('guest.expired', compact('session'));
        }

        return view('guest.confirm', compact('session', 'token'));
    }

    /**
     * Vérifie l'email saisi et redirige vers la salle de réunion
     */
    public function handleConfirm(Request $request, MentoringSession $session, $token)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // 1. Vérifier le token à nouveau
        if ($session->guest_token !== $token) {
            abort(403);
        }

        // 2. Vérifier si l'email correspond à l'un des intervenants de la séance
        $attendeeEmails = $session->all_mentors->pluck('email')->map(fn($e) => strtolower($e))->toArray();
        if (!in_array(strtolower($request->email), $attendeeEmails)) {
            return back()->with('error', "L'adresse email saisie ne correspond à aucun intervenant invité pour cette séance.");
        }

        // 3. Stocker l'autorisation en session (sécurité temporaire)
        Session::put("guest_auth_{$session->id}", [
            'email' => $request->email,
            'expires_at' => now()->addHours(2),
        ]);

        // 4. Rediriger vers la room Jitsi via le MeetingController bypass
        $meetingId = basename($session->meeting_link);
        
        return redirect()->route('meeting.show.guest', [
            'meetingId' => $meetingId,
            'guestToken' => $token
        ]);
    }
}
