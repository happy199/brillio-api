<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMentorProfilePublished
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si pas de profil mentor ou pas publié
        if (! $user || ! $user->mentorProfile || ! $user->mentorProfile->is_published) {
            // Si c'est une requête AJAX, on renvoie une erreur JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Profil mentor non publié.'], 403);
            }

            // Sinon on redirige vers la page de verrouillage
            return redirect()->route('mentor.mentorship.locked');
        }

        return $next($request);
    }
}
