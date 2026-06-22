<?php

namespace App\Http\Middleware;

use App\Models\UserLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Update last_login_at if it's null or more than 1 hour ago
            // to avoid hitting the DB on every single request
            if (! $user->last_login_at || $user->last_login_at->diffInHours(now()) >= 1) {
                $user->update(['last_login_at' => now()]);
            }

            // Track unique daily login for statistics
            UserLogin::firstOrCreate([
                'user_id' => $user->id,
                'login_date' => now()->toDateString(),
            ], [
                'organization_id' => $user->sponsored_by_organization_id ?? $user->organization_id,
            ]);
        }

        return $next($request);
    }
}
