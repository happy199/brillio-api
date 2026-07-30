<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrCommercial
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isCommercial())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Vous n\'avez pas accès à cette section.');
        }

        return $next($request);
    }
}
