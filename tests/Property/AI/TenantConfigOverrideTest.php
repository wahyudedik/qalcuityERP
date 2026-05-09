<?php

namespace Tests\Property\AI;

use App\Services\AI\AiProviderRouter;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\ProviderSwitcher;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

/**
 * Property-Based Tests untuk Tenant Config Override.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 5.5, 5.6**
 *
 * Property 5: Konfigurasi tenant meng-override konfigurasi global.
 *
 * Untuk SEMBARANG kombinasi konfigurasi global dan tenant:
 *   - Jika tenant memiliki `ai_provider` yang di-set → resolveProvider(tenantId)
 *     mengembalikan provider yang dikonfigurasi tenant, bukan default global.
 *   - Jika tenant TIDAK memiliki `ai_provider` → resolveProvider(tenantId)
 *     mengembalikan provider dari konfigurasi global (SystemSetting atau config default).
 */
class TenantConfigOverrideTest extends TestCase
{
    use TestTrait;

    /**
     * Daftar semua provider yang valid dalam sistem.
     */
    private const ALL_PROVIDERS = ['gemini', 'anthropic'];

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat mock GeminiProvider menggunakan Mockery.
     */
    private function makeGeminiMock(): GeminiProvider
    {
        $mock = Mockery::mock(GeminiProvider::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $mock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $mock->shouldReceive('getProviderName')->andReturn('gemini')->byDefault();

        return $mock;
    }

    /**
     * Buat mock AnthropicProvider menggunakan Mockery.
     */
    private function makeAnthropicMock(): AnthropicProvider
    {
        $mock = Mockery::mock(AnthropicProvider::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $mock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $mock->shouldReceive('getProviderName')->andReturn('anthropic')->byDefault();

        return $mock;
    }

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
     * Buat instance AiProviderRouter dengan mock provider.
     */
    private function makeRouter(
        ?GeminiProvider $gemini = null,
        ?AnthropicProvider $anthropic = null,
        ?ProviderSwitcher $switcher = null,
    ): AiProviderRouter {
        return new AiProviderRouter(
            $gemini ?? $this->makeGeminiMock(),
            $anthropic ?? $this->makeAnthropicMock(),
            $switcher ?? $this->makeSwitcher(),
        );
    }

    /**
     * Pre-populate SystemSetting cache.
     * SystemSetting::getCached() uses Cache::remember('system_settings_all', ...).
     */
    private function setSystemSetting(string $key, string $value): void
    {
        $existing = Cache::get('system_settings_all', []);
        $existing[$key] = ['value' => $value, 'is_encrypted' => false];
        Cache::put('system_settings_all', $existing, 3600);
    }

    /**
     * Pre-populate TenantApiSetting cache for a specific tenant.
     * TenantApiSetting::getCached($tenantId) uses Cache::remember("tenant_api_settings_{$tenantId}", ...).
     */
    private function setTenantApiSetting(int $tenantId, string $key, string $value): void
    {
        $cacheKey = "tenant_api_settings_{$tenantId}";
        $existing = Cache::get($cacheKey, []);
        $existing[$key] = ['value' => $value, 'is_encrypted' => false, 'group' => 'ai'];
        Cache::put($cacheKey, $existing, 1800);
    }

    /**
     * Hapus TenantApiSetting cache untuk tenant tertentu.
     */
    private function clearTenantApiSetting(int $tenantId): void
    {
        Cache::forget("tenant_api_settings_{$tenantId}");
    }

    // ─── Setup / Teardown ─────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.fallback_order', ['gemini', 'anthropic']);
        Config::set('ai.mode', 'failover');
        Config::set('ai.providers.gemini.api_key', 'fake-gemini-key');
        Config::set('ai.providers.gemini.model', 'gemini-2.5-flash');
        Config::set('ai.providers.anthropic.api_key', 'fake-anthropic-key');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');

        // Bersihkan cache settings agar setiap test mulai dari state bersih
        Cache::forget('system_settings_all');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 5a: Untuk SEMBARANG kombinasi global provider + tenant provider,
     * konfigurasi tenant SELALU menang atas konfigurasi global.
     *
     * Generator:
     *   - globalProvider: salah satu dari ['gemini', 'anthropic']
     *   - tenantProvider: salah satu dari ['gemini', 'anthropic']
     *   - tenantId: integer positif (1–1000)
     *
     * Assert: resolveProvider(tenantId) mengembalikan tenantProvider,
     * bukan globalProvider.
     *
     * **Validates: Requirements 5.5, 5.6**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_tenant_provider_always_overrides_global_provider(): void
    {
        $this
            ->forAll(
                Generators::elements(self::ALL_PROVIDERS),
                Generators::elements(self::ALL_PROVIDERS),
                Generators::choose(1, 1000)
            )
            ->then(function (string $globalProvider, string $tenantProvider, int $tenantId) {
                // Bersihkan cache untuk isolasi antar iterasi
                Cache::forget('system_settings_all');
                $this->clearTenantApiSetting($tenantId);

                // Set konfigurasi global via SystemSetting cache
                $this->setSystemSetting('ai_default_provider', $globalProvider);

                // Set konfigurasi tenant via TenantApiSetting cache
                $this->setTenantApiSetting($tenantId, 'ai_provider', $tenantProvider);

                $router = $this->makeRouter();
                $provider = $router->resolveProvider($tenantId);

                $this->assertSame(
                    $tenantProvider,
                    $provider->getProviderName(),
                    sprintf(
                        "resolveProvider(%d) harus mengembalikan provider tenant '%s', ".
                            "bukan provider global '%s'. Property 5 dilanggar.",
                        $tenantId,
                        $tenantProvider,
                        $globalProvider
                    )
                );
            });
    }

    /**
     * Property 5b: Untuk SEMBARANG global provider, ketika tenant TIDAK memiliki
     * override, resolveProvider(tenantId) mengembalikan provider global.
     *
     * Generator:
     *   - globalProvider: salah satu dari ['gemini', 'anthropic']
     *   - tenantId: integer positif (1–1000)
     *
     * Assert: resolveProvider(tenantId) mengembalikan globalProvider.
     *
     * **Validates: Requirements 5.6**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_global_provider_used_when_tenant_has_no_override(): void
    {
        $this
            ->forAll(
                Generators::elements(self::ALL_PROVIDERS),
                Generators::choose(1, 1000)
            )
            ->then(function (string $globalProvider, int $tenantId) {
                // Bersihkan cache untuk isolasi antar iterasi
                Cache::forget('system_settings_all');
                $this->clearTenantApiSetting($tenantId);

                // Set konfigurasi global via SystemSetting cache
                $this->setSystemSetting('ai_default_provider', $globalProvider);

                // Tenant TIDAK memiliki override (cache kosong untuk tenant ini)

                $router = $this->makeRouter();
                $provider = $router->resolveProvider($tenantId);

                $this->assertSame(
                    $globalProvider,
                    $provider->getProviderName(),
                    sprintf(
                        "resolveProvider(%d) harus mengembalikan provider global '%s' ".
                            'ketika tenant tidak memiliki override. Property 5 dilanggar.',
                        $tenantId,
                        $globalProvider
                    )
                );
            });
    }

    /**
     * Property 5c: Untuk SEMBARANG kombinasi di mana tenant provider BERBEDA
     * dari global provider, tenant provider yang dikembalikan.
     *
     * Ini memverifikasi secara eksplisit bahwa override benar-benar terjadi
     * ketika kedua provider berbeda (bukan hanya kebetulan sama).
     *
     * Generator:
     *   - tenantId: integer positif (1–1000)
     *
     * Kombinasi yang diuji:
     *   - global='gemini', tenant='anthropic' → harus mengembalikan 'anthropic'
     *   - global='anthropic', tenant='gemini' → harus mengembalikan 'gemini'
     *
     * **Validates: Requirements 5.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_tenant_provider_returned_when_differs_from_global(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 1000)
            )
            ->then(function (int $tenantId) {
                // ── Kasus 1: global=gemini, tenant=anthropic ──
                Cache::forget('system_settings_all');
                $this->clearTenantApiSetting($tenantId);

                $this->setSystemSetting('ai_default_provider', 'gemini');
                $this->setTenantApiSetting($tenantId, 'ai_provider', 'anthropic');

                $router = $this->makeRouter();
                $provider = $router->resolveProvider($tenantId);

                $this->assertSame(
                    'anthropic',
                    $provider->getProviderName(),
                    sprintf(
                        "Kasus 1: resolveProvider(%d) harus mengembalikan 'anthropic' ".
                            "ketika global='gemini' dan tenant='anthropic'. Property 5 dilanggar.",
                        $tenantId
                    )
                );

                // ── Kasus 2: global=anthropic, tenant=gemini ──
                Cache::forget('system_settings_all');
                $this->clearTenantApiSetting($tenantId);

                $this->setSystemSetting('ai_default_provider', 'anthropic');
                $this->setTenantApiSetting($tenantId, 'ai_provider', 'gemini');

                $router2 = $this->makeRouter();
                $provider2 = $router2->resolveProvider($tenantId);

                $this->assertSame(
                    'gemini',
                    $provider2->getProviderName(),
                    sprintf(
                        "Kasus 2: resolveProvider(%d) harus mengembalikan 'gemini' ".
                            "ketika global='anthropic' dan tenant='gemini'. Property 5 dilanggar.",
                        $tenantId
                    )
                );
            });
    }

    // ─── Edge Case Tests ──────────────────────────────────────────

    /**
     * Edge case: Tenant override mengalahkan config default (tanpa SystemSetting).
     *
     * Ketika SystemSetting tidak di-set (cache kosong), config default digunakan
     * sebagai global. Tenant override tetap harus menang.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_tenant_override_beats_config_default(): void
    {
        // Tidak ada SystemSetting → config default = 'gemini'
        Config::set('ai.default_provider', 'gemini');
        Cache::forget('system_settings_all');

        $tenantId = 42;
        $this->clearTenantApiSetting($tenantId);
        $this->setTenantApiSetting($tenantId, 'ai_provider', 'anthropic');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider($tenantId);

        $this->assertSame(
            'anthropic',
            $provider->getProviderName(),
            "Tenant override 'anthropic' harus menang atas config default 'gemini'."
        );
    }

    /**
     * Edge case: Tanpa tenant ID (null), konfigurasi global digunakan.
     *
     * **Validates: Requirements 5.6**
     */
    public function test_global_provider_used_when_tenant_id_is_null(): void
    {
        $this->setSystemSetting('ai_default_provider', 'anthropic');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider(null);

        $this->assertSame(
            'anthropic',
            $provider->getProviderName(),
            "resolveProvider(null) harus mengembalikan provider global 'anthropic'."
        );
    }

    /**
     * Edge case: Tenant override dengan provider yang sama dengan global.
     *
     * Ketika tenant mengkonfigurasi provider yang sama dengan global,
     * hasilnya tetap harus provider tersebut (tidak ada konflik).
     *
     * **Validates: Requirements 5.5**
     */
    public function test_tenant_override_same_as_global_still_works(): void
    {
        $tenantId = 99;
        $this->clearTenantApiSetting($tenantId);

        $this->setSystemSetting('ai_default_provider', 'gemini');
        $this->setTenantApiSetting($tenantId, 'ai_provider', 'gemini');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider($tenantId);

        $this->assertSame(
            'gemini',
            $provider->getProviderName(),
            "resolveProvider($tenantId) harus mengembalikan 'gemini' ketika tenant dan global sama-sama 'gemini'."
        );
    }

    /**
     * Edge case: Tenant ID yang berbeda memiliki override yang independen.
     *
     * Override satu tenant tidak boleh mempengaruhi tenant lain.
     *
     * **Validates: Requirements 5.5**
     */
    public function test_different_tenants_have_independent_overrides(): void
    {
        $tenantA = 10;
        $tenantB = 20;

        $this->clearTenantApiSetting($tenantA);
        $this->clearTenantApiSetting($tenantB);

        // Global = gemini
        $this->setSystemSetting('ai_default_provider', 'gemini');

        // Tenant A override ke anthropic
        $this->setTenantApiSetting($tenantA, 'ai_provider', 'anthropic');

        // Tenant B tidak memiliki override

        $router = $this->makeRouter();

        $providerA = $router->resolveProvider($tenantA);
        $providerB = $router->resolveProvider($tenantB);

        $this->assertSame(
            'anthropic',
            $providerA->getProviderName(),
            "Tenant A harus menggunakan provider override 'anthropic'."
        );

        $this->assertSame(
            'gemini',
            $providerB->getProviderName(),
            "Tenant B harus menggunakan provider global 'gemini' karena tidak ada override."
        );
    }
}
