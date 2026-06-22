<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour restreindre l'accès à la documentation Swagger API en production/staging.
 */
class RestrictSwaggerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Autoriser en environnement local ou de test pour faciliter le développement
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        // En staging ou production, l'utilisateur doit être connecté et être administrateur
        if ($request->user() && $request->user()->isAdmin()) {
            return $next($request);
        }

        // Si la requête attend du JSON (pour la spec brute)
        if ($request->expectsJson() || $request->is('docs/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Réservé aux administrateurs connectés.',
            ], 403);
        }

        // Sinon, rediriger vers la page de login admin
        return redirect()->route('admin.login')->with('error', 'Vous devez être administrateur pour accéder à la documentation API.');
    }
}
