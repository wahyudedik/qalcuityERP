<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\UseCaseRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test untuk response time warning di UseCaseRouter.
 *
 * Requirements: 10.7
 */
class UseCaseRouterResponseTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_logs_warning_when_response_time_exceeds_threshold(): void
    {
        // Arrange
        Config::set('ai.response_time_threshold_ms', 1000); // 1 detik threshold

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === '[UseCaseRouter] Response time melebihi threshold'
                    && $context['use_case'] === 'test_use_case'
                    && $context['provider'] === 'gemini'
                    && $context['response_time_ms'] === 2000
                    && $context['threshold_ms'] === 1000;
            });

        // Act - simulasi dengan reflection untuk memanggil private method
        $router = app(UseCaseRouter::class);
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('checkResponseTimeThreshold');
        $method->setAccessible(true);

        $method->invoke($router, 'test_use_case', 'gemini', 2000);

        // Assert - handled by Log::shouldReceive
    }

    /** @test */
    public function test_it_does_not_log_warning_when_response_time_below_threshold(): void
    {
        // Arrange
        Config::set('ai.response_time_threshold_ms', 30000); // 30 detik threshold

        Log::shouldReceive('warning')->never();

        // Act
        $router = app(UseCaseRouter::class);
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('checkResponseTimeThreshold');
        $method->setAccessible(true);

        $method->invoke($router, 'test_use_case', 'gemini', 5000); // 5 detik

        // Assert - handled by Log::shouldReceive
    }

    /** @test */
    public function test_it_uses_default_threshold_when_not_configured(): void
    {
        // Arrange
        Config::set('ai.response_time_threshold_ms', null);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['threshold_ms'] === 30000; // Default 30 detik
            });

        // Act
        $router = app(UseCaseRouter::class);
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('checkResponseTimeThreshold');
        $method->setAccessible(true);

        $method->invoke($router, 'test_use_case', 'gemini', 35000);

        // Assert - handled by Log::shouldReceive
    }
}
