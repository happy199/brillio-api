<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a random nonce for this request
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        Vite::useCspNonce($nonce);

        $response = $next($request);

        // Content Security Policy (CSP)
        $csp = "default-src 'self'; ";
        // script-src: added 'nonce-$nonce' and kept external providers.
        // Note: added 'unsafe-eval' only where strictly necessary for Alpine/Tailwind Play
        $csp .= "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://www.googletagmanager.com https://www.google-analytics.com https://www.clarity.ms https://cdn.mxpnl.com https://cdn.tailwindcss.com https://unpkg.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; ";
        $csp .= "img-src 'self' data: https: https://www.clarity.ms; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com data:; ";
        $csp .= "frame-ancestors 'self'; ";
        $csp .= "form-action 'self'; ";
        $csp .= "connect-src 'self' https://www.google-analytics.com https://*.clarity.ms https://c.bing.com https://api.mixpanel.com https://8x8.vc https://*.8x8.vc wss://8x8.vc wss://*.8x8.vc https://api.amplitude.com https://api2.amplitude.com; ";
        $csp .= "frame-src 'self' https://8x8.vc https://*.8x8.vc; ";
        $csp .= "base-uri 'self'; ";
        $csp .= "object-src 'none';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Security headers
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Permissions-Policy', 'camera=*, microphone=*, geolocation=(), browsing-topics=()');

        return $response;
    }
}
