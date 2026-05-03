<?php

namespace Tests\Unit\AI;

use App\Services\AI\Providers\AnthropicProvider;
use App\Exceptions\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

/**
 * Unit tests untuk AnthropicProvider.
 *
 * Feature: multi-ai-provider
 * Requirements: 2.1, 2.5, 2.6, 2.7, 10.2, 10.3
 */
class AnthropicProviderTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat instance AnthropicProvider dengan config yang sudah di-set.
     */
    private function makeProvider(): AnthropicProvider
    {
        Config::set('ai.providers.anthropic.api_key', 'fake-anthropic-api-key');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
            'claude-3-haiku-20240307',
        ]);
        Config::set('ai.providers.anthropic.max_tokens', 8192);
        Config::set('ai.providers.anthropic.timeout', 60);

        return new AnthropicProvider();
    }

    /**
     * Inject mock Guzzle client ke dalam provider via reflection.
     *
     * @param  AnthropicProvider  $provider
     * @param  Client             $mockClient
     */
    private function injectMockClient(AnthropicProvider $provider, Client $mockClient): void
    {
        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('client');
        $prop->setAccessible(true);
        $prop->setValue($provider, $mockClient);
    }

    /**
     * Buat mock Guzzle client dengan response yang sudah ditentukan.
     * Mengembalikan [$mockClient, &$container] untuk inspeksi request.
     *
     * @param  array  $responses  Array of Response/Exception
     * @param  array  &$container History container (diisi oleh Middleware::history)
     * @return Client
     */
    private function buildMockClient(array $responses, array &$container = []): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        $history = Middleware::history($container);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * Buat response sukses standar dari Anthropic API.
     */
    private function successResponse(string $text = 'Hello', string $model = 'claude-3-5-sonnet-20241022'): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'content' => [['type' => 'text', 'text' => $text]],
            'model'   => $model,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.7 — getProviderName() mengembalikan 'anthropic'
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function get_provider_name_returns_anthropic(): void
    {
        // Requirements: 2.7
        $provider = $this->makeProvider();

        $this->assertSame('anthropic', $provider->getProviderName());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // isAvailable() — berdasarkan keberadaan API key di config
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function is_available_returns_false_when_api_key_is_empty(): void
    {
        // Requirements: 2.8
        Config::set('ai.providers.anthropic.api_key', '');

        $provider = new AnthropicProvider();

        $this->assertFalse($provider->isAvailable());
    }

    #[Test]
    public function is_available_returns_true_when_api_key_is_set(): void
    {
        // Requirements: 2.8
        Config::set('ai.providers.anthropic.api_key', 'sk-ant-some-valid-key');

        $provider = new AnthropicProvider();

        $this->assertTrue($provider->isAvailable());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10.2, 10.3 — generate() mengirim request ke endpoint yang benar
    //              dengan header yang benar
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function generate_sends_request_to_correct_endpoint_with_correct_headers(): void
    {
        // Requirements: 10.2, 10.3
        $container = [];
        $mockClient = $this->buildMockClient([$this->successResponse()], $container);

        $provider = $this->makeProvider();
        $this->injectMockClient($provider, $mockClient);

        $provider->generate('Test prompt');

        $this->assertCount(1, $container, 'Harus ada tepat satu request yang dikirim');

        /** @var Request $request */
        $request = $container[0]['request'];

        // Verifikasi endpoint — Requirements: 10.2
        $this->assertSame(
            'https://api.anthropic.com/v1/messages',
            (string) $request->getUri(),
            'Request harus dikirim ke endpoint Anthropic Messages API'
        );

        // Verifikasi method HTTP
        $this->assertSame('POST', $request->getMethod());

        // Verifikasi header anthropic-version — Requirements: 10.3
        $this->assertSame(
            '2023-06-01',
            $request->getHeaderLine('anthropic-version'),
            'Header anthropic-version harus bernilai 2023-06-01'
        );

        // Verifikasi header x-api-key — Requirements: 10.3
        $this->assertSame(
            'fake-anthropic-api-key',
            $request->getHeaderLine('x-api-key'),
            'Header x-api-key harus berisi API key yang dikonfigurasi'
        );

        // Verifikasi header content-type
        $this->assertStringContainsString(
            'application/json',
            $request->getHeaderLine('content-type'),
            'Header content-type harus application/json'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.7 — generate() menggunakan model yang dikonfigurasi dalam request body
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function generate_uses_configured_model(): void
    {
        // Requirements: 2.7
        $configuredModel = 'claude-3-5-sonnet-20241022';
        Config::set('ai.providers.anthropic.model', $configuredModel);

        $container = [];
        $mockClient = $this->buildMockClient([$this->successResponse(model: $configuredModel)], $container);

        $provider = $this->makeProvider();
        $this->injectMockClient($provider, $mockClient);

        $provider->generate('Test prompt');

        $this->assertCount(1, $container);

        /** @var Request $request */
        $request = $container[0]['request'];
        $body = json_decode((string) $request->getBody(), true);

        $this->assertSame(
            $configuredModel,
            $body['model'],
            'Model dalam request body harus sesuai dengan konfigurasi'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.5 — Error 401 melempar RuntimeException dengan pesan yang tepat
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function error_401_throws_runtime_exception(): void
    {
        // Requirements: 2.5
        $mockResponse = new Response(401, [], json_encode(['error' => ['message' => 'Unauthorized']]));
        $mockRequest  = new Request('POST', 'https://api.anthropic.com/v1/messages');
        $clientException = new ClientException('Unauthorized', $mockRequest, $mockResponse);

        $mockClient = $this->buildMockClient([$clientException]);

        $provider = $this->makeProvider();
        $this->injectMockClient($provider, $mockClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API key Anthropic tidak valid atau tidak memiliki akses');

        $provider->generate('Test prompt');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.5 — Error 403 melempar RuntimeException dengan pesan yang tepat
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function error_403_throws_runtime_exception(): void
    {
        // Requirements: 2.5
        $mockResponse = new Response(403, [], json_encode(['error' => ['message' => 'Forbidden']]));
        $mockRequest  = new Request('POST', 'https://api.anthropic.com/v1/messages');
        $clientException = new ClientException('Forbidden', $mockRequest, $mockResponse);

        $mockClient = $this->buildMockClient([$clientException]);

        $provider = $this->makeProvider();
        $this->injectMockClient($provider, $mockClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API key Anthropic tidak valid atau tidak memiliki akses');

        $provider->generate('Test prompt');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.7 — withTenantContext() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_tenant_context_returns_same_instance(): void
    {
        // Requirements: 1.7, 2.10
        $provider = $this->makeProvider();

        $result = $provider->withTenantContext('Konteks bisnis tenant A');

        $this->assertSame($provider, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.8 — withLanguage() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_language_returns_same_instance(): void
    {
        // Requirements: 1.8, 2.10
        $provider = $this->makeProvider();

        $result = $provider->withLanguage('en');

        $this->assertSame($provider, $result);
    }
}
