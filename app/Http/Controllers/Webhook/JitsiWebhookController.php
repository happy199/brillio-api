<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Services\VideoRecordingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JitsiWebhookController extends Controller
{
    /**
     * Handle JaaS Webhook events
     */
    public function handle(Request $request)
    {
        // Vérification de la sécurité obligatoire
        $secret = config('services.jitsi.webhook_secret');
        if (empty($secret) || $request->header('Authorization') !== $secret) {
            Log::warning('Jitsi Webhook: Unauthorized access attempt', [
                'provided' => $request->header('Authorization'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'unauthorized'], 401);
        }

        $validated = $request->validate([
            'eventType' => 'nullable|string|max:255',
            'fqn' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'data.preAuthenicatedLink' => 'nullable|url|max:2048',
        ]);

        $payload = $validated;
        $eventType = $payload['eventType'] ?? null;

        Log::info('Jitsi Webhook Received', ['type' => $eventType, 'fqn' => $payload['fqn'] ?? 'N/A']);

        if ($eventType === 'TRANSCRIPTION_UPLOADED') {
            return $this->handleTranscriptionUploaded($payload);
        }

        return response()->json(['status' => 'ignored']);
    }

    /**
     * Handle final transcription upload event
     */
    private function handleTranscriptionUploaded(array $payload)
    {
        $fqn = $payload['fqn'] ?? '';
        $parts = explode('/', $fqn);
        $roomName = end($parts);

        if (empty($roomName)) {
            Log::error('Jitsi Webhook: No room name found in FQN', ['fqn' => $fqn]);

            return response()->json(['status' => 'error', 'message' => 'No room name'], 400);
        }

        $session = MentoringSession::where('meeting_link', 'LIKE', '%'.$roomName)->first();

        if (! $session) {
            Log::warning('Jitsi Webhook: No session found for room', ['roomName' => $roomName]);

            return response()->json(['status' => 'not_found']);
        }

        $transcriptionUrl = $payload['data']['preAuthenicatedLink'] ?? null;

        if (! $transcriptionUrl) {
            Log::error('Jitsi Webhook: No transcription URL in payload');

            return response()->json(['status' => 'error', 'message' => 'No URL'], 400);
        }

        // SSRF protection: validate URL scheme, host and ensure IP is public
        $parsedUrl = parse_url($transcriptionUrl);
        $scheme = strtolower($parsedUrl['scheme'] ?? '');
        $host = $parsedUrl['host'] ?? '';

        if (! in_array($scheme, ['http', 'https'], true) || empty($host)) {
            Log::error('Jitsi Webhook: Invalid transcription URL scheme or host', ['url' => $transcriptionUrl]);

            return response()->json(['status' => 'error', 'message' => 'Invalid URL scheme or host'], 400);
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            Log::error('Jitsi Webhook: Blocked SSRF attempt to private/reserved IP', [
                'url' => $transcriptionUrl,
                'resolved_ip' => $ip,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Access to private or reserved IP address is forbidden'], 403);
        }

        try {
            // Download the transcription file
            $response = Http::timeout(15)->get($transcriptionUrl);

            if ($response->successful()) {
                $content = $response->body();

                // Store file locally
                $fileName = 'transcriptions/session_'.$session->id.'_'.time().'.txt';
                Storage::disk('local')->put($fileName, $content);

                // Try to parse raw content if it's JSON (often Jitsi transcripts can be JSON segments)
                $rawSegments = json_decode($content, true);

                $session->update([
                    'transcription_raw' => $rawSegments ?: $content,
                    'has_transcription' => true,
                    'transcription_file_path' => $fileName,
                ]);

                Log::info('Jitsi Webhook: Transcription saved for session', ['session_id' => $session->id]);
            } else {
                Log::error('Jitsi Webhook: Failed to download transcription', [
                    'status' => $response->status(),
                    'url' => $transcriptionUrl,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Jitsi Webhook: Error processing transcription', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Termine et enregistre les données de transcription via l'API Web Speech (Client-side)
     */
    public function appendTranscription(Request $request, MentoringSession $session)
    {
        if (! $this->isParticipantAuthorized($session)) {
            return response()->json(['error' => 'Non autorisé à modifier cette séance.'], 403);
        }

        $request->validate([
            'text' => 'required|string',
            'speaker' => 'required|string',
            'timestamp' => 'required|numeric',
        ]);

        Log::info('Transcription Fragment Received', [
            'session_id' => $session->id,
            'speaker' => $request->speaker,
            'text' => $request->text,
        ]);

        $currentTranscription = $session->transcription_raw ?: [];

        // Si c'était du texte brut (ancien format), on convertit en tableau
        if (! is_array($currentTranscription)) {
            $currentTranscription = [['text' => (string) $currentTranscription, 'speaker' => 'Système', 'timestamp' => time()]];
        }

        // Éviter les doublons quasi-identiques (ex: deux participants captant la même phrase)
        $lastFragment = end($currentTranscription);
        if ($lastFragment && $lastFragment['text'] === $request->text && (time() - $lastFragment['timestamp'] < 3)) {
            return response()->json(['status' => 'ignored_duplicate']);
        }

        // Ajouter le nouveau fragment
        $currentTranscription[] = [
            'speaker' => $request->speaker,
            'text' => $request->text,
            'timestamp' => $request->timestamp,
        ];

        $session->update([
            'transcription_raw' => $currentTranscription,
            'has_transcription' => true,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Enregistrer la vidéo de la séance de mentorat
     */
    public function uploadRecording(Request $request, MentoringSession $session)
    {
        if (! $this->isParticipantAuthorized($session)) {
            return response()->json(['error' => 'Non autorisé à modifier cette séance.'], 403);
        }

        $url = app(VideoRecordingService::class)->processUploadRequest($request, 'mentoring_session_'.$session->id);

        if (! $url) {
            return response()->json(['error' => 'Aucun fichier vidéo transmis.'], 400);
        }

        $session->update([
            'video_recording_url' => $url,
        ]);

        return response()->json(['status' => 'success', 'url' => $url]);
    }

    /**
     * Vérifie si l'utilisateur connecté ou l'invité en session est autorisé
     */
    private function isParticipantAuthorized(MentoringSession $session): bool
    {
        $user = Auth::user();
        if ($user) {
            $isMentor = $session->all_mentors->pluck('id')->contains($user->id);
            $isMentee = $session->mentees()->where('user_id', $user->id)->exists();
            $isOrgMember = $user->organization_id === $session->scheduled_by_organization_id ||
                $session->mentees()->where('sponsored_by_organization_id', $user->organization_id)->exists();

            if ($isMentor || $isMentee || $isOrgMember) {
                return true;
            }
        }

        $guestAuth = session("guest_auth_{$session->id}");
        if ($guestAuth && ! empty($guestAuth['email'])) {
            $guestEmail = strtolower($guestAuth['email']);
            $mentorEmails = $session->all_mentors->pluck('email')->map(fn ($e) => strtolower($e))->toArray();

            return in_array($guestEmail, $mentorEmails, true);
        }

        return false;
    }
}
