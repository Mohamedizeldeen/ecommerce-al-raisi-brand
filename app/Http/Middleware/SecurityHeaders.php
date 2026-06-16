<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers. The CSP is deliberately balanced: Alpine.js evaluates
     * expressions at runtime, so 'unsafe-eval'/'unsafe-inline' are required for
     * scripts — but we still lock down object-src, base-uri, frame-ancestors and
     * form-action, which blocks the highest-value injection/clickjacking vectors.
     * CSP is skipped in local dev so it doesn't fight the Vite HMR server.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if (! app()->environment('local') && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net data:",
                "img-src 'self' data: https:",
                "connect-src 'self'",
                "frame-src 'self' https://www.youtube.com https://player.vimeo.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]));
        }

        return $response;
    }
}
