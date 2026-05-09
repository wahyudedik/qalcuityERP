<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\RedisHealthAlertNotification;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use RedisException;

/**
 * Redis Health Service
 *
 * Provides comprehensive Redis health monitoring, authentication validation,
 * and graceful fallback mechanisms for Redis unavailability scenarios.
 */
class RedisHealthService
{
    /**
     * Cache key for Redis health status
     */
    private const HEALTH_CACHE_KEY = 'redis_health_status';

    /**
     * Cache TTL for health status (seconds)
     */
    private const HEALTH_CACHE_TTL = 60;

    /**
     * Maximum connection attempts before marking as unhealthy
     */
    private const MAX_CONNECTION_ATTEMPTS = 3;

    /**
     * Test Redis connection and authentication
     *
     * @param  string  $connection  Redis connection name (default, cache, session, queue)
     * @return array Health check result with status and details
     */
    public function checkConnection(string $connection = 'default'): array
    {
        $startTime = microtime(true);

        try {
            // Check if Redis is enabled
            if (! $this->isRedisEnabled()) {
                return [
                    'status' => 'disabled',
                    'healthy' => false,
                    'message' => 'Redis is disabled via REDIS_ENABLED environment flag',
                    'connection' => $connection,
                    'response_time' => 0,
                    'details' => [
                        'redis_enabled' => false,
                        'fallback_available' => true,
                    ],
                ];
            }

            // Attempt Redis connection with timeout
            $redis = Redis::connection($connection);

            // Test basic connectivity with PING command
            $pingResult = $redis->ping();

            // Test authentication by attempting a simple operation
            $testKey = 'health_check_'.time();
            $testValue = 'test_value_'.uniqid();

            // Set and get test value to verify full functionality
            $redis->setex($testKey, 10, $testValue);
            $retrievedValue = $redis->get($testKey);
            $redis->del($testKey);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($retrievedValue === $testValue) {
                return [
                    'status' => 'healthy',
                    'healthy' => true,
                    'message' => 'Redis connection and authentication successful',
                    'connection' => $connection,
                    'response_time' => $responseTime,
                    'details' => [
                        'ping_result' => $pingResult,
                        'auth_test' => 'passed',
                        'read_write_test' => 'passed',
                    ],
                ];
            } else {
                throw new Exception('Redis read/write test failed');
            }
        } catch (RedisException $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Check if this is an authentication error
            $isAuthError = $this->isAuthenticationError($e);

            Log::warning('Redis connection failed', [
                'connection' => $connection,
                'error' => $e->getMessage(),
                'is_auth_error' => $isAuthError,
                'response_time' => $responseTime,
            ]);

            return [
                'status' => $isAuthError ? 'auth_failed' : 'connection_failed',
                'healthy' => false,
                'message' => $e->getMessage(),
                'connection' => $connection,
                'response_time' => $responseTime,
                'details' => [
                    'error_type' => $isAuthError ? 'authentication' : 'connection',
                    'fallback_recommended' => true,
                ],
            ];
        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Redis health check failed', [
                'connection' => $connection,
                'error' => $e->getMessage(),
                'response_time' => $responseTime,
            ]);

            return [
                'status' => 'error',
                'healthy' => false,
                'message' => $e->getMessage(),
                'connection' => $connection,
                'response_time' => $responseTime,
                'details' => [
                    'error_type' => 'general',
                    'fallback_recommended' => true,
                ],
            ];
        }
    }

    /**
     * Check health of all Redis connections
     *
     * @return array Comprehensive health status for all connections
     */
    public function checkAllConnections(): array
    {
        $connections = ['default', 'cache', 'session', 'queue'];
        $results = [];
        $overallHealthy = true;

        foreach ($connections as $connection) {
            $result = $this->checkConnection($connection);
            $results[$connection] = $result;

            if (! $result['healthy']) {
                $overallHealthy = false;
            }
        }

        return [
            'overall_healthy' => $overallHealthy,
            'timestamp' => now()->toISOString(),
            'connections' => $results,
            'recommendations' => $this->generateRecommendations($results),
        ];
    }

    /**
     * Validate Redis configuration on application startup
     *
     * @return bool True if Redis is properly configured or disabled
     */
    public function validateConfiguration(): bool
    {
        // If Redis is disabled, configuration is valid
        if (! $this->isRedisEnabled()) {
            Log::info('Redis is disabled, using database fallback drivers');

            return true;
        }

        Log::info('Validating Redis configuration on application startup');

        // Check for placeholder passwords in production
        $redisPassword = env('REDIS_PASSWORD');
        $isPlaceholderPassword = in_array($redisPassword, [
            'your_actual_redis_password_here',
            'null',
            null,
            '',
        ], true);

        if ($isPlaceholderPassword && app()->environment('production')) {
            Log::critical('Redis placeholder password detected in production', [
                'redis_password' => $redisPassword,
                'environment' => app()->environment(),
                'action_required' => 'Update REDIS_PASSWORD with actual Redis server password',
                'startup_validation' => true,
            ]);

            // Send immediate alert for production misconfiguration
            $this->sendStartupAlert('Redis placeholder password in production', [
                'environment' => app()->environment(),
                'action_required' => 'Update REDIS_PASSWORD with actual Redis server password',
                'severity' => 'critical',
            ]);

            return false;
        }

        // Test basic connectivity for all critical connections
        $criticalConnections = ['default', 'cache', 'session'];
        $validationResults = [];
        $overallValid = true;

        foreach ($criticalConnections as $connection) {
            $healthCheck = $this->checkConnection($connection);
            $validationResults[$connection] = $healthCheck;

            if (! $healthCheck['healthy']) {
                $overallValid = false;

                Log::warning('Redis startup validation failed for connection', [
                    'connection' => $connection,
                    'status' => $healthCheck['status'],
                    'message' => $healthCheck['message'],
                    'startup_validation' => true,
                ]);
            }
        }

        if (! $overallValid) {
            Log::error('Redis startup validation failed', [
                'failed_connections' => array_keys(array_filter($validationResults, fn ($r) => ! $r['healthy'])),
                'environment' => app()->environment(),
                'startup_validation' => true,
            ]);

            // Send startup alert for validation failures
            $this->sendStartupAlert('Redis startup validation failed', [
                'failed_connections' => $validationResults,
                'environment' => app()->environment(),
                'severity' => 'warning',
            ]);

            return false;
        }

        Log::info('Redis startup validation successful', [
            'validated_connections' => $criticalConnections,
            'environment' => app()->environment(),
        ]);

        return true;
    }

    /**
     * Get cached health status or perform fresh check
     */
    public function getCachedHealthStatus(string $connection = 'default', bool $forceRefresh = false): array
    {
        $cacheKey = self::HEALTH_CACHE_KEY.'_'.$connection;

        if (! $forceRefresh) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $healthStatus = $this->checkConnection($connection);

        // Cache the result for a short time to avoid repeated checks
        Cache::put($cacheKey, $healthStatus, self::HEALTH_CACHE_TTL);

        return $healthStatus;
    }

    /**
     * Determine if Redis should fallback to database drivers
     *
     * @return bool True if fallback is recommended
     */
    public function shouldFallbackToDatabase(): bool
    {
        // If Redis is disabled, always use database
        if (! $this->isRedisEnabled()) {
            return true;
        }

        // Check health of critical connections
        $cacheHealth = $this->getCachedHealthStatus('cache');
        $sessionHealth = $this->getCachedHealthStatus('session');

        // Recommend fallback if both cache and session are unhealthy
        return ! $cacheHealth['healthy'] && ! $sessionHealth['healthy'];
    }

    /**
     * Perform a quick ping check on a Redis connection
     *
     * Returns a simplified status array suitable for lightweight health probes.
     *
     * @param  string  $connection  Redis connection name
     * @return array{status: string, healthy: bool, message: string, response_time: float}
     */
    public function ping(string $connection = 'default'): array
    {
        if (! $this->isRedisEnabled()) {
            return [
                'status' => 'disabled',
                'healthy' => false,
                'message' => 'Redis is disabled via REDIS_ENABLED environment flag',
                'response_time' => 0,
            ];
        }

        $startTime = microtime(true);

        try {
            $redis = Redis::connection($connection);
            $redis->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'connected',
                'healthy' => true,
                'message' => 'Redis ping successful',
                'response_time' => $responseTime,
            ];
        } catch (RedisException $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $isAuthError = $this->isAuthenticationError($e);

            Log::warning('Redis ping failed', [
                'connection' => $connection,
                'error' => $e->getMessage(),
                'is_auth_error' => $isAuthError,
            ]);

            return [
                'status' => $isAuthError ? 'auth_failed' : 'unavailable',
                'healthy' => false,
                'message' => $e->getMessage(),
                'response_time' => $responseTime,
            ];
        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Redis ping error', [
                'connection' => $connection,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'unavailable',
                'healthy' => false,
                'message' => $e->getMessage(),
                'response_time' => $responseTime,
            ];
        }
    }

    /**
     * Get the current Redis connection status as a simple string
     *
     * Returns one of: 'connected', 'auth_failed', 'unavailable', 'disabled'
     *
     * @param  string  $connection  Redis connection name
     */
    public function getStatus(string $connection = 'default'): string
    {
        if (! $this->isRedisEnabled()) {
            return 'disabled';
        }

        // Check for placeholder password — treat as auth_failed before even connecting
        $redisPassword = env('REDIS_PASSWORD');
        if ($this->isPlaceholderPassword($redisPassword)) {
            Log::warning('Redis placeholder password detected — connection will likely fail with NOAUTH', [
                'connection' => $connection,
                'environment' => app()->environment(),
            ]);
        }

        $result = $this->ping($connection);

        return $result['status'];
    }

    /**
     * Check if the given password value is a known placeholder
     */
    private function isPlaceholderPassword(mixed $password): bool
    {
        return in_array($password, [
            'your_actual_redis_password_here',
            'null',
            null,
            '',
        ], true);
    }

    /**
     * Check if Redis is enabled via environment configuration
     */
    private function isRedisEnabled(): bool
    {
        return (bool) env('REDIS_ENABLED', false);
    }

    /**
     * Determine if the exception is related to authentication
     */
    private function isAuthenticationError(Exception $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'noauth') ||
            str_contains($message, 'authentication required') ||
            str_contains($message, 'invalid password') ||
            str_contains($message, 'wrong password');
    }

    /**
     * Generate recommendations based on health check results
     */
    private function generateRecommendations(array $results): array
    {
        $recommendations = [];

        foreach ($results as $connection => $result) {
            if (! $result['healthy']) {
                switch ($result['status']) {
                    case 'disabled':
                        $recommendations[] = "Redis is disabled. Using database fallback for {$connection}.";
                        break;

                    case 'auth_failed':
                        $recommendations[] = "Authentication failed for {$connection}. Check REDIS_PASSWORD configuration.";
                        break;

                    case 'connection_failed':
                        $recommendations[] = "Connection failed for {$connection}. Check Redis server availability and network connectivity.";
                        break;

                    default:
                        $recommendations[] = "Health check failed for {$connection}. Consider using database fallback.";
                }
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'All Redis connections are healthy.';
        }

        return $recommendations;
    }

    /**
     * Clear health status cache
     *
     * @param  string|null  $connection  Specific connection or null for all
     */
    public function clearHealthCache(?string $connection = null): void
    {
        if ($connection) {
            Cache::forget(self::HEALTH_CACHE_KEY.'_'.$connection);
        } else {
            $connections = ['default', 'cache', 'session', 'queue'];
            foreach ($connections as $conn) {
                Cache::forget(self::HEALTH_CACHE_KEY.'_'.$conn);
            }
        }
    }

    /**
     * Send startup alert for critical Redis configuration issues
     */
    private function sendStartupAlert(string $title, array $details): void
    {
        try {
            // Dispatch alert job to avoid blocking application startup
            dispatch(function () use ($title, $details) {
                try {
                    $superAdmins = User::where('is_super_admin', true)
                        ->where('is_active', true)
                        ->get();

                    if ($superAdmins->isNotEmpty()) {
                        Notification::send(
                            $superAdmins,
                            new RedisHealthAlertNotification($title, $details)
                        );

                        Log::info('Redis startup alert sent', [
                            'title' => $title,
                            'recipients' => $superAdmins->count(),
                            'severity' => $details['severity'] ?? 'info',
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Failed to send Redis startup alert', [
                        'title' => $title,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->onQueue('database'); // Use database queue to avoid Redis dependency

        } catch (Exception $e) {
            Log::error('Failed to dispatch Redis startup alert', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
