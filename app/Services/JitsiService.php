<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

class JitsiService
{
    private $appId;
    private $keyId;
    private $privateKey;

    public function __construct()
    {
        $this->appId = env('JAAS_APP_ID');
        $this->keyId = env('JAAS_KEY_ID');
        // Handle potential newline issues in environment variable
        $this->privateKey = str_replace('\n', "\n", env('JAAS_PRIVATE_KEY'));
    }

    /**
     * Generate a signed JWT for a specific user and room
     */
    public function generateToken($user, $roomName, $isModerator = false)
    {
        $now = time();
        $exp = $now + 7200; // 2 hours validity

        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'iat' => $now,
            'exp' => $exp,
            'nbf' => $now,
            'sub' => $this->appId,
            'room' => '*', // Allow access to any room (or restrict to specific room)
            'context' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url ?? "https://ui-avatars.com/api/?name=" . urlencode($user->name),
                    'moderator' => $isModerator ? 'true' : 'false'
                ],
                'features' => [
                    'livestreaming' => 'true',
                    'recording' => 'true',
                    'transcription' => 'true',
                    'outbound-call' => 'true'
                ]
            ]
        ];

        // Header with Kid is MANDATORY for JaaS
        $headers = [
            'kid' => $this->keyId,
            'typ' => 'JWT'
        ];

        try {
            return JWT::encode($payload, $this->privateKey, 'RS256', null, $headers);
        } catch (\Exception $e) {
            Log::error("Jitsi JWT Generation Error: " . $e->getMessage());
            return null;
        }
    }
}
