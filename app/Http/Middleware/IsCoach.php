<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCoach
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_coach) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux coachs.'], 403);
            }
            abort(403, 'Accès réservé aux coachs.');
        }

        return $next($request);
    }
}
