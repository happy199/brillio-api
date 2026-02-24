<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizationByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Configured base domain (fallback to brillio.africa)
        $baseDomain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'brillio.africa';
        if (!$baseDomain) {
            $baseDomain = 'brillio.africa';
        }

        // Remove 'www.' if present
        $host = str_replace('www.', '', $host);

        $organization = null;

        // 1. Is it a subdomain? (e.g., orgname.brillio.africa)
        if ($host !== $baseDomain && str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            $organization = Organization::active()
                ->where('slug', $subdomain)
                ->orWhere('custom_domain', $host)
                ->first();
        }
        // 2. Is it a completely custom domain mapping? (e.g., members.myorg.com)
        elseif ($host !== $baseDomain && $host !== 'localhost' && $host !== '127.0.0.1') {
            $organization = Organization::active()->where('custom_domain', $host)->first();
        }

        if ($organization) {
            // Bind to service container (can be resolved anywhere via app('current_organization'))
            app()->instance('current_organization', $organization);

            // Share with all Blade views directly
            View::share('current_organization', $organization);
        }

        return $next($request);
    }
}