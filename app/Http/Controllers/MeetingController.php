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
    public function show($meetingId)
    {
        $user = Auth::user();

        // 3. Reconstituer le lien ou chercher via room name
        // On suppose que meeting_link = https://meet.jit.si/$meetingId
        // Recherche de la session correspondante
        // Note: LIKE est plus sûr si jamais le préfixe change un jour, mais ici exact match sur fin de chaine
        $session = MentoringSession::where('meeting_link', 'LIKE', '%' . $meetingId)->firstOrFail();

        // 1. Vérifier si l'utilisateur est participant (Mentor ou Menté)
        $isMentor = $session->mentor_id === $user->id;
        $isMentee = $session->mentees()->where('user_id', $user->id)->exists();

        if (!$isMentor && !$isMentee) {
            abort(403, 'Accès refusé. Vous ne faites pas partie de cette séance.');
        }

        // 2. Vérifier statut session (pas annulée)
        if ($session->status === 'cancelled') {
            return redirect()->route($isMentor ? 'mentor.mentorship.sessions.show' : 'jeune.sessions.show', $session)
                ->with('error', 'Cette séance a été annulée.');
        }

        $meetingLink = $session->meeting_link;
        // On assure que c'est un lien complet pour l'iframe src.
        // ex: https://meet.jit.si/Brillio_123_XYZ

        return view('common.meeting.show', compact('session', 'meetingLink', 'isMentor'));
    }
}
