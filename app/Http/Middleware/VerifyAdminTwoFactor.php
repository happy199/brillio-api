<?php

namespace App\Http\Middleware;

class VerifyAdminTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $user = $request->user();

        // Si l'utilisateur est admin et que le 2FA est activé ET confirmé
        if ($user && $user->isAdmin() && $user->two_factor_secret && $user->two_factor_confirmed_at) {
            if (!$request->session()->has('admin_2fa_verified')) {
                return redirect()->route('admin.two_factor.index');
            }
        }

        return $next($request);
    }
}
