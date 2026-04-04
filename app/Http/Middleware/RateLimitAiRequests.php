<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * RateLimitAiRequests - Rate limiting middleware for AI/chat endpoints.
 * 
 * Prevents abuse of AI resources by limiting the number of requests
 * per user/tenant within a time window.
 */
class RateLimitAiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveKey($request);
        $limit = $this->resolveLimit($request);

        if (RateLimiter::tooManyAttempts($key, $limit->maxAttempts)) {
            return $this->buildTooManyResponse($key, $limit);
        }

        RateLimiter::hit($key, $limit->decaySeconds);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $limit->maxAttempts,
            $this->calculateRemaining($key, $limit->maxAttempts),
        );
    }

    /**
     * Resolve the rate limit key based on user or IP.
     */
    protected function resolveKey(Request $request): string
    {
        // Prioritize user-based limiting
        if ($request->user()) {
            return "ai:user:{$request->user()->id}";
        }

        // Fallback to IP-based limiting for unauthenticated requests
        return "ai:ip:{$request->ip()}";
    }

    /**
     * Resolve the rate limit based on tenant plan.
     */
    protected function resolveLimit(Request $request): Limit
    {
        $planMultiplier = $this->getPlanMultiplier($request);

        // Base limits per minute
        $baseLimit = 20; // Conservative default for AI requests

        return Limit::perMinute((int) ($baseLimit * $planMultiplier));
    }

    /**
     * Get plan-based multiplier for rate limits.
     */
    protected function getPlanMultiplier(Request $request): float
    {
        $tenant = null;

        if ($request->user()?->tenant) {
            $tenant = $request->user()->tenant;
        }

        if (!$tenant) {
            return 1.0;
        }

        return match ($tenant->plan) {
            'starter' => 1.0,
            'basic' => 1.5,
            'business' => 2.0,
            'professional' => 3.0,
            'pro' => 3.0,
            'enterprise' => 5.0,
            default => 0.5, // trial - very limited
        };
    }

    /**
     * Calculate remaining attempts.
     */
    protected function calculateRemaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - RateLimiter::attempts($key));
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remaining): Response
    {
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    /**
     * Build response for too many requests.
     */
    protected function buildTooManyResponse(string $key, Limit $limit): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'error' => 'rate_limit_exceeded',
            'message' => "Terlalu banyak permintaan AI. Silakan tunggu {$retryAfter} detik sebelum mencoba lagi.",
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) $limit->maxAttempts,
            'X-RateLimit-Remaining' => '0',
        ]);
    }
}
