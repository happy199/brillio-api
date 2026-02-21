<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOrganization
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user is an organization
     * If not, redirect to home with error message
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('organization.login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        if (auth()->user()->user_type !== 'organization') {
            abort(403, 'Accès refusé. Cette section est réservée aux organisations partenaires.');
        }

        // Email Verification check for Organizations
        if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&
        !auth()->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Votre adresse email n\'est pas vérifiée.')
                : redirect()->route('organization.verification.notice');
        }

        return $next($request);
    }
}