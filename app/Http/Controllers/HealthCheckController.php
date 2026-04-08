<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

/**
 * Health Check Controller
 * 
 * Provides system health monitoring endpoint
 * 
 * BUG-015: Tidak Ada Health Check Endpoint untuk Monitoring
 * 
 * Usage:
 * GET /api/health - Basic health check
 * GET /api/health/detailed - Detailed health check with all services
 * GET /api/health/ready - Kubernetes readiness probe
 * GET /api/health/live - Kubernetes liveness probe
 */
class HealthCheckController extends Controller
{
    /**
     * Basic health check
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Detailed health check with all services
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailed()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'redis' => $this->checkRedis(),
            'email' => $this->checkEmail(),
        ];

        $overallStatus = $this->determineOverallStatus($checks);

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
            'checks' => $checks,
            'summary' => [
                'total' => count($checks),
                'healthy' => collect($checks)->where('status', 'healthy')->count(),
                'degraded' => collect($checks)->where('status', 'degraded')->count(),
                'unhealthy' => collect($checks)->where('status', 'unhealthy')->count(),
            ],
        ], $overallStatus === 'unhealthy' ? 503 : 200);
    }

    /**
     * Kubernetes readiness probe
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function ready()
    {
        $ready = true;
        $checks = [];

        // Database must be healthy
        $dbCheck = $this->checkDatabase();
        $checks['database'] = $dbCheck;
        if ($dbCheck['status'] !== 'healthy') {
            $ready = false;
        }

        // Cache must be healthy
        $cacheCheck = $this->checkCache();
        $checks['cache'] = $cacheCheck;
        if ($cacheCheck['status'] !== 'healthy') {
            $ready = false;
        }

        return response()->json([
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $ready ? 200 : 503);
    }

    /**
     * Kubernetes liveness probe
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function live()
    {
        // Simple check - is the application running?
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
            'uptime' => $this->getUptime(),
        ]);
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $duration = (microtime(true) - $start) * 1000;

            return [
                'status' => 'healthy',
                'driver' => config('database.default'),
                'response_time_ms' => round($duration, 2),
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('database.default'),
                'response_time_ms' => null,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity
     */
    protected function checkCache(): array
    {
        try {
            $start = microtime(true);
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            $duration = (microtime(true) - $start) * 1000;

            if ($value !== 'test') {
                return [
                    'status' => 'unhealthy',
                    'driver' => config('cache.default'),
                    'response_time_ms' => round($duration, 2),
                    'message' => 'Cache read/write mismatch',
                ];
            }

            return [
                'status' => 'healthy',
                'driver' => config('cache.default'),
                'response_time_ms' => round($duration, 2),
                'message' => 'Cache operational',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('cache.default'),
                'response_time_ms' => null,
                'message' => 'Cache failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // For sync driver, always healthy
            if ($driver === 'sync') {
                return [
                    'status' => 'healthy',
                    'driver' => $driver,
                    'response_time_ms' => 0,
                    'message' => 'Queue driver is sync (no background processing)',
                    'warning' => 'Use database/redis driver for production',
                ];
            }

            // Check queue connection
            $start = microtime(true);
            // Use getSize() which is available in most queue drivers
            $queueSize = Queue::connection()->size();
            $duration = (microtime(true) - $start) * 1000;

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'response_time_ms' => round($duration, 2),
                'message' => 'Queue operational',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'degraded',
                'driver' => config('queue.default'),
                'response_time_ms' => null,
                'message' => 'Queue check failed: ' . $e->getMessage(),
                'impact' => 'Background jobs may not process',
            ];
        }
    }

    /**
     * Check storage connectivity
     */
    protected function checkStorage(): array
    {
        try {
            $start = microtime(true);
            $disk = Storage::disk('local');
            $testFile = 'health_check_' . time() . '.txt';

            $disk->put($testFile, 'test');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);
            $duration = (microtime(true) - $start) * 1000;

            if (!$exists) {
                return [
                    'status' => 'unhealthy',
                    'disk' => 'local',
                    'response_time_ms' => round($duration, 2),
                    'message' => 'Storage write/read failed',
                ];
            }

            return [
                'status' => 'healthy',
                'disk' => 'local',
                'response_time_ms' => round($duration, 2),
                'message' => 'Storage operational',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'disk' => 'local',
                'response_time_ms' => null,
                'message' => 'Storage failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connectivity (if configured)
     */
    protected function checkRedis(): array
    {
        try {
            // Check if Redis is configured
            if (!class_exists('Redis') && !extension_loaded('redis')) {
                return [
                    'status' => 'not_configured',
                    'message' => 'Redis extension not installed',
                ];
            }

            $start = microtime(true);
            Redis::ping();
            $duration = (microtime(true) - $start) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($duration, 2),
                'message' => 'Redis operational',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'degraded',
                'response_time_ms' => null,
                'message' => 'Redis failed: ' . $e->getMessage(),
                'impact' => 'Cache/sessions may fallback to database',
            ];
        }
    }

    /**
     * Check email configuration
     */
    protected function checkEmail(): array
    {
        try {
            $driver = config('mail.default', env('MAIL_MAILER', 'log'));

            if ($driver === 'log') {
                return [
                    'status' => 'degraded',
                    'driver' => $driver,
                    'message' => 'Email driver is log (emails not sent, only logged)',
                    'recommendation' => 'Configure SMTP for production',
                ];
            }

            if ($driver === 'array') {
                return [
                    'status' => 'unhealthy',
                    'driver' => $driver,
                    'message' => 'Email driver is array (emails discarded)',
                    'recommendation' => 'Configure SMTP immediately',
                ];
            }

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'message' => 'Email configured',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Email check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Determine overall health status
     */
    protected function determineOverallStatus(array $checks): string
    {
        $statuses = collect($checks)->pluck('status');

        // If any service is unhealthy, overall is unhealthy
        if ($statuses->contains('unhealthy')) {
            return 'unhealthy';
        }

        // If any service is degraded, overall is degraded
        if ($statuses->contains('degraded')) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * Get application uptime
     */
    protected function getUptime(): string
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : time();
        $uptime = time() - $startTime;

        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}
