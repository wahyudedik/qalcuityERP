<?php

namespace Tests\Property\AI;

use App\Contracts\AiProvider;
use App\Exceptions\AllProvidersUnavailableException;
use App\Services\AI\ProviderSwitcher;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Property-Based Tests untuk Fallback Provider.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 3.2, 3.3, 3.7**
 *
 * Property 3: Fallback provider menghindari provider dalam cooldown.
 *
 * Untuk SEMBARANG subset provider yang sedang dalam cooldown:
 *   - Jika subset TIDAK mencakup semua provider → getNextAvailableProvider()
 *     mengembalikan provider yang TIDAK ada dalam subset tersebut.
 *   - Jika subset mencakup SEMUA provider → AllProvidersUnavailableException dilempar.
 */
class ProviderFallbackTest extends TestCase
{
    use TestTrait;

    /**
     * Daftar semua provider yang tersedia dalam sistem.
     */
    private const ALL_PROVIDERS = ['gemini', 'anthropic'];

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat instance ProviderSwitcher dengan ArrayStore (in-memory cache)
     * yang terisolasi — tidak ada state yang bocor antar iterasi.
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

    /**
     * Buat map provider name → AiProvider instance untuk semua provider.
     *
     * @return array<string, AiProvider>
     */
    private function makeProviderInstances(): array
    {
        $instances = [];
        foreach (self::ALL_PROVIDERS as $name) {
            $instances[$name] = $this->makeProvider($name);
        }

        return $instances;
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 3: Fallback provider menghindari provider dalam cooldown.
     *
     * Untuk SEMBARANG subset provider yang di-mark unavailable:
     *   - Jika subset tidak mencakup semua provider → provider yang dikembalikan
     *     TIDAK ada dalam subset unavailable.
     *   - Jika subset mencakup semua provider → AllProvidersUnavailableException dilempar.
     *
     * Menggunakan Generators::subset(['gemini', 'anthropic']) yang menghasilkan
     * semua kemungkinan subset: [], ['gemini'], ['anthropic'], ['gemini', 'anthropic'].
     *
     * **Validates: Requirements 3.2, 3.3, 3.7**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_fallback_avoids_providers_in_cooldown(): void
    {
        Config::set('ai.mode', 'failover');

        $this
            ->forAll(
                Generators::subset(self::ALL_PROVIDERS)
            )
            ->then(function (array $unavailableProviders) {
                // Buat switcher baru per iterasi agar state cache terisolasi
                $switcher = $this->makeSwitcher();
                $providerInstances = $this->makeProviderInstances();
                $fallbackOrder = self::ALL_PROVIDERS;

                // Mark setiap provider dalam subset sebagai unavailable
                foreach ($unavailableProviders as $provider) {
                    $switcher->markProviderUnavailable($provider, 'rate_limit');
                }

                $allUnavailable = count($unavailableProviders) === count(self::ALL_PROVIDERS);

                if ($allUnavailable) {
                    // Semua provider dalam cooldown → harus melempar exception
                    try {
                        $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

                        $this->fail(
                            'AllProvidersUnavailableException harus dilempar ketika semua provider '.
                                'dalam cooldown. Unavailable: ['.implode(', ', $unavailableProviders).']. '.
                                'Property 3 dilanggar.'
                        );
                    } catch (AllProvidersUnavailableException $e) {
                        // Perilaku yang diharapkan — semua provider unavailable
                        $this->assertInstanceOf(
                            AllProvidersUnavailableException::class,
                            $e,
                            'Exception yang dilempar harus berupa AllProvidersUnavailableException'
                        );
                    }
                } else {
                    // Ada provider yang masih tersedia → harus mengembalikan salah satunya
                    try {
                        $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

                        // Provider yang dikembalikan TIDAK boleh ada dalam set unavailable
                        $this->assertNotContains(
                            $result->getProviderName(),
                            $unavailableProviders,
                            sprintf(
                                "Provider '%s' yang dikembalikan ada dalam set unavailable [%s]. ".
                                    'Property 3 dilanggar: fallback harus menghindari provider dalam cooldown.',
                                $result->getProviderName(),
                                implode(', ', $unavailableProviders)
                            )
                        );

                        // Provider yang dikembalikan harus merupakan salah satu dari provider yang dikenal
                        $this->assertContains(
                            $result->getProviderName(),
                            self::ALL_PROVIDERS,
                            sprintf(
                                "Provider '%s' yang dikembalikan bukan provider yang dikenal.",
                                $result->getProviderName()
                            )
                        );
                    } catch (AllProvidersUnavailableException $e) {
                        $availableProviders = array_diff(self::ALL_PROVIDERS, $unavailableProviders);

                        $this->fail(
                            sprintf(
                                'AllProvidersUnavailableException tidak boleh dilempar ketika masih ada '.
                                    'provider yang tersedia: [%s]. Unavailable: [%s]. Property 3 dilanggar.',
                                implode(', ', $availableProviders),
                                implode(', ', $unavailableProviders)
                            )
                        );
                    }
                }
            });
    }

    /**
     * Property 3 (variant): Provider yang dikembalikan adalah provider pertama
     * dalam fallback order yang tidak dalam cooldown.
     *
     * Memverifikasi bahwa ProviderSwitcher mengikuti urutan fallback yang ditentukan
     * dan mengembalikan provider PERTAMA yang tersedia, bukan sembarang provider.
     *
     * **Validates: Requirements 3.2, 3.7**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_fallback_returns_first_available_in_order(): void
    {
        Config::set('ai.mode', 'failover');

        $this
            ->forAll(
                Generators::subset(self::ALL_PROVIDERS)
            )
            ->then(function (array $unavailableProviders) {
                $switcher = $this->makeSwitcher();
                $providerInstances = $this->makeProviderInstances();
                $fallbackOrder = self::ALL_PROVIDERS;

                // Mark provider dalam subset sebagai unavailable
                foreach ($unavailableProviders as $provider) {
                    $switcher->markProviderUnavailable($provider, 'rate_limit');
                }

                // Hitung provider pertama yang seharusnya dikembalikan
                $expectedProvider = null;
                foreach ($fallbackOrder as $providerName) {
                    if (! in_array($providerName, $unavailableProviders, true)) {
                        $expectedProvider = $providerName;
                        break;
                    }
                }

                if ($expectedProvider === null) {
                    // Semua unavailable — verifikasi exception dilempar
                    $this->expectException(AllProvidersUnavailableException::class);
                    $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
                } else {
                    // Ada provider yang tersedia — verifikasi provider yang benar dikembalikan
                    $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

                    $this->assertSame(
                        $expectedProvider,
                        $result->getProviderName(),
                        sprintf(
                            "Provider yang dikembalikan harus '%s' (pertama dalam urutan yang tersedia), ".
                                "bukan '%s'. Unavailable: [%s]. Property 3 dilanggar.",
                            $expectedProvider,
                            $result->getProviderName(),
                            implode(', ', $unavailableProviders)
                        )
                    );
                }
            });
    }

    /**
     * Property 3 (edge case): Subset kosong — semua provider tersedia.
     *
     * Ketika tidak ada provider yang di-mark unavailable (subset kosong),
     * provider pertama dalam fallback order harus dikembalikan.
     *
     * **Validates: Requirements 3.2**
     */
    public function test_empty_unavailable_set_returns_first_provider(): void
    {
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();
        $providerInstances = $this->makeProviderInstances();
        $fallbackOrder = self::ALL_PROVIDERS;

        // Tidak ada provider yang di-mark unavailable
        $result = $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);

        $this->assertSame(
            self::ALL_PROVIDERS[0],
            $result->getProviderName(),
            'Ketika tidak ada provider dalam cooldown, provider pertama dalam urutan harus dikembalikan.'
        );
    }

    /**
     * Property 3 (edge case): Semua provider unavailable — exception harus dilempar.
     *
     * **Validates: Requirements 3.3**
     */
    public function test_all_providers_unavailable_throws_exception(): void
    {
        Config::set('ai.mode', 'failover');

        $switcher = $this->makeSwitcher();
        $providerInstances = $this->makeProviderInstances();
        $fallbackOrder = self::ALL_PROVIDERS;

        // Mark semua provider sebagai unavailable
        foreach (self::ALL_PROVIDERS as $provider) {
            $switcher->markProviderUnavailable($provider, 'rate_limit');
        }

        $this->expectException(AllProvidersUnavailableException::class);

        $switcher->getNextAvailableProvider($fallbackOrder, $providerInstances);
    }
}
