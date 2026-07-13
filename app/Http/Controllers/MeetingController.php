<?php

namespace App\Http\Controllers;

use App\Models\MentoringSession;
use App\Models\User;
use App\Services\JitsiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MeetingController extends Controller
{
    /**
     * Affiche la salle de conférence sécurisée (iframe)
     */
    protected $jitsiService;

    public function __construct(JitsiService $jitsiService)
    {
        $this->jitsiService = $jitsiService;
    }

    /**
     * Affiche la salle de conférence sécurisée (iframe)
     */
    public function show($meetingId)
    {
        Log::info('MeetingController: Request for meeting ID: '.$meetingId);

        try {
            $user = Auth::user();
            Log::info('MeetingController: User ID: '.($user ? $user->id : 'NULL'));

            if (! $user) {
                Log::error('MeetingController: Unexpected NULL user in auth protected route');
                abort(403, 'User not authenticated');
            }

            // 3. Reconstituer le lien ou chercher via room name
            // On suppose que meeting_link = https://meet.jit.si/$meetingId
            // Recherche de la session correspondante
            // Note: LIKE est plus sûr si jamais le préfixe change un jour, mais ici exact match sur fin de chaine
            $session = MentoringSession::where('meeting_link', 'LIKE', '%'.$meetingId)->firstOrFail();

            // 1. Vérifier si l'utilisateur est participant (Mentor, Menté, ou Organisation)
            $isMentor = $session->all_mentors->pluck('id')->contains($user->id);
            $isMentee = $session->mentees()->where('user_id', $user->id)->exists();
            $isOrganizationMember = $user->organization_id === $session->scheduled_by_organization_id ||
                                    $session->mentees()->where('sponsored_by_organization_id', $user->organization_id)->exists();

            if (! $isMentor && ! $isMentee && ! $isOrganizationMember) {
                abort(403, 'Accès refusé. Vous ne faites pas partie de cette séance.');
            }

            // 2. Vérifier statut session (pas annulée)
            if ($session->status === 'cancelled') {
                $route = 'jeune.sessions.show';
                if ($isMentor) {
                    $route = 'mentor.mentorship.sessions.show';
                }
                if ($isOrganizationMember) {
                    $route = 'organization.sessions.show';
                }

                return redirect()->route($route, $session)
                    ->with('error', 'Cette séance a été annulée.');
            }

            // Generate JWT for JaaS
            // Room Name must be the part after the last slash
            $roomName = basename($session->meeting_link);

            // L'organisation a les droits de mentor (animateur)
            $jwt = $this->jitsiService->generateToken($user, $roomName, $isMentor || $isOrganizationMember);

            // Update Link to use 8x8 JaaS domain
            // Format: https://8x8.vc/<AppID>/<RoomName>
            $appId = env('JAAS_APP_ID');
            $meetingLink = "https://8x8.vc/{$appId}/{$roomName}";

            return view('common.meeting.show', compact('session', 'meetingLink', 'jwt', 'isMentor', 'appId', 'roomName', 'user'));
        } catch (\Throwable $e) {
            Log::error('MeetingController Error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Accès pour les invités (Bypass Auth via Guest Token)
     */
    public function showGuest(Request $request, $meetingId)
    {
        $guestValidated = $request->validate(['guestToken' => 'nullable|string|max:500']);
        $guestToken = $guestValidated['guestToken'] ?? null;

        try {
            // 1. Trouver la session par le room name (meetingId)
            $session = MentoringSession::where('meeting_link', 'LIKE', '%'.$meetingId)
                ->where('guest_token', $guestToken)
                ->firstOrFail();

            // 2. Vérifier l'autorisation en session (posée par GuestAccessController)
            $guestAuth = session("guest_auth_{$session->id}");
            $mentorEmails = $session->all_mentors->pluck('email')->map(fn ($e) => strtolower($e))->toArray();

            // Ajouter membres organisation
            $orgEmails = User::where('organization_id', $session->scheduled_by_organization_id)
                ->pluck('email')
                ->map(fn ($e) => strtolower($e))
                ->toArray();

            $allowedEmails = array_merge($mentorEmails, $orgEmails);

            if (! $guestAuth || ! in_array(strtolower($guestAuth['email']), $allowedEmails)) {
                return redirect()->route('guest.sessions.confirm', ['session' => $session, 'token' => $guestToken])
                    ->with('error', 'Veuillez confirmer votre identité pour accéder à la séance.');
            }

            // 3. Ici, on simule l'utilisateur mentor (celui qui est authentifié par sa session)
            $user = User::where('email', $guestAuth['email'])->first();
            $isMentor = true;

            // Generate JWT for JaaS
            $roomName = basename($session->meeting_link);
            $jwt = $this->jitsiService->generateToken($user, $roomName, $isMentor);

            $appId = env('JAAS_APP_ID');
            $meetingLink = "https://8x8.vc/{$appId}/{$roomName}";

            return view('common.meeting.show', compact('session', 'meetingLink', 'jwt', 'isMentor', 'appId', 'roomName', 'user'));

        } catch (\Throwable $e) {
            Log::error('MeetingController showGuest Error: '.$e->getMessage());
            abort(403, 'Accès non autorisé à cette séance.');
        }
    }
}
