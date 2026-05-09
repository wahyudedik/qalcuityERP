<?php

namespace Tests\Property;

use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Predis\Connection\ConnectionException;
use RedisException;
use Tests\TestCase;

/**
 * Property-Based Tests for Redis Authentication Bug Condition.
 *
 * Feature: redis-auth-fix
 *
 * This test encodes the EXPECTED behavior (Property 1: Bug Condition Fix).
 * It was written to FAIL on unfixed code (placeholder password in .env) and
 * PASS on fixed code (real credentials or no-auth Redis in dev).
 *
 * The fix applied:
 * - .env REDIS_PASSWORD no longer contains the placeholder "your_actual_redis_password_here"
 * - config/database.php has enhanced fallback and error handling
 * - RedisHealthService provides proactive monitoring
 *
 * **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
 */
class RedisAuthBugConditionTest extends TestCase
{
    use TestTrait;

    /**
     * Property 1: Expected Behavior - Redis Authentication Success with Valid Credentials
     *
     * For any Redis connection attempt using the configured environment credentials
     * (not placeholder values), the fixed system SHALL successfully connect and
     * perform cache, session, and queue operations without NOAUTH errors.
     *
     * This test verifies the fix: the .env no longer contains the placeholder password
     * "your_actual_redis_password_here", so Redis operations succeed.
     *
     * **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
     *
     * **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
     */
    #[ErisRepeat(repeat: 3)]
    public function test_redis_authentication_succeeds_with_configured_credentials(): void
    {
        // Verify the fix: configured password must NOT be the placeholder
        $configuredPassword = env('REDIS_PASSWORD');
        $this->assertNotEquals(
            'your_actual_redis_password_here',
            $configuredPassword,
            'REDIS_PASSWORD must not be the placeholder value - this is the bug condition. '.
                'The fix requires setting a real password or null for no-auth Redis.'
        );

        $this
            ->forAll(
                Generators::elements([
                    'redis',
                    'database',
                ]), // session drivers to test
                Generators::elements([
                    'redis',
                    'database',
                ]) // cache drivers to test
            )
            ->then(function ($sessionDriver, $cacheDriver) {
                // Skip test if both drivers are database (no Redis involved)
                if ($sessionDriver === 'database' && $cacheDriver === 'database') {
                    $this->markTestSkipped('No Redis operations to test');
                }

                // Use the actual configured credentials (not placeholder)
                // This is the key difference from the unfixed state
                $actualPassword = env('REDIS_PASSWORD');
                Config::set('database.redis.default.password', $actualPassword);
                Config::set('database.redis.cache.password', $actualPassword);

                // Set session and cache drivers to use Redis
                Config::set('session.driver', $sessionDriver);
                Config::set('cache.default', $cacheDriver);

                $authFailureDetected = false;
                $exceptionMessage = '';

                try {
                    // Test 1: Direct Redis connection attempt with configured credentials
                    if ($sessionDriver === 'redis' || $cacheDriver === 'redis') {
                        // Purge any cached connections to force reconnect with new config
                        app('redis')->purge('default');
                        $redis = app('redis');
                        $redis->connection('default')->ping();
                    }

                    // Test 2: Cache operations (if using Redis cache)
                    if ($cacheDriver === 'redis') {
                        $testKey = 'redis_auth_fix_test_'.uniqid();
                        Cache::store('redis')->put($testKey, 'test_cache_value', 60);
                        $cachedValue = Cache::store('redis')->get($testKey);

                        // This should succeed with proper authentication
                        $this->assertEquals(
                            'test_cache_value',
                            $cachedValue,
                            'Cache operations should succeed with proper Redis authentication'
                        );

                        // Clean up
                        Cache::store('redis')->forget($testKey);
                    }
                } catch (RedisException $e) {
                    $authFailureDetected = true;
                    $exceptionMessage = $e->getMessage();
                } catch (ConnectionException $e) {
                    $authFailureDetected = true;
                    $exceptionMessage = $e->getMessage();
                } catch (\Exception $e) {
                    // Check if it's a Redis authentication error wrapped in another exception
                    if (
                        str_contains($e->getMessage(), 'NOAUTH') ||
                        str_contains($e->getMessage(), 'Authentication required') ||
                        str_contains($e->getMessage(), 'ERR AUTH')
                    ) {
                        $authFailureDetected = true;
                        $exceptionMessage = $e->getMessage();
                    } else {
                        // Re-throw if it's not a Redis auth error
                        throw $e;
                    }
                }

                // CRITICAL: This assertion encodes the EXPECTED behavior (successful auth)
                // On UNFIXED code with placeholder passwords, this FAILED
                // Now that the bug is fixed, this PASSES
                $this->assertFalse(
                    $authFailureDetected,
                    'Redis authentication should succeed with configured credentials. '.
                        'Auth failure detected with configured password. '.
                        "Error: {$exceptionMessage}. ".
                        'This indicates the fix may not be complete.'
                );
            });
    }

    /**
     * Property 1b: Expected Behavior - Configured Password is Not Placeholder
     *
     * Verify that the environment no longer contains the placeholder password
     * that caused the original bug. This is the core fix verification.
     *
     * **EXPECTED OUTCOME**: Test PASSES (confirms placeholder is removed)
     *
     * **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
     */
    public function test_configured_password_is_not_placeholder(): void
    {
        $configuredPassword = env('REDIS_PASSWORD');

        // The fix: REDIS_PASSWORD must not be the placeholder value
        $this->assertNotEquals(
            'your_actual_redis_password_here',
            $configuredPassword,
            'REDIS_PASSWORD must not be the placeholder "your_actual_redis_password_here". '.
                'This is the root cause of the original bug. '.
                'Set REDIS_PASSWORD to the actual Redis server password, or null/empty for no-auth Redis.'
        );

        // Also verify the config/database.php reads from the environment correctly
        $dbConfigPassword = config('database.redis.default.password');
        $this->assertNotEquals(
            'your_actual_redis_password_here',
            $dbConfigPassword,
            'database.redis.default.password config must not be the placeholder value. '.
                'Ensure config/database.php reads REDIS_PASSWORD from environment.'
        );
    }

    /**
     * Property 1c: Expected Behavior - Redis Connection Succeeds with Configured Credentials
     *
     * Verify that a direct Redis connection using the configured credentials succeeds.
     * This confirms the fix works end-to-end.
     *
     * **EXPECTED OUTCOME**: Test PASSES (confirms Redis connectivity works)
     *
     * **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
     */
    public function test_redis_connection_succeeds_with_configured_credentials(): void
    {
        $configuredPassword = env('REDIS_PASSWORD');

        // Verify the fix is in place
        $this->assertNotEquals(
            'your_actual_redis_password_here',
            $configuredPassword,
            'REDIS_PASSWORD must not be the placeholder value'
        );

        $connectionSucceeded = false;
        $exceptionMessage = '';

        try {
            // Use configured credentials (the fix ensures these are valid)
            Config::set('database.redis.default.password', $configuredPassword);

            // Purge cached connections to force reconnect
            app('redis')->purge('default');

            $redis = app('redis');
            $result = $redis->connection('default')->ping();

            // ping() returns true/1/'+PONG' depending on client
            $connectionSucceeded = ($result === true || $result === 1 || $result === '+PONG' || $result === 'PONG');
        } catch (RedisException $e) {
            $exceptionMessage = $e->getMessage();
        } catch (ConnectionException $e) {
            $exceptionMessage = $e->getMessage();
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }

        $this->assertTrue(
            $connectionSucceeded,
            'Redis connection should succeed with configured credentials. '.
                "Error: {$exceptionMessage}. ".
                'Ensure Redis server is running and REDIS_PASSWORD matches server configuration.'
        );
    }

    /**
     * Property 1d: Expected Behavior - Cache Operations Work with Redis
     *
     * Verify that cache operations using Redis succeed with the configured credentials.
     * This confirms requirement 2.2 (cache operations work correctly).
     *
     * **EXPECTED OUTCOME**: Test PASSES (confirms cache operations work)
     *
     * **Validates: Requirements 2.2, 2.4**
     */
    public function test_cache_operations_succeed_with_redis(): void
    {
        $configuredPassword = env('REDIS_PASSWORD');

        // Verify the fix is in place
        $this->assertNotEquals(
            'your_actual_redis_password_here',
            $configuredPassword,
            'REDIS_PASSWORD must not be the placeholder value'
        );

        // Use configured credentials
        Config::set('database.redis.default.password', $configuredPassword);
        Config::set('database.redis.cache.password', $configuredPassword);

        // Purge cached connections
        app('redis')->purge('default');
        app('redis')->purge('cache');

        $testKey = 'redis_auth_fix_cache_test_'.uniqid();
        $testValue = 'cache_test_value_'.time();

        try {
            // Store a value in Redis cache
            Cache::store('redis')->put($testKey, $testValue, 60);

            // Retrieve the value
            $retrieved = Cache::store('redis')->get($testKey);

            $this->assertEquals(
                $testValue,
                $retrieved,
                'Cache operations should succeed with proper Redis authentication. '.
                    'Requirement 2.2: Cache operations authenticate successfully.'
            );

            // Verify cache has the key
            $this->assertTrue(
                Cache::store('redis')->has($testKey),
                'Cache should contain the test key after successful Redis authentication'
            );

            // Clean up
            Cache::store('redis')->forget($testKey);
        } catch (\Exception $e) {
            $this->fail(
                'Cache operations failed with configured Redis credentials. '.
                    "Error: {$e->getMessage()}. ".
                    'This indicates the Redis authentication fix is not working correctly.'
            );
        }
    }
}
