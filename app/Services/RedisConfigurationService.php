<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Redis;
use RedisException;

/**
 * Redis Configuration Service
 *
 * Handles Redis connection validation, authentication checking, and automatic
 * fallback to database drivers when Redis authentication fails or is unavailable.
 *
 * This service implements the graceful degradation logic for Redis unavailability
 * scenarios as specified in the Redis authentication fix requirements.
 */
class RedisConfigurationService
{
    /**
     * Validate Redis configuration and implement fallback mechanisms
     *
     * @return bool True if Redis is available and properly configured
     */
    public function validateAndConfigureFallback(): bool
    {
        // Check if Redis is enabled
        $redisEnabled = env('REDIS_ENABLED', false);

        if (!$redisEnabled) {
            $this->enableDatabaseFallback('Redis is disabled via REDIS_ENABLED=false');
            return false;
        }

        // Validate Redis password configuration
        $redisPassword = env('REDIS_PASSWORD');
        $isPlaceholderPassword = $this->isPlaceholderPassword($redisPassword);

        // Log warning if placeholder password is detected
        if ($isPlaceholderPassword && app()->environment('production')) {
            Log::warning('Redis placeholder password detected in production environment', [
                'redis_password' => $redisPassword,
                'environment' => app()->environment(),
            ]);
        }

        // Test Redis connection and authentication
        $connectionResult = $this->testRedisConnection($redisPassword, $isPlaceholderPassword);

        if (!$connectionResult['success']) {
            $this->handleRedisConnectionFailure($connectionResult, $isPlaceholderPassword);
            return false;
        }

        return true;
    }

    /**
     * Check if the provided password is a placeholder value
     *
     * @param mixed $password
     * @return bool
     */
    private function isPlaceholderPassword($password): bool
    {
        return in_array($password, [
            'your_actual_redis_password_here',
            'null',
            null,
            ''
        ], true);
    }

    /**
     * Test Redis connection and authentication
     *
     * @param mixed $password
     * @param bool $isPlaceholder
     * @return array
     */
    private function testRedisConnection($password, bool $isPlaceholder): array
    {
        try {
            // Skip connection test if Redis extension is not loaded
            if (!extension_loaded('redis')) {
                return [
                    'success' => false,
                    'error' => 'Redis PHP extension not loaded',
                    'type' => 'extension_missing'
                ];
            }

            $redis = new Redis();
            $host = env('REDIS_HOST', '127.0.0.1');
            $port = env('REDIS_PORT', '6379');
            $timeout = 2.0; // Short timeout for validation

            $connected = $redis->connect($host, $port, $timeout);

            if (!$connected) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to Redis server',
                    'type' => 'connection_failed'
                ];
            }

            // Test authentication if password is provided and not placeholder
            if (!$isPlaceholder && $password) {
                $authenticated = $redis->auth($password);
                if (!$authenticated) {
                    $redis->close();
                    return [
                        'success' => false,
                        'error' => 'Redis authentication failed',
                        'type' => 'auth_failed'
                    ];
                }
            }

            // Test basic Redis operation
            $pingResult = $redis->ping();
            $redis->close();

            if ($pingResult !== true && $pingResult !== 'PONG') {
                return [
                    'success' => false,
                    'error' => 'Redis ping failed',
                    'type' => 'ping_failed'
                ];
            }

            return ['success' => true];
        } catch (RedisException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'redis_exception'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_exception'
            ];
        }
    }

    /**
     * Handle Redis connection failure and implement fallback
     *
     * @param array $connectionResult
     * @param bool $isPlaceholderPassword
     */
    private function handleRedisConnectionFailure(array $connectionResult, bool $isPlaceholderPassword): void
    {
        $errorType = $connectionResult['type'] ?? 'unknown';
        $errorMessage = $connectionResult['error'] ?? 'Unknown error';

        // Log the specific failure
        Log::error('Redis connection validation failed - implementing automatic fallback', [
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'redis_host' => env('REDIS_HOST', '127.0.0.1'),
            'redis_port' => env('REDIS_PORT', '6379'),
            'placeholder_password' => $isPlaceholderPassword,
            'environment' => app()->environment(),
        ]);

        // Implement fallback based on error type
        switch ($errorType) {
            case 'auth_failed':
                $this->enableDatabaseFallback('Redis authentication failed - invalid credentials');
                break;
            case 'connection_failed':
                $this->enableDatabaseFallback('Redis server connection failed - server unavailable');
                break;
            case 'extension_missing':
                $this->enableDatabaseFallback('Redis PHP extension not available');
                break;
            default:
                $this->enableDatabaseFallback("Redis error: {$errorMessage}");
                break;
        }
    }

    /**
     * Enable database fallback for session, cache, and queue drivers
     *
     * @param string $reason
     */
    private function enableDatabaseFallback(string $reason): void
    {
        Log::info('Enabling database fallback for Redis services', [
            'reason' => $reason,
            'original_session_driver' => config('session.driver'),
            'original_cache_driver' => config('cache.default'),
            'original_queue_driver' => config('queue.default'),
        ]);

        // Only modify configuration if not running in console (avoid affecting migrations, etc.)
        if (!app()->runningInConsole()) {
            // Fallback session driver to database
            if (config('session.driver') === 'redis') {
                Config::set('session.driver', 'database');
            }

            // Fallback cache driver to database
            if (config('cache.default') === 'redis') {
                Config::set('cache.default', 'database');
            }

            // Fallback queue driver to database
            if (config('queue.default') === 'redis') {
                Config::set('queue.default', 'database');
            }
        }

        // Log the fallback action
        Log::info('Database fallback enabled successfully', [
            'session_driver' => config('session.driver'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ]);
    }

    /**
     * Check if Redis fallback should be triggered based on current configuration
     *
     * @return bool
     */
    public function shouldTriggerFallback(): bool
    {
        $redisEnabled = env('REDIS_ENABLED', false);

        if (!$redisEnabled) {
            return true;
        }

        $redisPassword = env('REDIS_PASSWORD');
        $isPlaceholderPassword = $this->isPlaceholderPassword($redisPassword);

        // Trigger fallback if using placeholder password in production
        if ($isPlaceholderPassword && app()->environment('production')) {
            return true;
        }

        // Test connection to determine if fallback is needed
        $connectionResult = $this->testRedisConnection($redisPassword, $isPlaceholderPassword);

        return !$connectionResult['success'];
    }

    /**
     * Get Redis connection status for monitoring
     *
     * @return array
     */
    public function getConnectionStatus(): array
    {
        $redisEnabled = env('REDIS_ENABLED', false);

        if (!$redisEnabled) {
            return [
                'enabled' => false,
                'status' => 'disabled',
                'message' => 'Redis is disabled via REDIS_ENABLED=false'
            ];
        }

        $redisPassword = env('REDIS_PASSWORD');
        $isPlaceholderPassword = $this->isPlaceholderPassword($redisPassword);

        if ($isPlaceholderPassword) {
            return [
                'enabled' => true,
                'status' => 'misconfigured',
                'message' => 'Redis password is placeholder value',
                'placeholder_password' => true
            ];
        }

        $connectionResult = $this->testRedisConnection($redisPassword, $isPlaceholderPassword);

        if ($connectionResult['success']) {
            return [
                'enabled' => true,
                'status' => 'connected',
                'message' => 'Redis connection successful'
            ];
        }

        return [
            'enabled' => true,
            'status' => 'failed',
            'message' => $connectionResult['error'] ?? 'Connection failed',
            'error_type' => $connectionResult['type'] ?? 'unknown'
        ];
    }
}
