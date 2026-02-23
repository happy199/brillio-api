<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (! $user || $user->user_type !== 'organization') {
            abort(403, 'Accès non autorisé.');
        }

        $userRole = $user->organization_role ?? 'owner'; // Default to owner for legacy accounts

        // Admin/Owner bypass: if no specific roles were requested, or if they are owner
        if (empty($roles) || $userRole === 'owner') {
            return $next($request);
        }

        if (! in_array($userRole, $roles)) {
            abort(403, 'Vous n\'avez pas les permissions nécessaires pour accéder à cette section.');
        }

        return $next($request);
    }
}
