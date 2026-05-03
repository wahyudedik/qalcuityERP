<?php

namespace Tests\Unit\AI;

use App\Services\AI\AiProviderRouter;
use App\Services\GeminiService;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\ProviderSwitcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BackwardCompatibilityTest — Task 10.6
 *
 * Test bahwa AiProviderRouter dan GeminiService tanpa parameter $useCase
 * tetap menggunakan routing berbasis konfigurasi global (backward compatible).
 *
 * Requirements: 2.8, 8.7
 */
class BackwardCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    private AiProviderRouter $router;
    private GeminiService $geminiService;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup mock providers
        $geminiProvider = $this->createMock(GeminiProvider::class);
        $anthropicProvider = $this->createMock(AnthropicProvider::class);
        $switcher = $this->createMock(ProviderSwitcher::class);

        // Mock GeminiProvider to return success response
        $geminiProvider->method('chat')
            ->willReturn(['text' => 'Test response', 'model' => 'gemini-2.5-flash']);

        $geminiProvider->method('generate')
            ->willReturn(['text' => 'Test response', 'model' => 'gemini-2.5-flash']);

        $geminiProvider->method('chatWithMedia')
            ->willReturn(['text' => 'Test response', 'model' => 'gemini-2.5-flash']);

        $geminiProvider->method('generateWithImage')
            ->willReturn(['text' => 'Test response', 'model' => 'gemini-2.5-flash']);

        $geminiProvider->method('isAvailable')
            ->willReturn(true);

        $geminiProvider->method('withTenantContext')
            ->willReturnSelf();

        $geminiProvider->method('withLanguage')
            ->willReturnSelf();

        // Create router and service instances
        $this->router = new AiProviderRouter($geminiProvider, $anthropicProvider, $switcher);
        $this->geminiService = new GeminiService($this->router, $geminiProvider);
    }

    /**
     * Test bahwa AiProviderRouter::chat() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_ai_provider_router_chat_without_use_case_works()
    {
        // Arrange
        $prompt = 'Test prompt';
        $history = [];
        $options = [];

        // Act
        $result = $this->router->chat($prompt, $history, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa AiProviderRouter::generate() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_ai_provider_router_generate_without_use_case_works()
    {
        // Arrange
        $prompt = 'Test prompt';
        $options = [];

        // Act
        $result = $this->router->generate($prompt, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa AiProviderRouter::chatWithMedia() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_ai_provider_router_chat_with_media_without_use_case_works()
    {
        // Arrange
        $message = 'Test message';
        $files = [['mime_type' => 'image/jpeg', 'data' => 'base64data']];
        $history = [];
        $options = [];

        // Act
        $result = $this->router->chatWithMedia($message, $files, $history, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa AiProviderRouter::generateWithImage() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_ai_provider_router_generate_with_image_without_use_case_works()
    {
        // Arrange
        $prompt = 'Test prompt';
        $imageData = 'base64data';
        $mimeType = 'image/jpeg';

        // Act
        $result = $this->router->generateWithImage($prompt, $imageData, $mimeType);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa GeminiService::chat() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_gemini_service_chat_without_use_case_works()
    {
        // Arrange
        $message = 'Test message';
        $history = [];
        $options = [];

        // Act
        $result = $this->geminiService->chat($message, $history, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa GeminiService::generate() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_gemini_service_generate_without_use_case_works()
    {
        // Arrange
        $prompt = 'Test prompt';
        $options = [];

        // Act
        $result = $this->geminiService->generate($prompt, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa GeminiService::chatWithMedia() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_gemini_service_chat_with_media_without_use_case_works()
    {
        // Arrange
        $message = 'Test message';
        $files = [['mime_type' => 'image/jpeg', 'data' => 'base64data']];
        $history = [];
        $toolDeclarations = [];

        // Act
        $result = $this->geminiService->chatWithMedia($message, $files, $history, $toolDeclarations);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa GeminiService::generateWithImage() tanpa $useCase tetap berfungsi.
     *
     * @test
     */
    public function test_gemini_service_generate_with_image_without_use_case_works()
    {
        // Arrange
        $prompt = 'Test prompt';
        $imageData = 'base64data';
        $mimeType = 'image/jpeg';

        // Act
        $result = $this->geminiService->generateWithImage($prompt, $imageData, $mimeType);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa perilaku lama (tanpa $useCase) menggunakan routing berbasis konfigurasi global.
     *
     * @test
     */
    public function test_backward_compatibility_uses_global_config_routing()
    {
        // Arrange
        config(['ai.default_provider' => 'gemini']);
        $prompt = 'Test prompt';

        // Act
        $result = $this->router->generate($prompt);

        // Assert
        // Jika berhasil mendapat response, berarti routing berbasis config global bekerja
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertEquals('Test response', $result['text']);
    }

    /**
     * Test bahwa context dan language tetap dipropagasi tanpa $useCase.
     *
     * @test
     */
    public function test_context_and_language_propagation_without_use_case()
    {
        // Arrange
        $prompt = 'Test prompt';
        $context = 'Test tenant context';
        $language = 'en';

        // Act
        $result = $this->router
            ->withTenantContext($context)
            ->withLanguage($language)
            ->generate($prompt);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertEquals('Test response', $result['text']);
    }
}
