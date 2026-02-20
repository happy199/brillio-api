<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $plan = 'pro'): Response
    {
        $user = $request->user();

        if (!$user || !$user->isOrganization() || !$user->organization) {
            return $next($request);
        }

        $organization = $user->organization;

        // Determine required level
        $requiredLevel = 0;
        if ($plan === Organization::PLAN_PRO) {
            $requiredLevel = 1;
        }
        elseif ($plan === Organization::PLAN_ENTERPRISE) {
            $requiredLevel = 2;
        }

        // Determine current level
        $currentLevel = 0;
        if ($organization->isEnterprise()) {
            $currentLevel = 2;
        }
        elseif ($organization->isPro()) {
            $currentLevel = 1;
        }

        if ($currentLevel < $requiredLevel) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Subscription upgrade required.'], 403);
            }

            return redirect()->route('organization.subscriptions.index')
                ->with('error', 'Cette fonctionnalité nécessite un abonnement ' . ucfirst($plan) . '.');
        }

        return $next($request);
    }
}