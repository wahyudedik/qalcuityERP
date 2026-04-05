<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AddSecurityHeaders - Add security headers including Content Security Policy (CSP).
 * 
 * Helps prevent XSS attacks by controlling which resources the browser is allowed to load.
 */
class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP)
        // This is the main defense against XSS
        $cspPolicy = $this->buildCspPolicy();
        $response->headers->set('Content-Security-Policy', $cspPolicy);

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        return $response;
    }

    /**
     * Build Content Security Policy string.
     */
    protected function buildCspPolicy(): string
    {
        // Check if in development mode (Vite running on localhost:5173)
        $isDevelopment = config('app.debug', false);

        // Add Vite dev server to allowed sources in development
        $viteDevSrc = $isDevelopment ? ' http://localhost:5173 ws://localhost:5173' : '';

        // Define allowed sources for different content types
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com{$viteDevSrc}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net{$viteDevSrc}",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.gemini.google.com https://cdn.jsdelivr.net{$viteDevSrc}",
            "worker-src 'self' blob:",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        return implode('; ', $policies);
    }
}
