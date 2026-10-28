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
        $host = str_replace('www.', '', $request->getHost());

        $baseDomain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'brillio.africa';
        $baseDomain = $baseDomain ?: 'brillio.africa';

        $organization = $this->resolveSubdomain($host, $baseDomain)
            ?? $this->resolveCustomDomain($host, $baseDomain);

        if ($organization) {
            $this->applyOrganizationContext($organization);
        }

        return $next($request);
    }

    /**
     * Résout l'organisation si le host est un sous-domaine
     */
    private function resolveSubdomain(string $host, string $baseDomain): ?Organization
    {
        $isSubdomain = ($host !== $baseDomain && str_ends_with($host, '.'.$baseDomain))
            || ($host !== 'brillio.africa' && str_ends_with($host, '.brillio.africa'));

        if (! $isSubdomain) {
            return null;
        }

        $cleaned = str_ends_with($host, '.'.$baseDomain)
            ? str_replace('.'.$baseDomain, '', $host)
            : str_replace('.brillio.africa', '', $host);

        $subdomainParts = explode('.', $cleaned);
        $slugCandidate = $subdomainParts[0];

        $reservedSubdomains = ['api', 'admin', 'mail', 'staging', 'dev'];
        if (in_array($slugCandidate, $reservedSubdomains, true)) {
            return null;
        }

        $organization = Organization::active()
            ->where(function ($query) use ($cleaned, $slugCandidate, $host) {
                $query->where('slug', $cleaned)
                    ->orWhere('slug', $slugCandidate)
                    ->orWhere('custom_domain', $host);
            })
            ->first();

        if (! $organization) {
            abort(404, 'Organisation introuvable ou inactive.');
        }

        return $organization;
    }

    /**
     * Résout l'organisation si le host est un domaine personnalisé externe
     */
    private function resolveCustomDomain(string $host, string $baseDomain): ?Organization
    {
        $excluded = [$baseDomain, 'brillio.africa', 'localhost', '127.0.0.1'];
        if (in_array($host, $excluded, true)) {
            return null;
        }

        return Organization::active()->where('custom_domain', $host)->first();
    }

    /**
     * Injecte le contexte de l'organisation dans le conteneur, les vues et la config SEO
     */
    private function applyOrganizationContext(Organization $organization): void
    {
        app()->instance('current_organization', $organization);
        View::share('current_organization', $organization);

        config(['seo.site.name' => $organization->name]);
        if ($organization->logo_url) {
            config(['seo.organization.logo' => $organization->logo_url]);
            config(['seo.default.image' => $organization->logo_url]);
        }
    }
}
