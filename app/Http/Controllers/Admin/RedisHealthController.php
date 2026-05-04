<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RedisHealthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Redis Health Controller
 *
 * Provides web interface for Redis health monitoring and diagnostics.
 * Accessible only to super admin users for infrastructure monitoring.
 */
class RedisHealthController extends Controller
{
    /**
     * Redis health service instance
     *
     * @var RedisHealthService
     */
    private RedisHealthService $redisHealth;

    /**
     * Create a new controller instance.
     */
    public function __construct(RedisHealthService $redisHealth)
    {
        $this->redisHealth = $redisHealth;

        // Only super admin users can access Redis health monitoring
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_super_admin) {
                abort(403, 'Access denied. Super admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Display Redis health monitoring dashboard
     *
     * @return View
     */
    public function index(): View
    {
        $healthResults = $this->redisHealth->checkAllConnections();
        $cachedMetrics = cache('redis_health_metrics', []);

        return view('admin.redis-health.index', [
            'healthResults' => $healthResults,
            'cachedMetrics' => $cachedMetrics,
            'isRedisEnabled' => env('REDIS_ENABLED', false),
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Get Redis health status as JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $connection = $request->get('connection', 'all');
        $forceRefresh = $request->boolean('refresh', false);

        if ($connection === 'all') {
            $results = $this->redisHealth->checkAllConnections();
        } else {
            $results = $this->redisHealth->getCachedHealthStatus($connection, $forceRefresh);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test specific Redis connection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'connection' => 'required|string|in:default,cache,session,queue',
        ]);

        $connection = $request->get('connection');
        $result = $this->redisHealth->checkConnection($connection);

        return response()->json([
            'success' => true,
            'connection' => $connection,
            'result' => $result,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Clear Redis health cache
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $connection = $request->get('connection');

        $this->redisHealth->clearHealthCache($connection);

        return response()->json([
            'success' => true,
            'message' => $connection
                ? "Health cache cleared for connection: {$connection}"
                : 'Health cache cleared for all connections',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get Redis health metrics for dashboard charts
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        $metrics = cache('redis_health_metrics', []);

        // Add current health status
        $currentHealth = $this->redisHealth->checkAllConnections();

        $dashboardMetrics = [
            'current_status' => $currentHealth['overall_healthy'],
            'total_connections' => count($currentHealth['connections']),
            'healthy_connections' => count(array_filter($currentHealth['connections'], fn($c) => $c['healthy'])),
            'average_response_time' => $this->calculateAverageResponseTime($currentHealth['connections']),
            'last_check' => $currentHealth['timestamp'],
            'cached_metrics' => $metrics,
            'connection_details' => $currentHealth['connections'],
        ];

        return response()->json([
            'success' => true,
            'metrics' => $dashboardMetrics,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Validate Redis configuration
     *
     * @return JsonResponse
     */
    public function validateConfiguration(): JsonResponse
    {
        $isValid = $this->redisHealth->validateConfiguration();
        $shouldFallback = $this->redisHealth->shouldFallbackToDatabase();

        return response()->json([
            'success' => true,
            'configuration_valid' => $isValid,
            'should_fallback' => $shouldFallback,
            'redis_enabled' => env('REDIS_ENABLED', false),
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Calculate average response time from connections
     *
     * @param array $connections
     * @return float
     */
    private function calculateAverageResponseTime(array $connections): float
    {
        $responseTimes = array_map(fn($c) => $c['response_time'], $connections);
        $validTimes = array_filter($responseTimes, fn($time) => is_numeric($time) && $time > 0);

        return !empty($validTimes) ? round(array_sum($validTimes) / count($validTimes), 2) : 0.0;
    }
}
