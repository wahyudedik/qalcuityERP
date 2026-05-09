<?php

namespace Tests\Unit\Services;

use App\Services\AI\ModelSwitcher;
use App\Services\GeminiService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for GeminiService response format invariance after fallback.
 *
 * Feature: gemini-model-auto-switching
 * Property 6: Response format invariance after fallback
 *
 * Validates: Requirements 6.1, 6.2, 6.3
 */
class GeminiServiceResponseFormatPropertyTest extends TestCase
{
    use TestTrait;

    /** @var array<string> */
    private array $fallbackChain;

    private \ReflectionMethod $callWithFallback;

    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fallbackChain = [
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-1.5-flash',
        ];

        config([
            'gemini.model' => 'gemini-2.5-flash',
            'gemini.fallback_models' => $this->fallbackChain,
            'gemini.rate_limit_cooldown' => 60,
            'gemini.quota_cooldown' => 3600,
            'gemini.api_key' => 'test-key',
        ]);

        Cache::flush();

        $this->reflection = new ReflectionClass(GeminiService::class);

        $this->callWithFallback = $this->reflection->getMethod('callWithFallback');
        $this->callWithFallback->setAccessible(true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: build a GeminiService instance without invoking the real
    // constructor, injecting a real ModelSwitcher backed by the array cache.
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

    /**
     * Invoke callWithFallback on the given service with the given closure.
     */
    private function invokeCallWithFallback(GeminiService $service, callable $apiCall): array
    {
        return $this->callWithFallback->invoke($service, $apiCall);
    }

    // =========================================================================
    // Property 6: Response format invariance after fallback
    //
    // For any successful API call that involved one or more fallback switches,
    // the response array returned by GeminiService must contain all the same
    // required keys as a response from a non-switched call, plus a `model` key
    // indicating the model that was ultimately used.
    //
    // Feature: gemini-model-auto-switching, Property 6: Response format invariance after fallback
    // Validates: Requirements 6.1, 6.2, 6.3
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function test_response_format_invariance_after_fallback(): void
    {
        // Required keys that must always be present in a chat() response
        $requiredKeys = ['text', 'model'];

        $this
            ->forAll(
                // Pick how many models fail with 429 before success (1 or 2)
                Generators::choose(1, count($this->fallbackChain) - 1),
                // Generate a random non-empty response text (1–50 chars)
                Generators::map(
                    fn (int $len) => str_repeat('a', $len),
                    Generators::choose(1, 50)
                )
            )
            ->then(function (int $failCount, string $responseText) use ($requiredKeys) {
                Cache::store('array')->flush();
                $service = $this->makeService();

                $callCount = 0;

                // Closure simulates: first $failCount calls throw 429, then succeeds
                $apiCall = function (string $model) use (&$callCount, $failCount, $responseText) {
                    $callCount++;
                    if ($callCount <= $failCount) {
                        throw new \RuntimeException('Too Many Requests', 429);
                    }

                    return $responseText;
                };

                $response = $this->invokeCallWithFallback($service, $apiCall);

                // ── Assert: response is an array ──
                $this->assertIsArray(
                    $response,
                    'callWithFallback() must always return an array, even after fallback.'
                );

                // ── Assert: all required keys are present ──
                foreach ($requiredKeys as $key) {
                    $this->assertArrayHasKey(
                        $key,
                        $response,
                        "Response after fallback must contain required key '{$key}'. ".
                        'Got keys: '.implode(', ', array_keys($response))
                    );
                }

                // ── Assert: 'model' key is a non-empty string ──
                $this->assertIsString($response['model']);
                $this->assertNotEmpty(
                    $response['model'],
                    "Response 'model' key must be a non-empty string indicating which model was used."
                );

                // ── Assert: 'model' is one of the known fallback chain models ──
                $this->assertContains(
                    $response['model'],
                    $this->fallbackChain,
                    "Response 'model' must be one of the configured fallback chain models. Got: {$response['model']}"
                );

                // ── Assert: 'text' contains the expected response text ──
                $this->assertSame(
                    $responseText,
                    $response['text'],
                    "Response 'text' must contain the actual API response text after fallback."
                );

                // ── Assert: 'switched_model' flag is set when fallback occurred ──
                $this->assertArrayHasKey(
                    'switched_model',
                    $response,
                    "Response must contain 'switched_model' key when a fallback switch occurred."
                );
                $this->assertTrue(
                    $response['switched_model'],
                    "Response 'switched_model' must be true when a fallback switch occurred."
                );

                // ── Assert: no error key on successful fallback ──
                $this->assertArrayNotHasKey(
                    'error',
                    $response,
                    "Successful fallback response must not contain an 'error' key."
                );
            });
    }

    // =========================================================================
    // Property 6 (non-switched baseline): Response format invariance without fallback
    //
    // A response from a non-switched call must also contain all required keys
    // including 'model', confirming format consistency regardless of switching.
    //
    // Feature: gemini-model-auto-switching, Property 6: Response format invariance after fallback
    // Validates: Requirements 6.1, 6.2, 6.3
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function test_response_format_invariance_without_fallback(): void
    {
        $requiredKeys = ['text', 'model'];

        $this
            ->forAll(
                Generators::map(
                    fn (int $len) => str_repeat('x', $len),
                    Generators::choose(1, 80)
                )
            )
            ->then(function (string $responseText) use ($requiredKeys) {
                Cache::store('array')->flush();
                $service = $this->makeService();

                // Client always succeeds on first call — no fallback needed
                $apiCall = fn (string $model) => $responseText;

                $response = $this->invokeCallWithFallback($service, $apiCall);

                $this->assertIsArray($response);

                foreach ($requiredKeys as $key) {
                    $this->assertArrayHasKey(
                        $key,
                        $response,
                        "Non-switched response must contain required key '{$key}'."
                    );
                }

                $this->assertIsString($response['model']);
                $this->assertNotEmpty($response['model']);
                $this->assertContains($response['model'], $this->fallbackChain);
                $this->assertSame($responseText, $response['text']);

                // No switched_model flag on a direct success
                $this->assertArrayNotHasKey(
                    'switched_model',
                    $response,
                    "Non-switched response must not contain 'switched_model' key."
                );
            });
    }
}
