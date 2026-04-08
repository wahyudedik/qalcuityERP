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
     * Resolve the rate limit based on tenant plan and request type.
     * 
     * ✅ FIX: Sekarang membedakan plan tenant dengan granular limits
     */
    protected function resolveLimit(Request $request): Limit
    {
        $planMultiplier = $this->getPlanMultiplier($request);
        $requestType = $this->getRequestType($request);

        // Base limits per minute berdasarkan jenis request
        $baseLimits = [
            'chat' => 30,           // Chat messages - lebih frequent
            'analysis' => 20,       // Data analysis - moderate
            'generation' => 10,     // Content generation - expensive
            'default' => 20,        // Default AI requests
        ];

        $baseLimit = $baseLimits[$requestType] ?? $baseLimits['default'];
        $finalLimit = (int) ($baseLimit * $planMultiplier);

        // Minimum 5 requests per minute (bahkan untuk trial)
        // Maximum 200 requests per minute (untuk enterprise)
        $finalLimit = max(5, min(200, $finalLimit));

        return Limit::perMinutes(1, $finalLimit);
    }

    /**
     * Get plan-based multiplier for rate limits.
     * 
     * ✅ IMPROVED: Lebih granular dengan semua plan tiers
     */
    protected function getPlanMultiplier(Request $request): float
    {
        $tenant = null;

        if ($request->user()?->tenant) {
            $tenant = $request->user()->tenant;
        }

        if (!$tenant) {
            return 1.0; // Default untuk unauthenticated
        }

        // Plan multipliers - semakin tinggi plan, semakin besar limit
        return match ($tenant->plan ?? 'trial') {
            // Tier 1: Free/Trial - Very limited
            'trial', 'free' => 0.5,

            // Tier 2: Starter - Basic usage
            'starter' => 1.0,

            // Tier 3: Basic - Small business
            'basic' => 1.5,

            // Tier 4: Business - Growing business
            'business' => 2.0,

            // Tier 5: Professional/Pro - Established business
            'professional', 'pro' => 3.0,

            // Tier 6: Enterprise - Large organization
            'enterprise' => 5.0,

            // Default: Conservative limit
            default => 1.0,
        };
    }

    /**
     * Determine the type of AI request.
     * 
     * Different request types have different computational costs.
     */
    protected function getRequestType(Request $request): string
    {
        // Check URL path
        $path = $request->path();

        if (str_contains($path, 'chat') || str_contains($path, 'message')) {
            return 'chat';
        }

        if (str_contains($path, 'analyze') || str_contains($path, 'insight')) {
            return 'analysis';
        }

        if (str_contains($path, 'generate') || str_contains($path, 'create')) {
            return 'generation';
        }

        // Check request payload for type hint
        $type = $request->input('type') ?? $request->input('request_type');
        if ($type) {
            return match ($type) {
                'chat', 'message', 'conversation' => 'chat',
                'analyze', 'analysis', 'report' => 'analysis',
                'generate', 'create', 'content' => 'generation',
                default => 'default',
            };
        }

        return 'default';
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
     * 
     * ✅ ADDED: Logging untuk monitoring rate limit violations
     */
    protected function buildTooManyResponse(string $key, Limit $limit): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        // Log rate limit violation untuk monitoring
        \Log::warning('AI Rate Limit Exceeded', [
            'key' => $key,
            'limit' => $limit->maxAttempts,
            'retry_after' => $retryAfter,
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id,
            'ip' => request()->ip(),
            'path' => request()->path(),
        ]);

        return response()->json([
            'error' => 'rate_limit_exceeded',
            'message' => "Terlalu banyak permintaan AI. Silakan tunggu {$retryAfter} detik sebelum mencoba lagi.",
            'retry_after' => $retryAfter,
            'upgrade_hint' => $this->getUpgradeHint(),
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) $limit->maxAttempts,
            'X-RateLimit-Remaining' => '0',
        ]);
    }

    /**
     * Get upgrade hint for users who hit rate limit.
     */
    protected function getUpgradeHint(): ?string
    {
        $tenant = auth()->user()?->tenant;

        if (!$tenant) {
            return null;
        }

        return match ($tenant->plan ?? 'trial') {
            'trial', 'free' => 'Upgrade ke Starter untuk meningkatkan limit AI 2x lipat.',
            'starter' => 'Upgrade ke Basic untuk meningkatkan limit AI 1.5x lipat.',
            'basic' => 'Upgrade ke Business untuk meningkatkan limit AI 2x lipat.',
            'business' => 'Upgrade ke Professional untuk meningkatkan limit AI 3x lipat.',
            'professional', 'pro' => 'Upgrade ke Enterprise untuk meningkatkan limit AI 5x lipat.',
            default => null,
        };
    }
}
