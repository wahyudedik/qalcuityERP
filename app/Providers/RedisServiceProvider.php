<?php

namespace App\Providers;

use App\Services\RedisConfigurationService;
use App\Services\RedisHealthService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Redis Service Provider
 *
 * Handles Redis configuration validation, health monitoring,
 * and automatic fallback to database drivers when Redis is unavailable.
 */
class RedisServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RedisHealthService::class, function ($app) {
            return new RedisHealthService;
        });

        $this->app->singleton(RedisConfigurationService::class, function ($app) {
            return new RedisConfigurationService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only perform Redis validation and fallback logic in non-testing environments
        if (! $this->app->environment('testing')) {
            $this->configureRedisFallback();
        }
    }

    /**
     * Configure Redis fallback mechanisms
     */
    private function configureRedisFallback(): void
    {
        try {
            // First, use the configuration service for initial validation and fallback
            $redisConfig = $this->app->make(RedisConfigurationService::class);
            $isRedisAvailable = $redisConfig->validateAndConfigureFallback();

            if (! $isRedisAvailable) {
                Log::info('Redis configuration service activated database fallback');

                return;
            }

            // If Redis is available, proceed with health monitoring
            $redisHealth = $this->app->make(RedisHealthService::class);

            // Validate Redis configuration
            $isConfigValid = $redisHealth->validateConfiguration();

            if (! $isConfigValid) {
                $this->enableDatabaseFallback();

                return;
            }

            // Check if fallback is recommended based on health status
            if ($redisHealth->shouldFallbackToDatabase()) {
                $this->enableDatabaseFallback();

                Log::info('Redis fallback activated', [
                    'reason' => 'Redis health check failed',
                    'fallback_drivers' => [
                        'cache' => 'database',
                        'session' => 'database',
                        'queue' => 'database',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // If Redis services fail, enable fallback as safety measure
            Log::error('Redis services failed, enabling database fallback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->enableDatabaseFallback();
        }
    }

    /**
     * Enable database fallback for cache, session, and queue drivers
     */
    private function enableDatabaseFallback(): void
    {
        // Check current driver configurations
        $currentCacheDriver = config('cache.default');
        $currentSessionDriver = config('session.driver');
        $currentQueueDriver = config('queue.default');

        // Only fallback if currently using Redis
        if ($currentCacheDriver === 'redis') {
            Config::set('cache.default', 'database');
            Log::info('Cache driver fallback activated: redis → database');
        }

        if ($currentSessionDriver === 'redis') {
            Config::set('session.driver', 'database');
            Log::info('Session driver fallback activated: redis → database');
        }

        if ($currentQueueDriver === 'redis') {
            Config::set('queue.default', 'database');
            Log::info('Queue driver fallback activated: redis → database');
        }

        // Ensure failover cache store is available
        $this->ensureFailoverCacheStore();
    }

    /**
     * Ensure failover cache store is properly configured
     */
    private function ensureFailoverCacheStore(): void
    {
        $failoverStores = config('cache.stores.failover.stores', []);

        // Ensure database and array stores are in failover configuration
        if (! in_array('database', $failoverStores)) {
            $failoverStores[] = 'database';
        }

        if (! in_array('array', $failoverStores)) {
            $failoverStores[] = 'array';
        }

        Config::set('cache.stores.failover.stores', $failoverStores);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            RedisHealthService::class,
            RedisConfigurationService::class,
        ];
    }
}
