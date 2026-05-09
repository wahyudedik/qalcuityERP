<?php

namespace Tests\Property\AI;

use App\Services\AI\Providers\AnthropicProvider;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for System Prompt Generation.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 2.10**
 *
 * Property 8: System prompt mengandung konteks tenant dan instruksi bahasa.
 *
 * Untuk SEMBARANG kombinasi tenant context string dan language code yang valid,
 * system prompt yang dihasilkan oleh AnthropicProvider harus:
 *   1. Mengandung tenant context string tersebut
 *   2. Mengandung instruksi bahasa yang sesuai dengan language code
 */
class SystemPromptTest extends TestCase
{
    use TestTrait;

    /**
     * Language codes yang valid beserta keyword yang harus muncul di system prompt.
     */
    private const LANGUAGE_KEYWORDS = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
        'ms' => 'Bahasa Melayu',
        'zh' => '中文',
        'ar' => 'العربية',
        'ja' => '日本語',
        'ko' => '한국어',
        'fr' => 'français',
        'de' => 'Deutsch',
        'es' => 'español',
        'pt' => 'português',
        'hi' => 'हिंदी',
        'th' => 'ภาษาไทย',
        'vi' => 'tiếng Việt',
    ];

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat instance AnthropicProvider tanpa memerlukan API key nyata.
     */
    private function makeProvider(): AnthropicProvider
    {
        Config::set('ai.providers.anthropic.api_key', 'fake-key-for-system-prompt-test');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
        ]);
        Config::set('ai.providers.anthropic.max_tokens', 8192);
        Config::set('ai.providers.anthropic.timeout', 60);

        return new AnthropicProvider;
    }

    /**
     * Akses protected method getSystemPrompt() via reflection.
     */
    private function callGetSystemPrompt(AnthropicProvider $provider): string
    {
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('getSystemPrompt');
        $method->setAccessible(true);

        return $method->invoke($provider);
    }

    // ─── Unit Tests (Example-Based) ───────────────────────────────

    /**
     * Test bahwa system prompt mengandung tenant context ketika di-set.
     *
     * **Validates: Requirements 2.10**
     */
    public function test_system_prompt_contains_tenant_context_when_set(): void
    {
        $provider = $this->makeProvider();
        $tenantContext = 'PT Maju Bersama — Distributor elektronik di Jakarta';

        $provider->withTenantContext($tenantContext)->withLanguage('id');
        $systemPrompt = $this->callGetSystemPrompt($provider);

        $this->assertStringContainsString(
            $tenantContext,
            $systemPrompt,
            'System prompt harus mengandung tenant context yang di-set'
        );
    }

    /**
     * Test bahwa system prompt mengandung keyword bahasa untuk setiap language code yang valid.
     *
     * **Validates: Requirements 2.10**
     */
    public function test_system_prompt_contains_language_keyword_for_all_valid_codes(): void
    {
        foreach (self::LANGUAGE_KEYWORDS as $langCode => $keyword) {
            $provider = $this->makeProvider();
            $provider->withLanguage($langCode);
            $systemPrompt = $this->callGetSystemPrompt($provider);

            $this->assertStringContainsString(
                $keyword,
                $systemPrompt,
                "System prompt untuk language '{$langCode}' harus mengandung keyword '{$keyword}'"
            );
        }
    }

    /**
     * Test bahwa system prompt TIDAK mengandung section "KONTEKS BISNIS" ketika
     * tenant context kosong.
     *
     * **Validates: Requirements 2.10**
     */
    public function test_system_prompt_does_not_contain_business_context_section_when_empty(): void
    {
        $provider = $this->makeProvider();
        // Tidak memanggil withTenantContext() — tenantContext tetap null
        $systemPrompt = $this->callGetSystemPrompt($provider);

        $this->assertStringNotContainsString(
            'KONTEKS BISNIS',
            $systemPrompt,
            "System prompt tanpa tenant context tidak boleh mengandung section 'KONTEKS BISNIS'"
        );
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 8a: Untuk SEMBARANG tenant context non-kosong dan SEMBARANG
     * language code yang valid, system prompt SELALU mengandung tenant context string.
     *
     * **Validates: Requirements 2.10**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_system_prompt_always_contains_tenant_context_for_any_nonempty_context(): void
    {
        $this
            ->forAll(
                Generators::suchThat(
                    fn (string $s) => trim($s) !== '',
                    Generators::string()
                ),
                Generators::elements(array_keys(self::LANGUAGE_KEYWORDS))
            )
            ->then(function (string $tenantContext, string $language) {
                $provider = $this->makeProvider();
                $provider->withTenantContext($tenantContext)->withLanguage($language);
                $systemPrompt = $this->callGetSystemPrompt($provider);

                $this->assertStringContainsString(
                    $tenantContext,
                    $systemPrompt,
                    "System prompt harus mengandung tenant context '{$tenantContext}' ".
                        "untuk language '{$language}'. Property 8 dilanggar."
                );
            });
    }

    /**
     * Property 8b: Untuk SEMBARANG language code yang valid, system prompt
     * SELALU mengandung keyword bahasa yang sesuai.
     *
     * **Validates: Requirements 2.10**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_system_prompt_always_contains_language_keyword_for_any_valid_language(): void
    {
        $this
            ->forAll(
                Generators::elements(array_keys(self::LANGUAGE_KEYWORDS))
            )
            ->then(function (string $language) {
                $provider = $this->makeProvider();
                $provider->withLanguage($language);
                $systemPrompt = $this->callGetSystemPrompt($provider);

                $expectedKeyword = self::LANGUAGE_KEYWORDS[$language];

                $this->assertStringContainsString(
                    $expectedKeyword,
                    $systemPrompt,
                    "System prompt untuk language '{$language}' harus mengandung keyword ".
                        "'{$expectedKeyword}'. Property 8 dilanggar."
                );
            });
    }

    /**
     * Property 8c: Untuk SEMBARANG tenant context non-kosong dan SEMBARANG
     * language code yang valid, system prompt mengandung KEDUANYA — tenant
     * context DAN keyword bahasa yang sesuai.
     *
     * **Validates: Requirements 2.10**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_system_prompt_contains_both_tenant_context_and_language_keyword(): void
    {
        $this
            ->forAll(
                Generators::suchThat(
                    fn (string $s) => trim($s) !== '',
                    Generators::string()
                ),
                Generators::elements(array_keys(self::LANGUAGE_KEYWORDS))
            )
            ->then(function (string $tenantContext, string $language) {
                $provider = $this->makeProvider();
                $provider->withTenantContext($tenantContext)->withLanguage($language);
                $systemPrompt = $this->callGetSystemPrompt($provider);

                $expectedKeyword = self::LANGUAGE_KEYWORDS[$language];

                // System prompt harus mengandung tenant context
                $this->assertStringContainsString(
                    $tenantContext,
                    $systemPrompt,
                    'System prompt harus mengandung tenant context. Property 8 dilanggar.'
                );

                // System prompt harus mengandung keyword bahasa
                $this->assertStringContainsString(
                    $expectedKeyword,
                    $systemPrompt,
                    "System prompt harus mengandung keyword bahasa '{$expectedKeyword}' ".
                        "untuk language '{$language}'. Property 8 dilanggar."
                );
            });
    }

    /**
     * Edge case: Tenant context kosong → system prompt TIDAK mengandung section "KONTEKS BISNIS".
     *
     * Ketika tenant context tidak di-set (null), system prompt tidak boleh
     * menampilkan section "KONTEKS BISNIS PENGGUNA".
     *
     * **Validates: Requirements 2.10**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_empty_tenant_context_does_not_include_business_context_section(): void
    {
        $this
            ->forAll(
                Generators::elements(array_keys(self::LANGUAGE_KEYWORDS))
            )
            ->then(function (string $language) {
                $provider = $this->makeProvider();
                // Hanya set language, tidak set tenant context
                $provider->withLanguage($language);
                $systemPrompt = $this->callGetSystemPrompt($provider);

                $this->assertStringNotContainsString(
                    'KONTEKS BISNIS',
                    $systemPrompt,
                    'System prompt tanpa tenant context tidak boleh mengandung section '.
                        "'KONTEKS BISNIS' untuk language '{$language}'. Property 8 dilanggar."
                );
            });
    }
}
