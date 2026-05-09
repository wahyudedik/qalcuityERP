<?php

namespace App\Http\Middleware;

use App\Services\RedisHealthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redis Health Middleware
 *
 * Monitors Redis health during request processing and handles
 * graceful fallback when Redis becomes unavailable.
 */
class RedisHealthMiddleware
{
    /**
     * Redis health service instance
     */
    private RedisHealthService $redisHealth;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RedisHealthService $redisHealth)
    {
        $this->redisHealth = $redisHealth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only perform health checks in production or when explicitly enabled
        if (! $this->shouldPerformHealthCheck()) {
            return $next($request);
        }

        // Check Redis health before processing request
        $this->performPreRequestHealthCheck();

        $response = $next($request);

        // Optional: Check Redis health after request processing
        // This can help detect issues that occur during request handling
        $this->performPostRequestHealthCheck();

        return $response;
    }

    /**
     * Determine if health check should be performed
     */
    private function shouldPerformHealthCheck(): bool
    {
        // Skip health checks in testing environment
        if (app()->environment('testing')) {
            return false;
        }

        // Skip if Redis is not enabled
        if (! env('REDIS_ENABLED', false)) {
            return false;
        }

        // Perform health checks in production or when explicitly enabled
        return app()->environment('production') || env('REDIS_HEALTH_CHECK_ENABLED', false);
    }

    /**
     * Perform Redis health check before request processing
     */
    private function performPreRequestHealthCheck(): void
    {
        try {
            // Use cached health status to avoid performance impact
            $healthStatus = $this->redisHealth->getCachedHealthStatus('default');

            if (! $healthStatus['healthy']) {
                Log::warning('Redis unhealthy at request start', [
                    'status' => $healthStatus['status'],
                    'message' => $healthStatus['message'],
                    'request_url' => request()->url(),
                ]);

                // Trigger fallback if recommended
                if ($this->redisHealth->shouldFallbackToDatabase()) {
                    $this->triggerFallback();
                }
            }
        } catch (\Exception $e) {
            Log::error('Redis health check failed during request processing', [
                'error' => $e->getMessage(),
                'request_url' => request()->url(),
            ]);

            // Trigger fallback as safety measure
            $this->triggerFallback();
        }
    }

    /**
     * Perform Redis health check after request processing
     */
    private function performPostRequestHealthCheck(): void
    {
        // Only perform post-request checks periodically to avoid performance impact
        if (rand(1, 100) > 5) { // 5% chance
            return;
        }

        try {
            // Clear cached health status and perform fresh check
            $this->redisHealth->clearHealthCache('default');
            $healthStatus = $this->redisHealth->checkConnection('default');

            if (! $healthStatus['healthy']) {
                Log::info('Redis health degraded during request processing', [
                    'status' => $healthStatus['status'],
                    'response_time' => $healthStatus['response_time'],
                ]);
            }
        } catch (\Exception $e) {
            Log::debug('Post-request Redis health check failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Trigger fallback to database drivers
     */
    private function triggerFallback(): void
    {
        try {
            // Update configuration to use database drivers
            config(['cache.default' => 'database']);
            config(['session.driver' => 'database']);
            config(['queue.default' => 'database']);

            Log::info('Redis fallback triggered during request processing', [
                'cache_driver' => 'database',
                'session_driver' => 'database',
                'queue_driver' => 'database',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger Redis fallback', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
