<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected $apiToken;

    protected $zoneId;

    protected $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
        $this->zoneId = config('services.cloudflare.zone_id');
    }

    /**
     * Register a custom hostname for SSL for SaaS.
     */
    public function registerCustomHostname($domain)
    {
        if (empty($this->apiToken) || empty($this->zoneId)) {
            Log::error('Cloudflare configuration missing (API Token or Zone ID).');

            return [
                'success' => false,
                'message' => 'Configuration Cloudflare manquante côté serveur.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/zones/{$this->zoneId}/custom_hostnames", [
                'hostname' => $domain,
                'ssl' => [
                    'method' => 'http',
                    'type' => 'dv',
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Domaine enregistré avec succès sur Cloudflare.',
                ];
            }

            // Check if already exists (Conflict 409)
            if ($response->status() === 409) {
                return [
                    'success' => true,
                    'message' => 'Ce domaine est déjà configuré sur Cloudflare.',
                ];
            }

            Log::error('Cloudflare API Error: '.$response->body());

            $error = $response->json('errors.0.message') ?? 'Erreur inconnue de l\'API Cloudflare';

            return [
                'success' => false,
                'message' => 'Erreur Cloudflare : '.$error,
            ];

        } catch (\Exception $e) {
            Log::error('Cloudflare Exception: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la communication avec Cloudflare.',
            ];
        }
    }
}
