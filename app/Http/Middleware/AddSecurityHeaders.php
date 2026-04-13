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
        $response->headers->set('X-Frame-Options', 'DENY');

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
     *
     * CATATAN: Alpine.js v3 menggunakan new Function() / eval() secara internal
     * untuk mengevaluasi ekspresi di x-data, x-show, @click, :class, dll.
     * Ini adalah arsitektur Alpine v3 yang tidak bisa dihindari — 'unsafe-eval'
     * WAJIB ada agar Alpine berfungsi. XSS tetap dicegah via:
     * - Output escaping di Blade ({{ }} auto-escape)
     * - DOMPurify untuk konten AI/user-generated
     * - OutputEscaper service untuk semua output dinamis
     * - Tidak ada user input yang dirender sebagai Alpine expression
     *
     * Referensi: https://alpinejs.dev/advanced/csp
     * Alpine v4 (CSP mode) akan menghilangkan kebutuhan ini — pertimbangkan upgrade.
     */
    protected function buildCspPolicy(): string
    {
        $isDevelopment = config('app.debug', false);

        $viteDevSrc = '';
        if ($isDevelopment) {
            $appUrl = config('app.url', 'http://localhost');
            $appDomain = parse_url($appUrl, PHP_URL_HOST);
            $viteDevSrc = " http://localhost:5173 ws://localhost:5173 http://{$appDomain}:5173 ws://{$appDomain}:5173";
        }

        $policies = [
            "default-src 'self'",
            // 'unsafe-eval' diperlukan Alpine.js v3 untuk evaluasi ekspresi x-data/x-show/@click
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com{$viteDevSrc}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net{$viteDevSrc}",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://generativelanguage.googleapis.com https://cdn.jsdelivr.net{$viteDevSrc}",
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
