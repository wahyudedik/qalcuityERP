<?php

namespace Tests\Unit\AI;

use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

/**
 * Unit tests untuk GeminiProvider.
 *
 * Feature: multi-ai-provider
 * Requirements: 1.5, 1.6, 1.7, 1.8
 */
class GeminiProviderTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper: buat instance GeminiProvider dengan fake API key di config
    // ─────────────────────────────────────────────────────────────────────────

    private function makeProvider(): GeminiProvider
    {
        Config::set('ai.providers.gemini.api_key', 'fake-test-api-key');
        Config::set('ai.providers.gemini.model', 'gemini-2.5-flash');
        Config::set('ai.providers.gemini.fallback_models', ['gemini-2.5-flash']);

        return new GeminiProvider();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.6 — getProviderName() mengembalikan 'gemini'
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function get_provider_name_returns_gemini(): void
    {
        // Requirements: 1.6
        $provider = $this->makeProvider();

        $this->assertSame('gemini', $provider->getProviderName());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.5 — isAvailable() mengembalikan false ketika API key kosong
    //
    // GeminiProvider melempar RuntimeException saat konstruksi jika API key
    // kosong, sehingga kita menguji isAvailable() secara langsung melalui
    // refleksi pada instance tanpa konstruktor, lalu set config ke kosong.
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function is_available_returns_false_when_api_key_is_empty(): void
    {
        // Requirements: 1.5
        Config::set('ai.providers.gemini.api_key', '');
        Config::set('gemini.api_key', '');

        // Bypass constructor karena constructor melempar RuntimeException
        // ketika API key kosong — kita hanya ingin menguji logika isAvailable()
        $reflection = new ReflectionClass(GeminiProvider::class);
        $provider = $reflection->newInstanceWithoutConstructor();

        $this->assertFalse($provider->isAvailable());
    }

    #[Test]
    public function is_available_returns_true_when_api_key_is_set(): void
    {
        // Requirements: 1.5
        Config::set('ai.providers.gemini.api_key', 'some-valid-key');

        $reflection = new ReflectionClass(GeminiProvider::class);
        $provider = $reflection->newInstanceWithoutConstructor();

        $this->assertTrue($provider->isAvailable());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.7 — withTenantContext() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_tenant_context_returns_same_instance(): void
    {
        // Requirements: 1.7
        $provider = $this->makeProvider();

        $result = $provider->withTenantContext('Konteks bisnis tenant A');

        $this->assertSame($provider, $result);
    }

    #[Test]
    public function with_tenant_context_sets_context_value(): void
    {
        // Requirements: 1.7
        $provider = $this->makeProvider();
        $context = 'PT Contoh Bisnis — distributor elektronik';

        $provider->withTenantContext($context);

        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('tenantContext');
        $prop->setAccessible(true);

        $this->assertSame($context, $prop->getValue($provider));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.8 — withLanguage() mengembalikan instance yang sama (fluent)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function with_language_returns_same_instance(): void
    {
        // Requirements: 1.8
        $provider = $this->makeProvider();

        $result = $provider->withLanguage('en');

        $this->assertSame($provider, $result);
    }

    #[Test]
    public function with_language_sets_language_value(): void
    {
        // Requirements: 1.8
        $provider = $this->makeProvider();

        $provider->withLanguage('en');

        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('language');
        $prop->setAccessible(true);

        $this->assertSame('en', $prop->getValue($provider));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fluent chaining — withTenantContext() dan withLanguage() dapat di-chain
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function fluent_methods_can_be_chained(): void
    {
        // Requirements: 1.7, 1.8
        $provider = $this->makeProvider();

        $result = $provider
            ->withTenantContext('Konteks tenant')
            ->withLanguage('id');

        $this->assertSame($provider, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Constructor melempar RuntimeException ketika API key kosong
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function constructor_throws_runtime_exception_when_api_key_is_empty(): void
    {
        Config::set('ai.providers.gemini.api_key', '');
        Config::set('gemini.api_key', '');

        $this->expectException(\RuntimeException::class);

        new GeminiProvider();
    }
}
