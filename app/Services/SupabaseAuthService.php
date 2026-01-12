<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'authentification via Supabase
 * Gère OAuth (Google, Facebook, LinkedIn) et email/password
 */
class SupabaseAuthService
{
    protected string $supabaseUrl;
    protected string $anonKey;
    protected string $serviceRoleKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->anonKey = config('services.supabase.anon_key');
        $this->serviceRoleKey = config('services.supabase.service_role_key');
    }

    /**
     * Génère l'URL d'authentification OAuth avec PKCE
     */
    public function getOAuthUrl(string $provider, string $redirectTo, array $scopes = []): string
    {
        $params = [
            'provider' => $provider,
            'redirect_to' => $redirectTo,
        ];

        if (!empty($scopes)) {
            $params['scopes'] = implode(' ', $scopes);
        }

        return "{$this->supabaseUrl}/auth/v1/authorize?" . http_build_query($params);
    }

    /**
     * Échange le code OAuth contre un token (PKCE flow)
     */
    public function exchangeCodeForSession(string $code): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/token?grant_type=authorization_code", [
                'auth_code' => $code,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase OAuth exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase OAuth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère un utilisateur par son ID Supabase (admin API)
     */
    public function getUserById(string $userId): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->serviceRoleKey,
                'Authorization' => "Bearer {$this->serviceRoleKey}",
            ])->get("{$this->supabaseUrl}/auth/v1/admin/users/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase getUserById failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase getUserById exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Liste tous les utilisateurs (pour recherche par email)
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->serviceRoleKey,
                'Authorization' => "Bearer {$this->serviceRoleKey}",
            ])->get("{$this->supabaseUrl}/auth/v1/admin/users");

            if ($response->successful()) {
                $data = $response->json();
                $users = $data['users'] ?? $data;

                foreach ($users as $user) {
                    if (($user['email'] ?? '') === $email) {
                        return $user;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase getUserByEmail exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Inscription par email/password
     */
    public function signUpWithEmail(string $email, string $password, array $metadata = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/signup", [
                'email' => $email,
                'password' => $password,
                'data' => $metadata,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase signup failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase signup exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Connexion par email/password
     */
    public function signInWithEmail(string $email, string $password): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/token?grant_type=password", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase signin exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupère les informations utilisateur depuis le token
     */
    public function getUser(string $accessToken): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Authorization' => "Bearer {$accessToken}",
            ])->get("{$this->supabaseUrl}/auth/v1/user");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase getUser failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase getUser exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Déconnexion
     */
    public function signOut(string $accessToken): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Authorization' => "Bearer {$accessToken}",
            ])->post("{$this->supabaseUrl}/auth/v1/logout");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Supabase signOut exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Rafraîchit le token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/token?grant_type=refresh_token", [
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Supabase refresh exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail(string $email, string $redirectTo): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->supabaseUrl}/auth/v1/recover", [
                'email' => $email,
                'redirect_to' => $redirectTo,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Supabase password reset exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Récupère les données LinkedIn depuis le profil OAuth
     */
    public function extractLinkedInData(array $userData): array
    {
        $identities = $userData['identities'] ?? [];
        $linkedinIdentity = collect($identities)->firstWhere('provider', 'linkedin_oidc');

        $metadata = $userData['user_metadata'] ?? $userData['raw_user_meta_data'] ?? [];

        return [
            'linkedin_id' => $linkedinIdentity['id'] ?? $userData['id'] ?? null,
            'name' => $metadata['full_name'] ?? $metadata['name'] ?? null,
            'email' => $userData['email'] ?? null,
            'avatar_url' => $metadata['avatar_url'] ?? $metadata['picture'] ?? null,
            'raw_data' => $metadata,
        ];
    }

    /**
     * Récupère les données Google/Facebook depuis le profil OAuth
     */
    public function extractSocialData(array $userData): array
    {
        $appMetadata = $userData['app_metadata'] ?? $userData['raw_app_meta_data'] ?? [];
        $metadata = $userData['user_metadata'] ?? $userData['raw_user_meta_data'] ?? [];

        return [
            'provider' => $appMetadata['provider'] ?? 'email',
            'provider_id' => $metadata['provider_id'] ?? $metadata['sub'] ?? $userData['id'] ?? null,
            'name' => $metadata['full_name'] ?? $metadata['name'] ?? null,
            'email' => $userData['email'] ?? null,
            'avatar_url' => $metadata['avatar_url'] ?? $metadata['picture'] ?? null,
            'email_verified' => ($userData['email_confirmed_at'] ?? $userData['confirmed_at'] ?? null) !== null
                              || ($metadata['email_verified'] ?? false),
        ];
    }
}
