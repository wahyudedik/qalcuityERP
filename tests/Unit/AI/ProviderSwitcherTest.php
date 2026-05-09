<?php

namespace Tests\Unit\AI;

use App\Contracts\AiProvider;
use App\Events\AllModelsUnavailable;
use App\Exceptions\AllProvidersUnavailableException;
use App\Services\AI\ProviderSwitcher;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk ProviderSwitcher.
 *
 * Feature: multi-ai-provider
 * Requirements: 3.3, 3.8
 */
class ProviderSwitcherTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat instance ProviderSwitcher dengan ArrayStore (in-memory cache).
     */
    private function makeSwitcher(): ProviderSwitcher
    {
        $cache = new Repository(new ArrayStore);

        return new ProviderSwitcher($cache);
    }

    /**
     * Buat mock AiProvider menggunakan anonymous class.
     */
    private function makeProvider(string $name): AiProvider
    {
        return new class($name) implements AiProvider
        {
            public function __construct(private string $name) {}

            public function getProviderName(): string
            {
                return $this->name;
            }

            public function isAvailable(): bool
            {
                return true;
            }

            public function chat(string $prompt, array $history = [], array $options = []): array
            {
                return ['text' => 'response', 'model' => $this->name.'-model'];
            }

            public function generate(string $prompt, array $options = []): array
            {
                return ['text' => 'response', 'model' => $this->name.'-model'];
            }

            public function chatWithMedia(string $message, array $files, array $history = [], array $options = []): array
            {
                return ['text' => 'response', 'model' => $this->name.'-model'];
            }

            public function generateWithImage(string $prompt, string $imageData, string $mimeType): array
            {
                return ['text' => 'response', 'model' => $this->name.'-model'];
            }

            public function withTenantContext(string $context): static
            {
                return $this;
            }

            public function withLanguage(string $language): static
            {
                return $this;
            }
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.8 — Mode 'single' tidak melakukan fallback ke provider lain
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function single_mode_does_not_fallback_to_other_providers(): void
    {
        // Requirements: 3.8
        Config::set('ai.mode', 'single');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        // Mark primary provider (gemini) sebagai unavailable
        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        // Dalam mode 'single', harus melempar exception — tidak fallback ke anthropic
        $this->expectException(AllProvidersUnavailableException::class);

        $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
    }

    #[Test]
    public function single_mode_returns_primary_provider_when_available(): void
    {
        // Requirements: 3.8
        Config::set('ai.mode', 'single');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        // Primary provider tersedia — harus mengembalikan gemini, bukan anthropic
        $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

        $this->assertSame($gemini, $result);
        $this->assertSame('gemini', $result->getProviderName());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.3 — AllProvidersUnavailableException dilempar ketika semua provider
    //        dalam cooldown
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function all_providers_unavailable_throws_exception(): void
    {
        // Requirements: 3.3
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        // Mark semua provider sebagai unavailable
        $switcher->markProviderUnavailable('gemini', 'rate_limit');
        $switcher->markProviderUnavailable('anthropic', 'rate_limit');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        $this->expectException(AllProvidersUnavailableException::class);

        $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
    }

    #[Test]
    public function all_providers_unavailable_exception_has_correct_message(): void
    {
        // Requirements: 3.3, 9.1
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        $switcher->markProviderUnavailable('gemini', 'rate_limit');
        $switcher->markProviderUnavailable('anthropic', 'rate_limit');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        try {
            $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
            $this->fail('Expected AllProvidersUnavailableException was not thrown.');
        } catch (AllProvidersUnavailableException $e) {
            $this->assertSame(
                'Layanan AI sedang tidak tersedia. Silakan coba beberapa saat lagi.',
                $e->getMessage()
            );
            $this->assertSame(503, $e->getCode());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.3 — Event AllModelsUnavailable di-dispatch ketika semua provider
    //        unavailable
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function all_providers_unavailable_dispatches_event(): void
    {
        // Requirements: 3.3
        Event::fake();
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        $switcher->markProviderUnavailable('gemini', 'rate_limit');
        $switcher->markProviderUnavailable('anthropic', 'rate_limit');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        try {
            $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
        } catch (AllProvidersUnavailableException) {
            // Exception diharapkan — kita hanya ingin memverifikasi event
        }

        Event::assertDispatched(AllModelsUnavailable::class);
    }

    #[Test]
    public function all_providers_unavailable_dispatches_event_in_single_mode(): void
    {
        // Requirements: 3.3, 3.8
        Event::fake();
        Config::set('ai.mode', 'single');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');

        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        $fallbackOrder = ['gemini'];
        $providerInstances = ['gemini' => $gemini];

        try {
            $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
        } catch (AllProvidersUnavailableException) {
            // Exception diharapkan
        }

        Event::assertDispatched(AllModelsUnavailable::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Failover mode — mengembalikan provider berikutnya yang tersedia
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function failover_mode_returns_next_available_provider(): void
    {
        // Requirements: 3.2, 3.7
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        // Mark provider pertama (gemini) sebagai unavailable
        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

        // Harus mengembalikan anthropic sebagai fallback
        $this->assertSame($anthropic, $result);
        $this->assertSame('anthropic', $result->getProviderName());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Provider tersedia — mengembalikan provider pertama dalam urutan
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function available_provider_is_returned_first(): void
    {
        // Requirements: 3.2
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();

        $gemini = $this->makeProvider('gemini');
        $anthropic = $this->makeProvider('anthropic');

        // Tidak ada provider dalam cooldown
        $fallbackOrder = ['gemini', 'anthropic'];
        $providerInstances = [
            'gemini' => $gemini,
            'anthropic' => $anthropic,
        ];

        $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

        // Harus mengembalikan provider pertama (gemini)
        $this->assertSame($gemini, $result);
        $this->assertSame('gemini', $result->getProviderName());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // markProviderUnavailable — isProviderAvailable() mengembalikan false
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function mark_provider_unavailable_sets_cooldown(): void
    {
        // Requirements: 3.6
        $switcher = $this->makeSwitcher();

        // Sebelum di-mark: provider tersedia
        $this->assertTrue($switcher->isProviderAvailable('gemini'));

        // Mark sebagai unavailable
        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        // Setelah di-mark: provider tidak tersedia
        $this->assertFalse($switcher->isProviderAvailable('gemini'));
    }

    #[Test]
    public function mark_provider_unavailable_with_quota_exceeded_sets_cooldown(): void
    {
        // Requirements: 3.6
        $switcher = $this->makeSwitcher();

        $this->assertTrue($switcher->isProviderAvailable('anthropic'));

        $switcher->markProviderUnavailable('anthropic', 'quota_exceeded');

        $this->assertFalse($switcher->isProviderAvailable('anthropic'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // resetProvider — menghapus cooldown
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function reset_provider_clears_cooldown(): void
    {
        // Requirements: 3.5
        $switcher = $this->makeSwitcher();

        $switcher->markProviderUnavailable('gemini', 'rate_limit');
        $this->assertFalse($switcher->isProviderAvailable('gemini'));

        $switcher->resetProvider('gemini');

        $this->assertTrue($switcher->isProviderAvailable('gemini'));
    }
}
