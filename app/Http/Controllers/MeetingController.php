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
    protected $jitsiService;

    public function __construct(\App\Services\JitsiService $jitsiService)
    {
        $this->jitsiService = $jitsiService;
    }

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

        // Generate JWT for JaaS
        // Room Name must be the part after the last slash
        $roomName = basename($session->meeting_link);
        $jwt = $this->jitsiService->generateToken($user, $roomName, $isMentor);

        // Update Link to use 8x8 JaaS domain
        // Format: https://8x8.vc/<AppID>/<RoomName>
        $appId = env('JAAS_APP_ID');
        $meetingLink = "https://8x8.vc/{$appId}/{$roomName}";

        return view('common.meeting.show', compact('session', 'meetingLink', 'jwt', 'isMentor', 'appId', 'roomName'));
    }
}
