<?php

namespace Tests\Unit\AI;

use App\Services\AI\AiProviderRouter;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk backward compatibility GeminiService.
 *
 * Memverifikasi bahwa GeminiService (thin wrapper) tetap berfungsi
 * seperti sebelumnya — dapat di-resolve dari container dan mendelegasikan
 * semua method ke AiProviderRouter / GeminiProvider.
 *
 * Feature: multi-ai-provider
 * Requirements: 6.1, 6.2, 6.3, 6.4
 */
class GeminiServiceCompatibilityTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function makeRouterMock(): AiProviderRouter
    {
        $mock = Mockery::mock(AiProviderRouter::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();

        return $mock;
    }

    private function makeGeminiProviderMock(): GeminiProvider
    {
        $mock = Mockery::mock(GeminiProvider::class);
        $mock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $mock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();

        return $mock;
    }

    private function makeService(
        ?AiProviderRouter $router = null,
        ?GeminiProvider $geminiProvider = null,
    ): GeminiService {
        return new GeminiService(
            $router ?? $this->makeRouterMock(),
            $geminiProvider ?? $this->makeGeminiProviderMock(),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Setup / Teardown
    // ─────────────────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ai.providers.gemini.api_key', 'fake-gemini-key');
        Config::set('ai.providers.gemini.model', 'gemini-2.5-flash');
        Config::set('ai.providers.gemini.fallback_models', ['gemini-2.5-flash']);
        Config::set('ai.providers.anthropic.api_key', 'fake-anthropic-key');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.default_provider', 'gemini');
        Config::set('ai.fallback_order', ['gemini', 'anthropic']);
        Config::set('ai.mode', 'failover');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.1, 6.4 — GeminiService dapat di-resolve dari container
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function gemini_service_can_be_resolved_from_container(): void
    {
        // Requirements: 6.1, 6.4
        // Bind mock dependencies agar container tidak mencoba membuat koneksi nyata
        $routerMock = $this->makeRouterMock();
        $geminiProviderMock = $this->makeGeminiProviderMock();

        $this->app->bind(AiProviderRouter::class, fn () => $routerMock);
        $this->app->bind(GeminiProvider::class, fn () => $geminiProviderMock);

        // Resolve GeminiService dari container — harus berhasil tanpa error
        $service = app(GeminiService::class);

        $this->assertInstanceOf(GeminiService::class, $service);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — chat() mendelegasikan ke router
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function chat_method_delegates_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        $routerMock = $this->makeRouterMock();
        $expected = ['text' => 'Halo dari router', 'model' => 'gemini-2.5-flash'];

        $routerMock->shouldReceive('chat')
            ->once()
            ->with('Halo', [], [], null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->chat('Halo', [], []);

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function chat_method_passes_history_and_options_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        $routerMock = $this->makeRouterMock();
        $history = [['role' => 'user', 'text' => 'Pertanyaan sebelumnya']];
        $options = ['temperature' => 0.7];
        $expected = ['text' => 'Respons', 'model' => 'gemini-2.5-flash'];

        $routerMock->shouldReceive('chat')
            ->once()
            ->with('Pesan baru', $history, $options, null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->chat('Pesan baru', $history, $options);

        $this->assertSame($expected, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — generate() mendelegasikan ke router
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function generate_method_delegates_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        $routerMock = $this->makeRouterMock();
        $expected = ['text' => 'Teks yang dihasilkan', 'model' => 'gemini-2.5-flash'];

        $routerMock->shouldReceive('generate')
            ->once()
            ->with('Prompt saya', [], null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->generate('Prompt saya', []);

        $this->assertSame($expected, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — chatWithMedia() mendelegasikan ke router
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function chat_with_media_delegates_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        $routerMock = $this->makeRouterMock();
        $files = [['mime_type' => 'image/jpeg', 'data' => base64_encode('fake-image')]];
        $expected = ['text' => 'Deskripsi gambar', 'model' => 'gemini-2.5-flash'];

        // chatWithMedia tanpa toolDeclarations -> options = []
        $routerMock->shouldReceive('chatWithMedia')
            ->once()
            ->with('Apa ini?', $files, [], [], null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->chatWithMedia('Apa ini?', $files, [], []);

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function chat_with_media_passes_tool_declarations_as_options_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        // toolDeclarations yang tidak kosong harus diteruskan sebagai options['tools']
        $routerMock = $this->makeRouterMock();
        $files = [['mime_type' => 'image/png', 'data' => base64_encode('fake-png')]];
        $toolDeclarations = [['name' => 'get_stock', 'description' => 'Ambil stok']];
        $expected = ['text' => 'Hasil dengan tools', 'model' => 'gemini-2.5-flash'];

        $routerMock->shouldReceive('chatWithMedia')
            ->once()
            ->with('Cek stok', $files, [], ['tools' => $toolDeclarations], null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->chatWithMedia('Cek stok', $files, [], $toolDeclarations);

        $this->assertSame($expected, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — generateWithImage() mendelegasikan ke router
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function generate_with_image_delegates_to_router(): void
    {
        // Requirements: 6.2, 6.3, 8.1
        $routerMock = $this->makeRouterMock();
        $imageData = base64_encode('fake-image-data');
        $expected = ['text' => 'Analisis gambar', 'model' => 'gemini-2.5-flash'];

        $routerMock->shouldReceive('generateWithImage')
            ->once()
            ->with('Analisis gambar ini', $imageData, 'image/jpeg', null)
            ->andReturn($expected);

        $service = $this->makeService($routerMock);
        $result = $service->generateWithImage('Analisis gambar ini', $imageData, 'image/jpeg');

        $this->assertSame($expected, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — withTenantContext() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_tenant_context_returns_same_instance(): void
    {
        // Requirements: 6.2, 6.3
        $routerMock = $this->makeRouterMock();
        $geminiProviderMock = $this->makeGeminiProviderMock();

        $routerMock->shouldReceive('withTenantContext')
            ->once()
            ->with('Konteks bisnis tenant A')
            ->andReturnSelf();

        $geminiProviderMock->shouldReceive('withTenantContext')
            ->once()
            ->with('Konteks bisnis tenant A')
            ->andReturnSelf();

        $service = $this->makeService($routerMock, $geminiProviderMock);
        $result = $service->withTenantContext('Konteks bisnis tenant A');

        $this->assertSame($service, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.2, 6.3 — withLanguage() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_language_returns_same_instance(): void
    {
        // Requirements: 6.2, 6.3
        $routerMock = $this->makeRouterMock();
        $geminiProviderMock = $this->makeGeminiProviderMock();

        $routerMock->shouldReceive('withLanguage')
            ->once()
            ->with('en')
            ->andReturnSelf();

        $geminiProviderMock->shouldReceive('withLanguage')
            ->once()
            ->with('en')
            ->andReturnSelf();

        $service = $this->makeService($routerMock, $geminiProviderMock);
        $result = $service->withLanguage('en');

        $this->assertSame($service, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Gemini-specific methods — didelegasikan ke GeminiProvider
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function chat_with_tools_delegates_to_gemini_provider(): void
    {
        // Requirements: 6.2
        // chatWithTools adalah method Gemini-specific, harus didelegasikan ke GeminiProvider
        $geminiProviderMock = $this->makeGeminiProviderMock();
        $toolDeclarations = [['name' => 'get_invoice', 'description' => 'Ambil invoice']];
        $history = [['role' => 'user', 'text' => 'Sebelumnya']];
        $expected = [
            'text' => 'Memanggil tool',
            'model' => 'gemini-2.5-flash',
            'function_calls' => [['name' => 'get_invoice', 'args' => []]],
        ];

        $geminiProviderMock->shouldReceive('chatWithTools')
            ->once()
            ->with('Tampilkan invoice', $history, $toolDeclarations)
            ->andReturn($expected);

        $service = $this->makeService(null, $geminiProviderMock);
        $result = $service->chatWithTools('Tampilkan invoice', $history, $toolDeclarations);

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function get_active_model_delegates_to_gemini_provider(): void
    {
        // Requirements: 6.2
        // getActiveModel adalah method Gemini-specific, harus didelegasikan ke GeminiProvider
        $geminiProviderMock = $this->makeGeminiProviderMock();

        $geminiProviderMock->shouldReceive('getActiveModel')
            ->once()
            ->andReturn('gemini-2.5-flash');

        $service = $this->makeService(null, $geminiProviderMock);
        $result = $service->getActiveModel();

        $this->assertSame('gemini-2.5-flash', $result);
    }
}
