<?php

namespace Tests\Unit\Services;

use App\Events\AllModelsUnavailable;
use App\Services\AI\ModelSwitcher;
use App\Services\GeminiService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for GeminiService AllModelsUnavailable dispatch and user-friendly error.
 *
 * Feature: gemini-model-auto-switching
 * Property 7: AllModelsUnavailable event and user-friendly error
 *
 * Validates: Requirements 2.5, 6.4, 10.1
 */
class GeminiServiceAllModelsUnavailablePropertyTest extends TestCase
{
    use TestTrait;

    /** @var array<string> */
    private array $fallbackChain;

    /** @var ReflectionClass */
    private ReflectionClass $reflection;

    /** @var \ReflectionMethod */
    private \ReflectionMethod $callWithFallback;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fallbackChain = [
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-1.5-flash',
        ];

        config([
            'gemini.model'               => 'gemini-2.5-flash',
            'gemini.fallback_models'     => $this->fallbackChain,
            'gemini.rate_limit_cooldown' => 60,
            'gemini.quota_cooldown'      => 3600,
            'gemini.api_key'             => 'test-key',
        ]);

        Cache::flush();

        $this->reflection = new ReflectionClass(GeminiService::class);

        $this->callWithFallback = $this->reflection->getMethod('callWithFallback');
        $this->callWithFallback->setAccessible(true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: build a GeminiService instance backed by the array cache store.
    // ─────────────────────────────────────────────────────────────────────────

    private function makeService(): GeminiService
    {
        $switcher = new ModelSwitcher(Cache::store('array'));

        $service = $this->reflection->newInstanceWithoutConstructor();

        $this->setProperty($service, 'switcher', $switcher);
        $this->setProperty($service, 'models', $this->fallbackChain);
        $this->setProperty($service, 'activeModel', $this->fallbackChain[0]);
        $this->setProperty($service, 'rateLimitCodes', [429, 503, 500]);
        $this->setProperty($service, 'language', 'id');

        return $service;
    }

    private function setProperty(GeminiService $service, string $name, mixed $value): void
    {
        $prop = $this->reflection->getProperty($name);
        $prop->setAccessible(true);
        $prop->setValue($service, $value);
    }

    private function invokeCallWithFallback(GeminiService $service, callable $apiCall): array
    {
        return $this->callWithFallback->invoke($service, $apiCall);
    }

    // =========================================================================
    // Property 7: AllModelsUnavailable event and user-friendly error
    //
    // For any request where all models in the fallback chain are simultaneously
    // in cooldown at the time of the call, the AllModelsUnavailable event must
    // be dispatched, and the response returned to the caller must be the
    // user-friendly Indonesian error message — never a raw API error.
    //
    // Feature: gemini-model-auto-switching, Property 7: AllModelsUnavailable event and user-friendly error
    // Validates: Requirements 2.5, 6.4, 10.1
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testAllModelsUnavailableDispatchesEventAndReturnsUserFriendlyMessage(): void
    {
        // Feature: gemini-model-auto-switching, Property 7: AllModelsUnavailable event and user-friendly error

        $expectedMessage = 'Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.';

        $this
            ->forAll(
                // Generate an HTTP error code that triggers fallback (429 or 503)
                Generators::elements(429, 503)
            )
            ->then(function (int $errorCode) use ($expectedMessage) {
                Cache::store('array')->flush();
                Event::fake([AllModelsUnavailable::class]);

                $service = $this->makeService();

                // All models always return the given error code — none will succeed
                $apiCall = function (string $model) use ($errorCode) {
                    throw new \RuntimeException('Rate limit exceeded', $errorCode);
                };

                $response = $this->invokeCallWithFallback($service, $apiCall);

                // ── Assert: response is an array ──
                $this->assertIsArray(
                    $response,
                    'callWithFallback() must return an array even when all models are unavailable.'
                );

                // ── Assert: response contains 'text' key with the Indonesian user-friendly message ──
                $this->assertArrayHasKey(
                    'text',
                    $response,
                    "Response must contain 'text' key when all models are exhausted."
                );
                $this->assertSame(
                    $expectedMessage,
                    $response['text'],
                    "Response 'text' must be the user-friendly Indonesian error message, not a raw API error."
                );

                // ── Assert: response contains 'error' => true ──
                $this->assertArrayHasKey(
                    'error',
                    $response,
                    "Response must contain 'error' key when all models are exhausted."
                );
                $this->assertTrue(
                    $response['error'],
                    "Response 'error' must be true when all models are exhausted."
                );

                // ── Assert: AllModelsUnavailable event was dispatched ──
                Event::assertDispatched(
                    AllModelsUnavailable::class,
                    fn($event) => is_array($event->unavailableModels) && count($event->unavailableModels) > 0
                );
            });
    }

    #[ErisRepeat(repeat: 100)]
    public function testAllModelsUnavailableResponseNeverContainsRawApiError(): void
    {
        // Feature: gemini-model-auto-switching, Property 7: AllModelsUnavailable event and user-friendly error

        $expectedMessage = 'Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.';

        $this
            ->forAll(
                // Generate a random raw error message that should never appear in the response
                Generators::map(
                    fn(int $len) => 'API_ERROR_' . str_repeat('x', $len),
                    Generators::choose(1, 40)
                )
            )
            ->then(function (string $rawErrorMessage) use ($expectedMessage) {
                Cache::store('array')->flush();
                Event::fake([AllModelsUnavailable::class]);

                $service = $this->makeService();

                // All models throw a 429 with a raw error message
                $apiCall = function (string $model) use ($rawErrorMessage) {
                    throw new \RuntimeException($rawErrorMessage, 429);
                };

                $response = $this->invokeCallWithFallback($service, $apiCall);

                $this->assertIsArray($response);

                // ── Assert: raw error message is never exposed in the response ──
                $this->assertNotSame(
                    $rawErrorMessage,
                    $response['text'] ?? null,
                    "Raw API error message must never be returned to the caller."
                );

                // ── Assert: the user-friendly message is always returned ──
                $this->assertSame(
                    $expectedMessage,
                    $response['text'],
                    "Response must always contain the user-friendly Indonesian message when all models fail."
                );

                // ── Assert: AllModelsUnavailable event was dispatched ──
                Event::assertDispatched(AllModelsUnavailable::class);
            });
    }
}
