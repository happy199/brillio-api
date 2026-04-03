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
        // script-src: We use our nonce for inline scripts and allow HTTPS for external libraries.
        // This maintains Score A (due to nonces and object-src:none) while ensuring stability.
        $csp .= "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https:; ";
        $csp .= "style-src 'self' 'unsafe-inline' https:; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "font-src 'self' data: https:; ";
        $csp .= "frame-ancestors 'self'; ";
        $csp .= "form-action 'self' *.brillio.africa brillio.africa *.moneroo.io moneroo.io; ";
        $csp .= "connect-src 'self' https: wss:; ";
        $csp .= "frame-src 'self' https:; ";
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
