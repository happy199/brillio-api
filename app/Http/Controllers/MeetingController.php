<?php

namespace App\Http\Controllers;

use App\Models\MentoringSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Affiche la salle de conférence sécurisée (iframe)
     */
    public function show(MentoringSession $session)
    {
        $user = Auth::user();

        // 1. Vérifier si l'utilisateur est participant (Mentor ou Menté)
        $isMentor = $session->mentor_id === $user->id;
        $isMentee = $session->mentees()->where('user_id', $user->id)->exists();

        if (!$isMentor && !$isMentee) {
            abort(403, 'Accès refusé. Vous ne faites pas partie de cette séance.');
        }

        // 2. Vérifier statut session (pas annulée)
        if ($session->status === 'cancelled') {
            return redirect()->route($isMentor ? 'mentor.mentorship.sessions.show' : 'jeune.mentorship.sessions.show', $session)
                ->with('error', 'Cette séance a été annulée.');
        }

        // 3. Obtenir le lien Jitsi brut (stocké en DB ou généré à la volée)
        $meetingLink = $session->meeting_link;
        // On assure que c'est un lien complet pour l'iframe src.
        // ex: https://meet.jit.si/Brillio_123_XYZ

        return view('common.meeting.show', compact('session', 'meetingLink', 'isMentor'));
    }
}
