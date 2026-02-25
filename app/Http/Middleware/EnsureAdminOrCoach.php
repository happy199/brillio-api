<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrCoach
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || (! $request->user()->isAdmin() && ! $request->user()->isCoach())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès restreint.',
                ], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Accès restreint.');
        }

        return $next($request);
    }
}
