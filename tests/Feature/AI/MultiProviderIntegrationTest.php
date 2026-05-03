<?php

namespace Tests\Feature\AI;

use App\Exceptions\AllProvidersUnavailableException;
use App\Exceptions\RateLimitException;
use App\Models\AiProviderSwitchLog;
use App\Services\AI\AiProviderRouter;
use App\Services\AI\ProviderSwitcher;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MultiProviderIntegrationTest extends TestCase
{
    private GeminiProvider $geminiMock;
    private AnthropicProvider $anthropicMock;
    private ProviderSwitcher $switcher;
    private AiProviderRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.fallback_order', ['gemini', 'anthropic']);
        Config::set('ai.mode', 'failover');
        Config::set('ai.providers.gemini.api_key', 'fake-key');
        Config::set('ai.providers.gemini.rate_limit_cooldown', 60);
        Config::set('ai.providers.anthropic.api_key', 'fake-key');
        Config::set('ai.providers.anthropic.rate_limit_cooldown', 60);

        $cache = new Repository(new ArrayStore());
        $this->switcher = new ProviderSwitcher($cache);

        $this->geminiMock = Mockery::mock(GeminiProvider::class);
        $this->anthropicMock = Mockery::mock(AnthropicProvider::class);

        $this->geminiMock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $this->geminiMock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $this->geminiMock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $this->geminiMock->shouldReceive('getProviderName')->andReturn('gemini')->byDefault();

        $this->anthropicMock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $this->anthropicMock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();
        $this->anthropicMock->shouldReceive('isAvailable')->andReturn(true)->byDefault();
        $this->anthropicMock->shouldReceive('getProviderName')->andReturn('anthropic')->byDefault();

        $this->router = new AiProviderRouter(
            $this->geminiMock,
            $this->anthropicMock,
            $this->switcher,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function fallback_from_gemini_to_anthropic_on_rate_limit(): void
    {
        $this->geminiMock
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Gemini rate limit tercapai.', 429));

        $this->anthropicMock
            ->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'Respons dari Anthropic', 'model' => 'claude-3-5-sonnet-20241022']);

        $result = $this->router->generate('Hitung total penjualan bulan ini');

        $this->assertEquals('Respons dari Anthropic', $result['text']);
        $this->assertEquals('claude-3-5-sonnet-20241022', $result['model']);
    }

    #[Test]
    public function switch_log_created_when_fallback_occurs(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);
        $this->actingAs($user);

        $this->router->withTenantId($tenant->id);

        $this->geminiMock
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Gemini rate limit.', 429));

        $this->anthropicMock
            ->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'Respons Anthropic', 'model' => 'claude-3-5-sonnet-20241022']);

        $this->router->generate('Test prompt');

        $this->assertDatabaseHas('ai_provider_switch_logs', [
            'tenant_id'     => $tenant->id,
            'from_provider' => 'gemini',
            'to_provider'   => 'anthropic',
            'reason'        => 'rate_limit',
        ]);
    }

    #[Test]
    public function all_providers_unavailable_throws_exception_with_correct_message(): void
    {
        $this->geminiMock
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Gemini rate limit.', 429));

        $this->anthropicMock
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new RateLimitException('Anthropic rate limit.', 429));

        $this->expectException(AllProvidersUnavailableException::class);
        $this->expectExceptionMessage('Layanan AI sedang tidak tersedia. Silakan coba beberapa saat lagi.');
        $this->expectExceptionCode(503);

        $this->router->generate('Test prompt');
    }

    #[Test]
    public function switch_log_contains_correct_from_to_and_reason(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);
        $this->actingAs($user);

        $this->router->withTenantId($tenant->id);

        $this->geminiMock
            ->shouldReceive('chat')
            ->once()
            ->andThrow(new RateLimitException('Rate limit Gemini.', 429));

        $this->anthropicMock
            ->shouldReceive('chat')
            ->once()
            ->andReturn(['text' => 'Jawaban dari Anthropic', 'model' => 'claude-3-5-sonnet-20241022']);

        $this->router->chat('Apa kabar?', []);

        $log = AiProviderSwitchLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->latest('created_at')
            ->first();

        $this->assertNotNull($log, 'Log switch harus ada di database.');
        $this->assertEquals('gemini', $log->from_provider);
        $this->assertEquals('anthropic', $log->to_provider);
        $this->assertEquals('rate_limit', $log->reason);
        $this->assertEquals($tenant->id, $log->tenant_id);
    }
}