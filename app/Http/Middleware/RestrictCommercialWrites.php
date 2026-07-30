<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictCommercialWrites
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isCommercial() && ! $user->isAdmin()) {
            if (! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                // Allow specific routes for commercials
                $allowedRoutes = [
                    'admin.login',
                    'admin.login.post',
                    'admin.logout',
                    'admin.2fa.enable',
                    'admin.2fa.verify',
                    'admin.2fa.disable',
                    'admin.commercials.take_charge', // We'll name our route this
                    'admin.commercials.end_charge',
                ];

                $currentRouteName = $request->route() ? $request->route()->getName() : null;

                if (! in_array($currentRouteName, $allowedRoutes)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['message' => 'Action non autorisée. Les comptes commerciaux sont en lecture seule pour cette section.', 'success' => false], 403);
                    }

                    return back()->with('error', 'Action non autorisée. Les comptes commerciaux sont en lecture seule pour cette section.');
                }
            }
        }

        return $next($request);
    }
}
