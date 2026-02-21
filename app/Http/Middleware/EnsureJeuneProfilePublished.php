<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJeuneProfilePublished
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si c'est un jeune et que son profil n'est pas public
        if ($user && $user->isJeune() && (!$user->jeuneProfile || !$user->jeuneProfile->is_public)) {
            // Si c'est une requête AJAX, on renvoie une erreur JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Profil non public.'], 403);
            }

            // Sinon on redirige vers la page de verrouillage
            return redirect()->route('jeune.mentorship.locked');
        }

        return $next($request);
    }
}