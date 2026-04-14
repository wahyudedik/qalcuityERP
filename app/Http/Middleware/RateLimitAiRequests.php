<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RateLimitAiRequests — Per-tenant rate limiting for AI/chat endpoints.
 *
 * Limits AI requests to 60 per minute per tenant, preventing a single tenant
 * from exhausting the shared AI quota.
 *
 * Bug 1.28 Fix: Rate limiter is now keyed by tenant_id instead of globally,
 * so each tenant has its own independent 60 req/min bucket.
 */
class RateLimitAiRequests
{
    public function __construct(private RateLimiter $limiter) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = auth()->user()?->tenant_id;
        $key = "ai_requests:{$tenantId}";

        if ($this->limiter->tooManyAttempts($key, 60)) {
            return response()->json([
                'error' => 'Too many AI requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, 60); // 60 second window

        return $next($request);
    }
}
