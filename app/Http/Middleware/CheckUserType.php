<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('auth.choice');
        }

        if ($user->user_type !== $type) {
            // Rediriger vers le bon espace
            if ($user->user_type === 'jeune') {
                return redirect()->route('jeune.dashboard');
            } elseif ($user->user_type === 'mentor') {
                return redirect()->route('mentor.dashboard');
            } elseif ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->user_type === 'organization') {
                return redirect()->route('organization.dashboard');
            }

            abort(403, 'Acces non autorise.');
        }

        return $next($request);
    }
}
