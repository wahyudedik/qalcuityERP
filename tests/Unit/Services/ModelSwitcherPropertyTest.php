<?php

namespace Tests\Unit\Services;

use App\Events\AllModelsUnavailable;
use App\Exceptions\AllModelsUnavailableException;
use App\Models\SystemSetting;
use App\Services\AI\ModelSwitcher;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Property-Based Tests for ModelSwitcher.
 *
 * Feature: gemini-model-auto-switching
 */
class ModelSwitcherPropertyTest extends TestCase
{
    use TestTrait;

    /** @var array<string> */
    private array $fallbackChain;

    protected function setUp(): void
    {
        parent::setUp();

        // Use a fixed fallback chain for all tests so config is predictable
        $this->fallbackChain = [
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-1.5-flash',
            'gemini-2.5-pro',
        ];

        config([
            'gemini.model'            => 'gemini-2.5-flash',
            'gemini.fallback_models'  => $this->fallbackChain,
            'gemini.rate_limit_cooldown' => 60,
            'gemini.quota_cooldown'   => 3600,
        ]);

        // Clear all switcher cache keys before each test
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // reset time mock
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: build a fresh ModelSwitcher backed by the real array cache store
    // ─────────────────────────────────────────────────────────────────────────

    private function makeSwitcher(): ModelSwitcher
    {
        return new ModelSwitcher(Cache::store('array'));
    }

    // =========================================================================
    // Property 1: Active model persistence
    //
    // For any model name, after setActiveModel() is called, getActiveModel()
    // must return that exact model — not the primary or any other model.
    //
    // Feature: gemini-model-auto-switching, Property 1: Active model persistence
    // Validates: Requirements 1.1, 1.2
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testActiveModelPersistence(): void
    {
        $this
            ->forAll(
                Generators::elements($this->fallbackChain)
            )
            ->then(function (string $model) {
                // Fresh switcher + fresh cache for each iteration
                Cache::store('array')->flush();
                $switcher = $this->makeSwitcher();

                // Mark all OTHER models unavailable so getActiveModel() won't
                // fall back to primary when $model is not primary
                foreach ($this->fallbackChain as $m) {
                    if ($m !== $model) {
                        $switcher->markUnavailable($m, 'rate_limit');
                    }
                }

                $switcher->setActiveModel($model);

                $this->assertSame(
                    $model,
                    $switcher->getActiveModel(),
                    "After setActiveModel('{$model}'), getActiveModel() must return '{$model}'."
                );
            });
    }

    // =========================================================================
    // Property 2: Fallback chain respects order and skips cooldowns
    //
    // For any subset of models currently in cooldown, nextAvailableModel()
    // must return the first model in chain order that is NOT in the unavailable
    // set. If all models are in cooldown, AllModelsUnavailableException is thrown.
    //
    // Feature: gemini-model-auto-switching, Property 2: Fallback chain respects order and skips cooldowns
    // Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.7
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testFallbackChainSkipsUnavailableModels(): void
    {
        Event::fake([AllModelsUnavailable::class]);

        $this
            ->forAll(
                // Number of models to mark unavailable (0 to chain length)
                Generators::choose(0, count($this->fallbackChain))
            )
            ->then(function (int $unavailableCount) {
                Cache::store('array')->flush();
                $switcher = $this->makeSwitcher();

                // Mark the first $unavailableCount models as unavailable
                $unavailable = array_slice($this->fallbackChain, 0, $unavailableCount);
                foreach ($unavailable as $m) {
                    $switcher->markUnavailable($m, 'rate_limit');
                }

                $failedModel = $this->fallbackChain[0]; // always "fail" from primary

                if ($unavailableCount >= count($this->fallbackChain)) {
                    // All models exhausted — must throw and dispatch event
                    $this->expectException(AllModelsUnavailableException::class);
                    $switcher->nextAvailableModel($failedModel);
                    Event::assertDispatched(AllModelsUnavailable::class);
                } else {
                    // Must return the first available model in chain order
                    $expected = null;
                    foreach ($this->fallbackChain as $m) {
                        if ($m !== $failedModel && !in_array($m, $unavailable, true)) {
                            $expected = $m;
                            break;
                        }
                    }

                    $result = $switcher->nextAvailableModel($failedModel);

                    $this->assertSame(
                        $expected,
                        $result,
                        "nextAvailableModel() must return the first available model in chain order. " .
                        "unavailable=" . implode(',', $unavailable) . ", expected={$expected}, got={$result}"
                    );
                }
            });
    }

    // =========================================================================
    // Property 3: Cooldown duration invariant
    //
    // A model marked unavailable must remain unavailable for the full cooldown
    // window and become available again once the window passes.
    //
    // Feature: gemini-model-auto-switching, Property 3: Cooldown duration invariant
    // Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testCooldownDurationInvariant(): void
    {
        $this
            ->forAll(
                Generators::elements(['rate_limit', 'quota_exceeded', 'service_unavailable']),
                Generators::elements($this->fallbackChain)
            )
            ->then(function (string $reason, string $model) {
                Cache::store('array')->flush();
                $switcher = $this->makeSwitcher();

                $cooldown = match ($reason) {
                    'quota_exceeded' => 3600,
                    default          => 60,
                };

                $now = Carbon::now();
                Carbon::setTestNow($now);

                $switcher->markUnavailable($model, $reason);

                // ── Model must be unavailable immediately after marking ──
                $availability = $switcher->getModelAvailability();
                $entry = collect($availability)->firstWhere('model', $model);

                $this->assertNotNull($entry, "getModelAvailability() must include model '{$model}'.");
                $this->assertFalse(
                    $entry['available'],
                    "Model '{$model}' must be unavailable immediately after markUnavailable(reason={$reason})."
                );
                $this->assertSame(
                    $reason,
                    $entry['reason'],
                    "Unavailability reason must be stored correctly."
                );
                $this->assertNotNull(
                    $entry['recovers_at'],
                    "recovers_at must be set for unavailable model."
                );

                // ── Model must still be unavailable just before cooldown expires ──
                Carbon::setTestNow($now->copy()->addSeconds($cooldown - 1));
                $midAvailability = $switcher->getModelAvailability();
                $midEntry = collect($midAvailability)->firstWhere('model', $model);

                $this->assertFalse(
                    $midEntry['available'],
                    "Model '{$model}' must still be unavailable {$cooldown}-1 seconds after marking."
                );

                // ── Model must be available again after cooldown expires ──
                Carbon::setTestNow($now->copy()->addSeconds($cooldown + 1));
                $afterAvailability = $switcher->getModelAvailability();
                $afterEntry = collect($afterAvailability)->firstWhere('model', $model);

                $this->assertTrue(
                    $afterEntry['available'],
                    "Model '{$model}' must be available again after cooldown of {$cooldown}s expires (reason={$reason})."
                );
                $this->assertNull($afterEntry['reason']);
                $this->assertNull($afterEntry['recovers_at']);

                Carbon::setTestNow();
            });
    }

    // =========================================================================
    // Property 4: Recovery round-trip to primary model
    //
    // After the primary model's cooldown expires, getActiveModel() must return
    // the primary model again (for retry on next request).
    //
    // Feature: gemini-model-auto-switching, Property 4: Recovery round-trip to primary model
    // Validates: Requirements 4.1, 4.2
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testRecoveryRoundTripToPrimaryModel(): void
    {
        $this
            ->forAll(
                Generators::elements(['rate_limit', 'quota_exceeded'])
            )
            ->then(function (string $reason) {
                Cache::store('array')->flush();
                $switcher = $this->makeSwitcher();

                $primary  = config('gemini.model');
                $cooldown = $reason === 'quota_exceeded' ? 3600 : 60;

                $now = Carbon::now();
                Carbon::setTestNow($now);

                // Simulate: primary fails, switch to fallback
                $switcher->markUnavailable($primary, $reason);
                $fallback = $switcher->nextAvailableModel($primary);
                $switcher->setActiveModel($fallback);

                // During cooldown — active model must NOT be primary
                $this->assertNotSame(
                    $primary,
                    $switcher->getActiveModel(),
                    "During cooldown, getActiveModel() must not return primary '{$primary}'."
                );

                // After cooldown expires — getActiveModel() must return primary
                Carbon::setTestNow($now->copy()->addSeconds($cooldown + 1));

                $this->assertSame(
                    $primary,
                    $switcher->getActiveModel(),
                    "After cooldown of {$cooldown}s, getActiveModel() must return primary '{$primary}' for retry (reason={$reason})."
                );

                Carbon::setTestNow();
            });
    }

    // =========================================================================
    // Property 8: Switch frequency warning threshold
    //
    // When >= 10 switch events occur within a 60-minute window, a Laravel
    // warning must be logged. The counter increments monotonically.
    //
    // Feature: gemini-model-auto-switching, Property 8: Switch frequency warning threshold
    // Validates: Requirements 10.3
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testSwitchCountThresholdWarning(): void
    {
        $this
            ->forAll(
                Generators::choose(10, 20) // N >= 10 switches
            )
            ->then(function (int $n) {
                Cache::store('array')->flush();
                $switcher = $this->makeSwitcher();

                $warningLogged = false;

                // Intercept Log::warning via a spy on the underlying channel
                Log::listen(function (\Illuminate\Log\Events\MessageLogged $event) use (&$warningLogged) {
                    if ($event->level === 'warning'
                        && str_contains($event->message, 'high switch frequency')) {
                        $warningLogged = true;
                    }
                });

                // Trigger N markUnavailable calls — each increments the switch counter.
                // Use unique model names per iteration so unavailability entries don't
                // collide and each call always reaches incrementSwitchCount().
                for ($i = 0; $i < $n; $i++) {
                    $switcher->markUnavailable('model_iter_' . $i, 'rate_limit');
                }

                $this->assertTrue(
                    $warningLogged,
                    "Log::warning('high switch frequency') must be emitted when switch count reaches >= 10 (n={$n})."
                );
            });
    }

    // =========================================================================
    // Property 9: SystemSetting precedence and cache invalidation
    //
    // When gemini_fallback_models is set in SystemSetting, getFallbackChain()
    // must return that value. After resetAll(), stale unavailability state is
    // cleared so the new chain takes effect cleanly.
    //
    // Feature: gemini-model-auto-switching, Property 9: SystemSetting precedence and cache invalidation
    // Validates: Requirements 8.1, 8.3, 8.5
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function testSystemSettingChainPrecedenceAndCacheInvalidation(): void
    {
        $this
            ->forAll(
                // Generate a non-empty subset size (1–3 models) for the custom chain
                Generators::choose(1, 3)
            )
            ->then(function (int $chainSize) {
                Cache::store('array')->flush();
                SystemSetting::clearCache();

                $switcher = $this->makeSwitcher();

                // Build a custom chain that differs from the config default
                $customChain = array_slice([
                    'gemini-custom-a',
                    'gemini-custom-b',
                    'gemini-custom-c',
                ], 0, $chainSize);

                // ── Before setting SystemSetting — must use config chain ──
                $this->assertSame(
                    $this->fallbackChain,
                    $switcher->getFallbackChain(),
                    "Before SystemSetting is set, getFallbackChain() must return config chain."
                );

                // ── Set SystemSetting ──
                SystemSetting::set('gemini_fallback_models', json_encode($customChain), false, 'ai');
                SystemSetting::clearCache(); // ensure getCached() re-reads from DB

                // ── After setting SystemSetting — must use custom chain ──
                $chainAfter = $switcher->getFallbackChain();
                $this->assertSame(
                    $customChain,
                    $chainAfter,
                    "After SystemSetting::set('gemini_fallback_models'), getFallbackChain() must return the custom chain. " .
                    "Expected=" . json_encode($customChain) . ", Got=" . json_encode($chainAfter)
                );

                // ── Mark a model from the OLD config chain as unavailable ──
                $oldModel = $this->fallbackChain[1]; // 'gemini-2.5-flash-lite'
                $switcher->markUnavailable($oldModel, 'rate_limit');

                // ── resetAll() must clear unavailability state ──
                $switcher->resetAll();

                // After reset, getModelAvailability() uses the NEW chain
                // and must show all models as available (stale state cleared)
                $availability = $switcher->getModelAvailability();

                foreach ($availability as $entry) {
                    $this->assertTrue(
                        $entry['available'],
                        "After resetAll(), all models in the new chain must be available. " .
                        "Model '{$entry['model']}' is still unavailable."
                    );
                }

                // Cleanup SystemSetting for next iteration
                SystemSetting::where('key', 'gemini_fallback_models')->delete();
                SystemSetting::clearCache();
            });
    }
}
