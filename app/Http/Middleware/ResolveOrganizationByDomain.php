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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Configured base domain (fallback to brillio.africa)
        $baseDomain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'brillio.africa';
        if (! $baseDomain) {
            $baseDomain = 'brillio.africa';
        }

        // Remove 'www.' if present
        $host = str_replace('www.', '', $host);

        $organization = null;

        // 1. Is it a subdomain? (e.g., orgname.brillio.africa)
        $isSubdomain = ($host !== $baseDomain && str_ends_with($host, '.'.$baseDomain))
            || ($host !== 'brillio.africa' && str_ends_with($host, '.brillio.africa'));

        if ($isSubdomain) {
            $cleaned = str_ends_with($host, '.'.$baseDomain)
                ? str_replace('.'.$baseDomain, '', $host)
                : str_replace('.brillio.africa', '', $host);

            // In case of nested/repeated subdomains in tests or proxies, use the first segment as slug
            $subdomainParts = explode('.', $cleaned);
            $subdomain = $cleaned;
            $slugCandidate = $subdomainParts[0];

            // Ignorer les sous-domaines techniques réservés
            $reservedSubdomains = ['api', 'admin', 'mail', 'staging', 'dev'];
            if (! in_array($slugCandidate, $reservedSubdomains, true)) {
                $organization = Organization::active()
                    ->where(function ($query) use ($subdomain, $slugCandidate, $host) {
                        $query->where('slug', $subdomain)
                            ->orWhere('slug', $slugCandidate)
                            ->orWhere('custom_domain', $host);
                    })
                    ->first();

                // Si le sous-domaine n'appartient à aucune organisation active, bloquer pour éviter l'abus de wildcard
                if (! $organization) {
                    abort(404, 'Organisation introuvable ou inactive.');
                }
            }
        }
        // 2. Is it a completely custom domain mapping? (e.g., members.myorg.com)
        elseif ($host !== $baseDomain && $host !== 'brillio.africa' && $host !== 'localhost' && $host !== '127.0.0.1') {
            $organization = Organization::active()->where('custom_domain', $host)->first();
        }

        if ($organization) {
            // Bind to service container (can be resolved anywhere via app('current_organization'))
            app()->instance('current_organization', $organization);

            // Share with all Blade views directly
            View::share('current_organization', $organization);

            // Override SEO Configs for whitelabeling
            config(['seo.site.name' => $organization->name]);
            if ($organization->logo_url) {
                config(['seo.organization.logo' => $organization->logo_url]);
                config(['seo.default.image' => $organization->logo_url]);
            }
        }

        return $next($request);
    }
}
