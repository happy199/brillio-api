namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
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
        // Vérification de la sécurité si un secret est configuré
        $secret = config('services.jitsi.webhook_secret');
        if ($secret && $request->header('Authorization') !== $secret) {
            Log::warning('Jitsi Webhook: Unauthorized access attempt', [
                'provided' => $request->header('Authorization'),
                'ip' => $request->ip()
            ]);
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $payload = $request->all();
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

        $session = MentoringSession::where('meeting_link', 'LIKE', '%' . $roomName)->first();

        if (!$session) {
            Log::warning('Jitsi Webhook: No session found for room', ['roomName' => $roomName]);
            return response()->json(['status' => 'not_found']);
        }

        $transcriptionUrl = $payload['data']['preAuthenicatedLink'] ?? null;

        if (!$transcriptionUrl) {
            Log::error('Jitsi Webhook: No transcription URL in payload');
            return response()->json(['status' => 'error', 'message' => 'No URL'], 400);
        }

        try {
            // Download the transcription file
            $response = Http::get($transcriptionUrl);

            if ($response->successful()) {
                $content = $response->body();
                
                // Store file locally
                $fileName = 'transcriptions/session_' . $session->id . '_' . time() . '.txt';
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
                    'url' => $transcriptionUrl
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Jitsi Webhook: Error processing transcription', [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
