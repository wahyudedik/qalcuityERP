<?php

namespace Tests\Unit\Services;

use App\Services\AiResponseCacheService;
use App\Services\RuleBasedResponseHandler;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AiOptimizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use array driver for testing (fast, no dependencies)
        Cache::swap(Cache::driver('array'));
    }

    /** @test */
    public function it_can_generate_cache_key()
    {
        $service = new AiResponseCacheService;

        $key1 = $service->generateCacheKey(1, 1, 'Halo apa kabar?');
        $key2 = $service->generateCacheKey(1, 1, 'halo apa kabar'); // normalized should match

        $this->assertNotEmpty($key1);
        $this->assertStringStartsWith('ai_response:', $key1);
        $this->assertEquals($key1, $key2); // Same after normalization
    }

    /** @test */
    public function it_can_cache_and_retrieve_response()
    {
        $service = new AiResponseCacheService;
        $cacheKey = 'test_cache_key';

        $response = [
            'text' => 'Test response',
            'model' => 'gemini-2.5-flash',
            'function_calls' => [],
        ];

        // Put in cache
        $service->put($cacheKey, $response, 3600);

        // Retrieve from cache
        $cached = $service->get($cacheKey);

        $this->assertNotNull($cached);
        $this->assertEquals($response['text'], $cached['text']);
        $this->assertEquals($response['model'], $cached['model']);
    }

    /** @test */
    public function it_returns_null_for_non_existent_cache()
    {
        $service = new AiResponseCacheService;

        $cached = $service->get('non_existent_key');

        $this->assertNull($cached);
    }

    /** @test */
    public function it_can_use_remember_pattern()
    {
        $service = new AiResponseCacheService;
        $cacheKey = 'test_remember_key';

        $callCount = 0;

        // First call - should execute callback
        $result1 = $service->remember($cacheKey, function () use (&$callCount) {
            $callCount++;

            return ['text' => 'Generated response', 'model' => 'test'];
        });

        $this->assertEquals(1, $callCount);
        $this->assertEquals('Generated response', $result1['text']);

        // Second call - should use cache
        $result2 = $service->remember($cacheKey, function () use (&$callCount) {
            $callCount++;

            return ['text' => 'New response', 'model' => 'test'];
        });

        $this->assertEquals(1, $callCount); // Callback not called again
        $this->assertEquals('Generated response', $result2['text']); // Cached value
    }

    /** @test */
    public function rule_based_handler_can_detect_simple_patterns()
    {
        $handler = new RuleBasedResponseHandler;

        $this->assertTrue($handler->canHandle('halo'));
        $this->assertTrue($handler->canHandle('Terima kasih'));
        $this->assertTrue($handler->canHandle('BYE'));
        $this->assertTrue($handler->canHandle('siapa kamu?'));
        $this->assertTrue($handler->canHandle('bantuan'));

        $this->assertFalse($handler->canHandle('berapa omzet bulan ini?'));
        $this->assertFalse($handler->canHandle('tambah produk kopi'));
    }

    /** @test */
    public function rule_based_handler_returns_appropriate_responses()
    {
        $handler = new RuleBasedResponseHandler;

        // Test greeting
        $response = $handler->handle('halo', 'John');
        $this->assertStringContainsString('John', $response['text']);
        $this->assertEquals('rule-based', $response['model']);
        $this->assertEmpty($response['function_calls']);

        // Test gratitude
        $response = $handler->handle('terima kasih', 'Jane');
        $this->assertStringContainsString('Sama-sama', $response['text']);

        // Test identity
        $response = $handler->handle('siapa kamu');
        $this->assertStringContainsString('Qalcuity AI', $response['text']);
    }

    /** @test */
    public function rule_based_handler_supports_multiple_languages()
    {
        $handler = new RuleBasedResponseHandler;

        // English
        $this->assertTrue($handler->canHandle('hello'));
        $this->assertTrue($handler->canHandle('thank you'));

        // Indonesian
        $this->assertTrue($handler->canHandle('selamat pagi'));
        $this->assertTrue($handler->canHandle('makasih'));
    }

    /** @test */
    public function cache_determines_ttl_based_on_content()
    {
        $service = new AiResponseCacheService;

        // Real-time data should have short TTL
        $realtimeResponse = [
            'text' => 'Stok produk A ada 10 unit hari ini',
            'model' => 'gemini-2.5-flash',
        ];

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('determineTtl');
        $method->setAccessible(true);

        $ttl = $method->invoke($service, $realtimeResponse);
        $this->assertEquals(300, $ttl); // SHORT_TTL

        // Report data should have long TTL
        $reportResponse = [
            'text' => 'Laporan bulanan menunjukkan peningkatan 20%',
            'model' => 'gemini-2.5-flash',
        ];

        $ttl = $method->invoke($service, $reportResponse);
        $this->assertEquals(86400, $ttl); // LONG_TTL

        // Default response
        $defaultResponse = [
            'text' => 'Produk yang tersedia adalah A, B, C',
            'model' => 'gemini-2.5-flash',
        ];

        $ttl = $method->invoke($service, $defaultResponse);
        $this->assertEquals(3600, $ttl); // DEFAULT_TTL
    }

    /** @test */
    public function message_normalization_works_correctly()
    {
        $service = new AiResponseCacheService;

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizeMessage');
        $method->setAccessible(true);

        $normalized = $method->invoke($service, '  Halo   APA   kabar??  ');
        $this->assertEquals('halo apa kabar', $normalized);

        $normalized = $method->invoke($service, 'Berapa OMZET bulan INI???');
        $this->assertEquals('berapa omzet bulan ini', $normalized);
    }

    /** @test */
    public function rule_based_handler_lists_supported_patterns()
    {
        $handler = new RuleBasedResponseHandler;

        $patterns = $handler->getSupportedPatterns();

        $this->assertArrayHasKey('greetings', $patterns);
        $this->assertArrayHasKey('gratitude', $patterns);
        $this->assertArrayHasKey('farewell', $patterns);
        $this->assertIsArray($patterns['greetings']);
        $this->assertContains('halo', $patterns['greetings']);
    }
}
