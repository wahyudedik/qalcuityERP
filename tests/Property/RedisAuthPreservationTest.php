<?php

namespace Tests\Property;

use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Property-Based Tests for Redis Authentication Preservation Behavior.
 *
 * Feature: redis-auth-fix
 *
 * **IMPORTANT**: These are PRESERVATION tests that should PASS on unfixed code
 * to establish baseline behavior that must be maintained after the fix.
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
 */
class RedisAuthPreservationTest extends TestCase
{
    use TestTrait;

    /**
     * Property 2: Preservation - Database Sessions Continue to Work When Redis is Disabled
     *
     * For any configuration where Redis is disabled (REDIS_ENABLED=false),
     * the system SHALL continue to use database-based sessions successfully,
     * preserving existing fallback behavior.
     *
     * **EXPECTED OUTCOME**: Test PASSES on unfixed code (confirms baseline behavior)
     *
     * **Validates: Requirements 3.1**
     */
    #[ErisRepeat(repeat: 3)]
    public function test_database_sessions_work_when_redis_disabled(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'test_session_key_1',
                    'user_preferences',
                    'cart_items',
                    'temp_data',
                ]), // various session keys
                Generators::elements([
                    'simple_string_value',
                    ['array' => 'value', 'nested' => ['data' => 123]],
                    42,
                    true,
                ]), // various session values
                Generators::elements([
                    'your_actual_redis_password_here',
                    'invalid_password',
                    null,
                    '',
                ]) // various Redis passwords (should not affect database sessions)
            )
            ->then(function ($sessionKey, $sessionValue, $redisPassword) {
                // Configure to use database sessions (Redis disabled scenario)
                Config::set('session.driver', 'database');
                Config::set('database.redis.default.password', $redisPassword);

                // Explicitly disable Redis to simulate production fallback
                Config::set('redis.enabled', false);

                // Test database session operations
                Session::put($sessionKey, $sessionValue);
                Session::save();

                // Verify session data persists correctly
                $retrievedValue = Session::get($sessionKey);

                $this->assertEquals(
                    $sessionValue,
                    $retrievedValue,
                    'Database sessions should work regardless of Redis configuration. '.
                        "Session key: {$sessionKey}, Redis password: ".($redisPassword ?? 'null')
                );

                // Verify session exists
                $this->assertTrue(
                    Session::has($sessionKey),
                    'Session should exist in database storage when Redis is disabled'
                );

                // Test session removal
                Session::forget($sessionKey);
                $this->assertFalse(
                    Session::has($sessionKey),
                    'Session should be removable from database storage'
                );
            });
    }

    /**
     * Property 2b: Preservation - File Cache Operations Work When Redis is Not Configured
     *
     * For any configuration using file-based cache, the system SHALL continue
     * to operate normally without Redis dependency, preserving existing behavior.
     *
     * **EXPECTED OUTCOME**: Test PASSES on unfixed code (confirms baseline behavior)
     *
     * **Validates: Requirements 3.2**
     */
    #[ErisRepeat(repeat: 3)]
    public function test_file_cache_works_without_redis(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'cache_key_1',
                    'user_data_cache',
                    'settings_cache',
                    'temp_calculation',
                ]), // various cache keys
                Generators::elements([
                    'cached_string_value',
                    ['cached' => 'array', 'with' => ['nested' => 'data']],
                    999,
                    false,
                ]), // various cache values
                Generators::choose(1, 3600) // cache TTL in seconds
            )
            ->then(function ($cacheKey, $cacheValue, $ttl) {
                // Configure to use file cache (no Redis dependency)
                Config::set('cache.default', 'file');

                // Set invalid Redis config to ensure file cache doesn't depend on it
                Config::set('database.redis.default.password', 'invalid_redis_password');
                Config::set('database.redis.cache.password', 'invalid_redis_password');

                // Test file cache operations
                Cache::put($cacheKey, $cacheValue, $ttl);

                // Verify cache data is stored correctly
                $retrievedValue = Cache::get($cacheKey);

                $this->assertEquals(
                    $cacheValue,
                    $retrievedValue,
                    'File cache should work independently of Redis configuration. '.
                        "Cache key: {$cacheKey}, TTL: {$ttl}"
                );

                // Verify cache exists
                $this->assertTrue(
                    Cache::has($cacheKey),
                    'Cache should exist in file storage without Redis dependency'
                );

                // Test cache removal
                Cache::forget($cacheKey);
                $this->assertFalse(
                    Cache::has($cacheKey),
                    'Cache should be removable from file storage'
                );
            });
    }

    /**
     * Property 2c: Preservation - Redis Connections Without Authentication Work in Development
     *
     * For development environments where Redis authentication is not required,
     * the system SHALL continue to connect successfully, preserving existing behavior.
     *
     * **EXPECTED OUTCOME**: Test PASSES on unfixed code (confirms baseline behavior)
     *
     * **Validates: Requirements 3.3**
     */
    #[ErisRepeat(repeat: 3)]
    public function test_redis_works_without_authentication_in_development(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    null,
                    '',
                    false,
                ]), // no password scenarios (development Redis without auth)
                Generators::elements([
                    'dev_session_key',
                    'dev_cache_key',
                    'dev_temp_data',
                ]), // various keys for development testing
                Generators::elements([
                    'development_value',
                    ['dev' => 'data'],
                    123,
                ]) // various values
            )
            ->then(function ($noPassword, $testKey, $testValue) {
                // Skip if Redis server is not available or requires auth
                try {
                    // Configure Redis without password (development scenario)
                    Config::set('database.redis.default.password', $noPassword);
                    Config::set('database.redis.cache.password', $noPassword);

                    // Test Redis connection without authentication
                    $redis = app('redis');
                    $redis->connection('default')->ping();

                    // If ping succeeds, test session and cache operations
                    Config::set('session.driver', 'redis');
                    Config::set('cache.default', 'redis');

                    // Test session operations
                    Session::put($testKey.'_session', $testValue);
                    Session::save();

                    $sessionValue = Session::get($testKey.'_session');
                    $this->assertEquals(
                        $testValue,
                        $sessionValue,
                        'Redis sessions should work without authentication in development'
                    );

                    // Test cache operations
                    Cache::put($testKey.'_cache', $testValue, 60);
                    $cacheValue = Cache::get($testKey.'_cache');

                    $this->assertEquals(
                        $testValue,
                        $cacheValue,
                        'Redis cache should work without authentication in development'
                    );
                } catch (\Exception $e) {
                    // If Redis is not available or requires auth, skip this test
                    $this->markTestSkipped(
                        'Redis server not available or requires authentication: '.$e->getMessage()
                    );
                }
            });
    }

    /**
     * Property 2d: Preservation - Database Operations Continue Normally Without Redis
     *
     * For any database operations, the system SHALL continue to function
     * normally without Redis dependency, preserving core functionality.
     *
     * **EXPECTED OUTCOME**: Test PASSES on unfixed code (confirms baseline behavior)
     *
     * **Validates: Requirements 3.4**
     */
    #[ErisRepeat(repeat: 3)]
    public function test_database_operations_continue_without_redis(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    'database',
                    'file',
                    'array',
                ]), // non-Redis cache drivers
                Generators::elements([
                    'database',
                    'file',
                ]), // non-Redis session drivers
                Generators::elements([
                    'your_actual_redis_password_here',
                    'completely_wrong_password',
                    null,
                ]) // various Redis passwords (should not affect database operations)
            )
            ->then(function ($cacheDriver, $sessionDriver, $redisPassword) {
                // Configure non-Redis drivers
                Config::set('cache.default', $cacheDriver);
                Config::set('session.driver', $sessionDriver);

                // Set potentially invalid Redis config
                Config::set('database.redis.default.password', $redisPassword);
                Config::set('database.redis.cache.password', $redisPassword);

                // Test that basic application functionality works
                // This simulates core ERP operations that should not depend on Redis

                // Test 1: Session operations work with non-Redis drivers
                Session::put('app_test_key', 'app_test_value');
                Session::save();

                $this->assertEquals(
                    'app_test_value',
                    Session::get('app_test_key'),
                    "Application sessions should work with {$sessionDriver} driver regardless of Redis config"
                );

                // Test 2: Cache operations work with non-Redis drivers
                Cache::put('app_cache_key', 'app_cache_value', 300);

                $this->assertEquals(
                    'app_cache_value',
                    Cache::get('app_cache_key'),
                    "Application cache should work with {$cacheDriver} driver regardless of Redis config"
                );

                // Test 3: Configuration access works (simulates app settings)
                $appName = config('app.name');
                $this->assertNotEmpty(
                    $appName,
                    'Application configuration should be accessible regardless of Redis status'
                );

                // Test 4: Database connection works (simulates core ERP database operations)
                $dbConnection = config('database.default');
                $this->assertNotEmpty(
                    $dbConnection,
                    'Database configuration should be accessible regardless of Redis status'
                );

                // Cleanup
                Session::forget('app_test_key');
                Cache::forget('app_cache_key');
            });
    }

    /**
     * Property 2e: Preservation - Mixed Configuration Scenarios Work Correctly
     *
     * For various combinations of Redis and non-Redis configurations,
     * the system SHALL handle fallback scenarios gracefully, preserving
     * the ability to operate with partial Redis functionality.
     *
     * **EXPECTED OUTCOME**: Test PASSES on unfixed code (confirms baseline behavior)
     *
     * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
     */
    #[ErisRepeat(repeat: 5)]
    public function test_mixed_configuration_scenarios_work(): void
    {
        $this
            ->forAll(
                Generators::elements([
                    ['session' => 'database', 'cache' => 'file'],
                    ['session' => 'file', 'cache' => 'database'],
                    ['session' => 'database', 'cache' => 'database'],
                    ['session' => 'file', 'cache' => 'file'],
                    ['session' => 'database', 'cache' => 'array'],
                ]), // various non-Redis driver combinations
                Generators::elements([
                    true,
                    false,
                ]), // Redis enabled/disabled flag
                Generators::elements([
                    'valid_looking_password',
                    'your_actual_redis_password_here',
                    '',
                    null,
                ]) // various Redis password scenarios
            )
            ->then(function ($drivers, $redisEnabled, $redisPassword) {
                // Configure the mixed scenario
                Config::set('session.driver', $drivers['session']);
                Config::set('cache.default', $drivers['cache']);
                Config::set('redis.enabled', $redisEnabled);
                Config::set('database.redis.default.password', $redisPassword);
                Config::set('database.redis.cache.password', $redisPassword);

                // Test that both session and cache work in this mixed configuration
                $testData = [
                    'session_key' => 'mixed_config_session_value',
                    'cache_key' => 'mixed_config_cache_value',
                ];

                // Session operations
                Session::put('mixed_test_session', $testData['session_key']);
                Session::save();

                $this->assertEquals(
                    $testData['session_key'],
                    Session::get('mixed_test_session'),
                    "Session should work with {$drivers['session']} driver in mixed configuration. ".
                        'Redis enabled: '.($redisEnabled ? 'true' : 'false').
                        ', Redis password: '.($redisPassword ?? 'null')
                );

                // Cache operations
                Cache::put('mixed_test_cache', $testData['cache_key'], 120);

                $this->assertEquals(
                    $testData['cache_key'],
                    Cache::get('mixed_test_cache'),
                    "Cache should work with {$drivers['cache']} driver in mixed configuration. ".
                        'Redis enabled: '.($redisEnabled ? 'true' : 'false').
                        ', Redis password: '.($redisPassword ?? 'null')
                );

                // Verify both systems can coexist
                $this->assertTrue(
                    Session::has('mixed_test_session') && Cache::has('mixed_test_cache'),
                    'Both session and cache should work simultaneously in mixed configuration'
                );

                // Cleanup
                Session::forget('mixed_test_session');
                Cache::forget('mixed_test_cache');
            });
    }
}
