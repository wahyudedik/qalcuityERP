<?php

namespace Tests\Unit\AI;

use App\Exceptions\AllProvidersUnavailableException;
use App\Exceptions\RateLimitException;
use App\Models\AiUsageLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AI\AiProviderRouter;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\ProviderSwitcher;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk AiProviderRouter.
 *
 * Feature: multi-ai-provider
 * Requirements: 3.1, 3.4, 7.1, 7.2
 *
 * Strategi testing:
 * - Provider (Gemini, Anthropic) di-mock menggunakan Mockery
 * - SystemSetting dan TenantApiSetting dikontrol via Cache pre-population
 * - Logging diverifikasi via assertDatabaseHas (menggunakan DatabaseTransactions)
 * - ProviderSwitcher menggunakan in-memory ArrayStore
 */
class AiProviderRouterTest extends TestCase
{
    use DatabaseTransactions;
    //
    // Helpers
    //

    private function makeGeminiMock(): GeminiProvider
    {
        $mock = Mockery::mock(GeminiProvider::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $mock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $mock->shouldReceive('getProviderName')->andReturn('gemini')->byDefault();

        return $mock;
    }

    private function makeAnthropicMock(): AnthropicProvider
    {
        $mock = Mockery::mock(AnthropicProvider::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $mock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $mock->shouldReceive('getProviderName')->andReturn('anthropic')->byDefault();

        return $mock;
    }

    private function makeSwitcher(): ProviderSwitcher
    {
        $cache = new Repository(new ArrayStore);

        return new ProviderSwitcher($cache);
    }

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

    //
    // Setup
    //

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.fallback_order', ['gemini', 'anthropic']);
        Config::set('ai.mode', 'failover');
        Config::set('ai.providers.gemini.api_key', 'fake-gemini-key');
        Config::set('ai.providers.gemini.model', 'gemini-2.5-flash');
        Config::set('ai.providers.gemini.rate_limit_cooldown', 60);
        Config::set('ai.providers.anthropic.api_key', 'fake-anthropic-key');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.rate_limit_cooldown', 60);

        // Bersihkan cache settings agar setiap test mulai dari state bersih
        Cache::forget('system_settings_all');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    //
    // 3.1  Routing ke provider yang benar berdasarkan konfigurasi
    //

    #[Test]
    public function routes_to_gemini_by_default(): void
    {
        // Requirements: 3.1
        Config::set('ai.default_provider', 'gemini');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'gemini response', 'model' => 'gemini-2.5-flash']);
        $anthropicMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('gemini response', $result['text']);
        $this->assertSame('gemini-2.5-flash', $result['model']);
    }

    #[Test]
    public function routes_to_anthropic_when_config_default_is_anthropic(): void
    {
        // Requirements: 3.1
        Config::set('ai.default_provider', 'anthropic');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);
        $geminiMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('anthropic response', $result['text']);
        $this->assertSame('claude-3-5-sonnet-20241022', $result['model']);
    }

    #[Test]
    public function routes_to_anthropic_when_system_setting_set(): void
    {
        // Requirements: 3.1
        // SystemSetting['ai_default_provider'] = 'anthropic' via cache pre-population
        $this->setSystemSetting('ai_default_provider', 'anthropic');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);
        $geminiMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('anthropic response', $result['text']);
    }

    #[Test]
    public function tenant_override_takes_priority_over_system_setting(): void
    {
        // Requirements: 3.1, 5.5
        // SystemSetting = 'gemini', TenantApiSetting = 'anthropic' -> anthropic wins
        $this->setSystemSetting('ai_default_provider', 'gemini');
        $this->setTenantApiSetting(42, 'ai_provider', 'anthropic');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);
        $geminiMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId(42);
        $result = $router->generate('Test prompt');

        $this->assertSame('anthropic response', $result['text']);
    }

    #[Test]
    public function tenant_override_takes_priority_even_when_system_setting_is_anthropic(): void
    {
        // Requirements: 3.1, 5.5
        // SystemSetting = 'anthropic', TenantApiSetting = 'gemini' -> gemini wins
        $this->setSystemSetting('ai_default_provider', 'anthropic');
        $this->setTenantApiSetting(99, 'ai_provider', 'gemini');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'gemini response', 'model' => 'gemini-2.5-flash']);
        $anthropicMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId(99);
        $result = $router->generate('Test prompt');

        $this->assertSame('gemini response', $result['text']);
    }

    #[Test]
    public function uses_config_default_when_no_system_setting_exists(): void
    {
        // Requirements: 3.1, 6.7
        Config::set('ai.default_provider', 'gemini');
        // Cache kosong -> SystemSetting::get() returns null -> fallback to config

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'gemini response', 'model' => 'gemini-2.5-flash']);
        $anthropicMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('gemini response', $result['text']);
    }

    //
    // Fallback  beralih ke provider berikutnya saat rate limit / server error
    //

    #[Test]
    public function falls_back_to_next_provider_on_rate_limit(): void
    {
        // Requirements: 3.2, 3.4
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Rate limit exceeded', 429));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic fallback response', 'model' => 'claude-3-5-sonnet-20241022']);

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('anthropic fallback response', $result['text']);
        $this->assertSame('claude-3-5-sonnet-20241022', $result['model']);
    }

    #[Test]
    public function falls_back_to_next_provider_on_server_error(): void
    {
        // Requirements: 3.2
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new \RuntimeException('Internal server error', 500));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $result = $router->generate('Test prompt');

        $this->assertSame('anthropic response', $result['text']);
    }

    #[Test]
    public function throws_all_providers_unavailable_when_all_fail(): void
    {
        // Requirements: 3.3
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Gemini rate limit', 429));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Anthropic rate limit', 429));

        $router = $this->makeRouter($geminiMock, $anthropicMock);

        $this->expectException(AllProvidersUnavailableException::class);

        $router->generate('Test prompt');
    }

    //
    // 7.1  Log dibuat di ai_usage_logs dengan kolom provider terisi
    //

    #[Test]
    public function logs_usage_to_ai_usage_logs_with_provider_column(): void
    {
        // Requirements: 7.1
        // Setelah generate() berhasil, harus ada record di ai_usage_logs dengan provider terisi
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'gemini response', 'model' => 'gemini-2.5-flash']);

        $router = $this->makeRouter($geminiMock);
        $router->withTenantId($tenant->id);
        $router->generate('Test prompt');

        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'provider' => 'gemini',
        ]);
    }

    #[Test]
    public function logs_usage_with_correct_provider_when_anthropic_is_used(): void
    {
        // Requirements: 7.1
        // Ketika anthropic digunakan, kolom provider harus berisi 'anthropic'
        $this->setSystemSetting('ai_default_provider', 'anthropic');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);
        $geminiMock->shouldNotReceive('generate');

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId($tenant->id);
        $router->generate('Test prompt');

        $this->assertDatabaseHas('ai_usage_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'provider' => 'anthropic',
        ]);
    }

    #[Test]
    public function increments_message_count_on_subsequent_calls(): void
    {
        // Requirements: 7.1
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $geminiMock->shouldReceive('generate')
            ->twice()
            ->andReturn(['text' => 'response', 'model' => 'gemini-2.5-flash']);

        $router = $this->makeRouter($geminiMock);
        $router->withTenantId($tenant->id);

        $router->generate('First prompt');
        $router->generate('Second prompt');

        $log = AiUsageLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame(2, $log->message_count);
    }

    #[Test]
    public function does_not_log_usage_when_tenant_id_is_null(): void
    {
        // Requirements: 7.1
        // Tanpa tenant_id, tidak ada log yang dibuat
        $geminiMock = $this->makeGeminiMock();
        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'response', 'model' => 'gemini-2.5-flash']);

        $router = $this->makeRouter($geminiMock);
        // Tidak memanggil withTenantId() -> tenant_id = null
        $router->generate('Test prompt');

        $this->assertDatabaseCount('ai_usage_logs', 0);
    }

    //
    // 7.2  Log dibuat di ai_provider_switch_logs saat terjadi switch provider
    //

    #[Test]
    public function logs_provider_switch_when_fallback_occurs(): void
    {
        // Requirements: 3.4, 7.2
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Rate limit exceeded', 429));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId($tenant->id);
        $router->generate('Test prompt');

        $this->assertDatabaseHas('ai_provider_switch_logs', [
            'tenant_id' => $tenant->id,
            'from_provider' => 'gemini',
            'to_provider' => 'anthropic',
            'reason' => 'rate_limit',
        ]);
    }

    #[Test]
    public function logs_provider_switch_with_server_error_reason(): void
    {
        // Requirements: 3.4, 7.2
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new \RuntimeException('Internal server error', 500));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'anthropic response', 'model' => 'claude-3-5-sonnet-20241022']);

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId($tenant->id);
        $router->generate('Test prompt');

        $this->assertDatabaseHas('ai_provider_switch_logs', [
            'tenant_id' => $tenant->id,
            'from_provider' => 'gemini',
            'to_provider' => 'anthropic',
            'reason' => 'server_error',
        ]);
    }

    #[Test]
    public function logs_switch_to_none_when_all_providers_fail(): void
    {
        // Requirements: 3.4, 7.2
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.mode', 'failover');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->actingAs($user);

        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Gemini rate limit', 429));

        $anthropicMock->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Anthropic rate limit', 429));

        $router = $this->makeRouter($geminiMock, $anthropicMock);
        $router->withTenantId($tenant->id);

        try {
            $router->generate('Test prompt');
        } catch (AllProvidersUnavailableException) {
            // Exception diharapkan
        }

        $this->assertDatabaseHas('ai_provider_switch_logs', [
            'tenant_id' => $tenant->id,
            'from_provider' => 'anthropic',
            'to_provider' => 'none',
            'reason' => 'rate_limit',
        ]);
    }

    #[Test]
    public function does_not_log_switch_when_no_fallback_occurs(): void
    {
        // Requirements: 7.2
        $geminiMock = $this->makeGeminiMock();
        $geminiMock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'gemini response', 'model' => 'gemini-2.5-flash']);

        $router = $this->makeRouter($geminiMock);
        $router->generate('Test prompt');

        $this->assertDatabaseCount('ai_provider_switch_logs', 0);
    }

    //
    // resolveProvider()  verifikasi logika pemilihan provider
    //

    #[Test]
    public function resolve_provider_returns_gemini_by_default(): void
    {
        // Requirements: 3.1
        Config::set('ai.default_provider', 'gemini');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider();

        $this->assertSame('gemini', $provider->getProviderName());
    }

    #[Test]
    public function resolve_provider_returns_anthropic_when_config_set(): void
    {
        // Requirements: 3.1
        Config::set('ai.default_provider', 'anthropic');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider();

        $this->assertSame('anthropic', $provider->getProviderName());
    }

    #[Test]
    public function resolve_provider_returns_anthropic_when_system_setting_set(): void
    {
        // Requirements: 3.1
        $this->setSystemSetting('ai_default_provider', 'anthropic');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider();

        $this->assertSame('anthropic', $provider->getProviderName());
    }

    #[Test]
    public function resolve_provider_returns_tenant_override_provider(): void
    {
        // Requirements: 3.1, 5.5
        $this->setSystemSetting('ai_default_provider', 'gemini');
        $this->setTenantApiSetting(77, 'ai_provider', 'anthropic');

        $router = $this->makeRouter();
        $provider = $router->resolveProvider(77);

        $this->assertSame('anthropic', $provider->getProviderName());
    }

    //
    // getProviderName() dan isAvailable()
    //

    #[Test]
    public function get_provider_name_returns_router(): void
    {
        // Requirements: 1.6
        $router = $this->makeRouter();

        $this->assertSame('router', $router->getProviderName());
    }

    #[Test]
    public function is_available_returns_true_when_at_least_one_provider_available(): void
    {
        // Requirements: 1.5
        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('isAvailable')->andReturn(true);
        $anthropicMock->shouldReceive('isAvailable')->andReturn(false);

        $router = $this->makeRouter($geminiMock, $anthropicMock);

        $this->assertTrue($router->isAvailable());
    }

    #[Test]
    public function is_available_returns_false_when_no_provider_available(): void
    {
        // Requirements: 1.5
        $geminiMock = $this->makeGeminiMock();
        $anthropicMock = $this->makeAnthropicMock();

        $geminiMock->shouldReceive('isAvailable')->andReturn(false);
        $anthropicMock->shouldReceive('isAvailable')->andReturn(false);

        $router = $this->makeRouter($geminiMock, $anthropicMock);

        $this->assertFalse($router->isAvailable());
    }

    //
    // Fluent interface  withTenantContext() dan withLanguage()
    //

    #[Test]
    public function with_tenant_context_returns_same_instance(): void
    {
        // Requirements: 1.7
        $router = $this->makeRouter();

        $result = $router->withTenantContext('Konteks bisnis tenant A');

        $this->assertSame($router, $result);
    }

    #[Test]
    public function with_language_returns_same_instance(): void
    {
        // Requirements: 1.8
        $router = $this->makeRouter();

        $result = $router->withLanguage('en');

        $this->assertSame($router, $result);
    }

    #[Test]
    public function with_tenant_id_returns_same_instance(): void
    {
        $router = $this->makeRouter();

        $result = $router->withTenantId(1);

        $this->assertSame($router, $result);
    }
}
