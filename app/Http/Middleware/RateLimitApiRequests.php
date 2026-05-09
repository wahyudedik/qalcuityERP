<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-tenant, per-endpoint rate limiting for API and high-traffic routes.
 *
 * Usage in routes:
 *   ->middleware('api.rate:api-read')
 *   ->middleware('api.rate:api-write')
 *   ->middleware('api.rate:webhook-inbound')
 *
 * Adds standard rate limit headers to every response:
 *   X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After (on 429)
 */
class RateLimitApiRequests
{
    public function handle(Request $request, Closure $next, string $limiterName = 'api-default'): Response
    {
        $key = $this->resolveKey($request, $limiterName);

        $limit = $this->resolveLimit($request, $limiterName);
        $maxAttempts = $limit->maxAttempts;
        $decaySeconds = $limit->decaySeconds;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyResponse($key, $maxAttempts, $limiterName);
        }

        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemaining($key, $maxAttempts),
        );
    }

    /**
     * Build a unique rate limit key based on tenant + endpoint group.
     */
    protected function resolveKey(Request $request, string $limiterName): string
    {
        // For API token-authenticated requests, key by tenant
        $apiToken = $request->attributes->get('api_token');
        if ($apiToken) {
            return "rl:{$limiterName}:tenant:{$apiToken->tenant_id}";
        }

        // For authenticated web users, key by user
        if ($request->user()) {
            return "rl:{$limiterName}:user:{$request->user()->id}";
        }

        // For unauthenticated (webhooks, etc.), key by IP
        return "rl:{$limiterName}:ip:{$request->ip()}";
    }

    /**
     * Resolve the rate limit based on limiter name and tenant plan.
     */
    protected function resolveLimit(Request $request, string $limiterName): Limit
    {
        // Check if tenant has a custom plan-based limit
        $planMultiplier = $this->getPlanMultiplier($request);

        return match ($limiterName) {
            'api-read' => Limit::perMinute((int) (60 * $planMultiplier)),
            'api-write' => Limit::perMinute((int) (20 * $planMultiplier)),
            'api-default' => Limit::perMinute((int) (60 * $planMultiplier)),
            'webhook-inbound' => Limit::perMinute(30),
            'webhook-test' => Limit::perMinute(5),
            'pos-checkout' => Limit::perMinute((int) (60 * $planMultiplier)),
            'export' => Limit::perMinute(10),
            'import' => Limit::perMinute(5),
            'auth' => Limit::perMinute(10),
            default => Limit::perMinute(60),
        };
    }

    /**
     * Get plan-based multiplier for rate limits.
     * Higher plans get more generous limits.
     */
    protected function getPlanMultiplier(Request $request): float
    {
        $tenant = null;

        $apiToken = $request->attributes->get('api_token');
        if ($apiToken) {
            $tenant = $apiToken->tenant;
        } elseif ($request->user()?->tenant) {
            $tenant = $request->user()->tenant;
        }

        if (! $tenant) {
            return 1.0;
        }

        return match ($tenant->plan) {
            'starter' => 1.0,
            'basic' => 1.5,
            'business' => 2.0,
            'professional' => 3.0,
            'pro' => 3.0,
            'enterprise' => 10.0,
            default => 0.5, // trial
        };
    }

    protected function calculateRemaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - RateLimiter::attempts($key));
    }

    protected function addHeaders(Response $response, int $maxAttempts, int $remaining): Response
    {
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    protected function buildTooManyResponse(string $key, int $maxAttempts, string $limiterName): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        $labels = [
            'api-read' => 'API read',
            'api-write' => 'API write',
            'api-default' => 'API',
            'webhook-inbound' => 'Webhook',
            'webhook-test' => 'Webhook test',
            'pos-checkout' => 'POS checkout',
            'export' => 'Export',
            'import' => 'Import',
            'auth' => 'Authentication',
        ];

        $label = $labels[$limiterName] ?? 'Request';

        return response()->json([
            'error' => 'rate_limit_exceeded',
            'message' => "{$label} rate limit terlampaui. Coba lagi dalam {$retryAfter} detik.",
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) $maxAttempts,
            'X-RateLimit-Remaining' => '0',
        ]);
    }
}
