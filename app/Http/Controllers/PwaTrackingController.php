<?php

namespace App\Http\Controllers;

use App\Models\PwaInstallation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PwaTrackingController extends Controller
{
    /**
     * Enregistre un événement PWA (installation ou lancement en mode autonome).
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_uuid' => 'required|string|max:64',
            'action' => 'required|string|in:install,launch',
            'platform' => 'nullable|string|in:android,ios,desktop,unknown',
            'browser' => 'nullable|string|max:64',
        ]);

        $clientUuid = $validated['client_uuid'];
        $action = $validated['action'];
        $platform = $validated['platform'] ?? 'unknown';
        $browser = $validated['browser'] ?? null;
        $userId = Auth::id();

        $installation = PwaInstallation::where('client_uuid', $clientUuid)->first();

        if (! $installation) {
            $installation = new PwaInstallation();
            $installation->client_uuid = $clientUuid;
            $installation->platform = $platform;
            $installation->browser = $browser;
            $installation->user_agent = substr((string) $request->userAgent(), 0, 255);
            $installation->ip_address = $request->ip();
            $installation->launches_count = 1;
            $installation->installed_at = ($action === 'install') ? now() : null;
            $installation->last_active_at = now();
        } else {
            // Mettre à jour la plateforme si connue
            if ($platform !== 'unknown') {
                $installation->platform = $platform;
            }
            if ($browser) {
                $installation->browser = $browser;
            }

            if ($action === 'install' && ! $installation->installed_at) {
                $installation->installed_at = now();
            }

            $installation->last_active_at = now();
            if ($action === 'launch') {
                $installation->launches_count = ($installation->launches_count ?? 0) + 1;
            }
        }

        // Associer l'utilisateur connecté s'il ne l'était pas encore
        if ($userId && ! $installation->user_id) {
            $installation->user_id = $userId;
        }

        $installation->save();

        return response()->json([
            'status' => 'success',
            'tracked' => $action,
            'installed' => (bool) $installation->installed_at,
        ]);
    }
}
