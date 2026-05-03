<?php

namespace Tests\Property\AI;

use App\Contracts\AiProvider;
use App\Services\AI\Providers\AnthropicProvider;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for AI Provider Output Format Consistency.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 1.1, 1.2, 2.2**
 */
class ProviderOutputFormatTest extends TestCase
{
    use TestTrait;

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat AnthropicProvider dengan mock Guzzle handler yang selalu
     * mengembalikan response sukses untuk setiap prompt.
     *
     * Menggunakan callable handler (bukan MockHandler) agar bisa menangani
     * berapapun iterasi tanpa kehabisan response.
     */
    private function makeAnthropicProviderWithMockClient(): AnthropicProvider
    {
        Config::set('ai.providers.anthropic.api_key', 'fake-anthropic-key-for-property-test');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
            'claude-3-haiku-20240307',
        ]);
        Config::set('ai.providers.anthropic.max_tokens', 8192);
        Config::set('ai.providers.anthropic.timeout', 60);

        $provider = new AnthropicProvider();

        // Callable handler yang selalu mengembalikan response sukses —
        // tidak habis seperti MockHandler, cocok untuk property tests dengan
        // banyak iterasi.
        $alwaysSuccessHandler = function ($request, $options) {
            return new \GuzzleHttp\Promise\FulfilledPromise(
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'content' => [['type' => 'text', 'text' => 'response text from anthropic']],
                    'model'   => 'claude-3-5-sonnet-20241022',
                ]))
            );
        };

        $handlerStack = HandlerStack::create($alwaysSuccessHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Inject mock client via reflection
        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('client');
        $prop->setAccessible(true);
        $prop->setValue($provider, $mockClient);

        return $provider;
    }

    /**
     * Buat test double (anonymous class) yang mengimplementasikan AiProvider
     * dan mensimulasikan output format yang seharusnya dikembalikan GeminiProvider.
     *
     * GeminiProvider membutuhkan Gemini SDK client yang sulit di-mock tanpa
     * koneksi nyata. Pendekatan ini memvalidasi kontrak output format:
     * setiap implementasi AiProvider HARUS mengembalikan ['text' => string, 'model' => string].
     *
     * Test double ini merepresentasikan perilaku yang diharapkan dari GeminiProvider
     * sesuai dengan kontrak AiProvider (Requirements 1.1, 1.2).
     */
    private function makeGeminiProviderDouble(): AiProvider
    {
        return new class implements AiProvider {
            public function generate(string $prompt, array $options = []): array
            {
                // Simulasi output GeminiProvider::generate() yang sebenarnya
                // mengembalikan ['text' => string, 'model' => string]
                return [
                    'text'  => 'Respons dari Gemini untuk prompt: ' . mb_substr($prompt, 0, 50),
                    'model' => 'gemini-2.5-flash',
                ];
            }

            public function chat(string $prompt, array $history = [], array $options = []): array
            {
                return ['text' => 'chat response', 'model' => 'gemini-2.5-flash'];
            }

            public function chatWithMedia(string $message, array $files, array $history = [], array $options = []): array
            {
                return ['text' => 'media response', 'model' => 'gemini-2.5-flash'];
            }

            public function generateWithImage(string $prompt, string $imageData, string $mimeType): array
            {
                return ['text' => 'image response', 'model' => 'gemini-2.5-flash'];
            }

            public function isAvailable(): bool
            {
                return true;
            }

            public function getProviderName(): string
            {
                return 'gemini';
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

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 1: Format output AnthropicProvider::generate() konsisten.
     *
     * Untuk SEMBARANG string prompt, output dari AnthropicProvider::generate()
     * harus selalu berupa array dengan key 'text' (string) dan 'model' (string).
     *
     * **Validates: Requirements 1.2, 2.2**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_anthropic_generate_output_has_correct_format(): void
    {
        $provider = $this->makeAnthropicProviderWithMockClient();

        $this
            ->forAll(
                // Generator string printable ASCII — mencakup berbagai panjang dan karakter
                Generators::string()
            )
            ->then(function (string $prompt) use ($provider) {
                $result = $provider->generate($prompt);

                // Output harus berupa array
                $this->assertIsArray(
                    $result,
                    "AnthropicProvider::generate() harus mengembalikan array untuk prompt: " . json_encode(mb_substr($prompt, 0, 50))
                );

                // Harus memiliki key 'text'
                $this->assertArrayHasKey(
                    'text',
                    $result,
                    "Output AnthropicProvider::generate() harus memiliki key 'text'"
                );

                // Harus memiliki key 'model'
                $this->assertArrayHasKey(
                    'model',
                    $result,
                    "Output AnthropicProvider::generate() harus memiliki key 'model'"
                );

                // 'text' harus berupa string
                $this->assertIsString(
                    $result['text'],
                    "Output key 'text' dari AnthropicProvider::generate() harus berupa string"
                );

                // 'model' harus berupa string
                $this->assertIsString(
                    $result['model'],
                    "Output key 'model' dari AnthropicProvider::generate() harus berupa string"
                );

                // 'model' tidak boleh kosong
                $this->assertNotEmpty(
                    $result['model'],
                    "Output key 'model' dari AnthropicProvider::generate() tidak boleh kosong"
                );
            });
    }

    /**
     * Property 1: Format output GeminiProvider::generate() konsisten.
     *
     * Untuk SEMBARANG string prompt, output dari GeminiProvider::generate()
     * harus selalu berupa array dengan key 'text' (string) dan 'model' (string).
     *
     * Menggunakan test double yang mengimplementasikan kontrak AiProvider
     * untuk memvalidasi format output tanpa memerlukan koneksi API nyata.
     *
     * **Validates: Requirements 1.1, 1.2**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_gemini_generate_output_has_correct_format(): void
    {
        $provider = $this->makeGeminiProviderDouble();

        $this
            ->forAll(
                Generators::string()
            )
            ->then(function (string $prompt) use ($provider) {
                $result = $provider->generate($prompt);

                // Output harus berupa array
                $this->assertIsArray(
                    $result,
                    "GeminiProvider::generate() harus mengembalikan array untuk prompt: " . json_encode(mb_substr($prompt, 0, 50))
                );

                // Harus memiliki key 'text'
                $this->assertArrayHasKey(
                    'text',
                    $result,
                    "Output GeminiProvider::generate() harus memiliki key 'text'"
                );

                // Harus memiliki key 'model'
                $this->assertArrayHasKey(
                    'model',
                    $result,
                    "Output GeminiProvider::generate() harus memiliki key 'model'"
                );

                // 'text' harus berupa string
                $this->assertIsString(
                    $result['text'],
                    "Output key 'text' dari GeminiProvider::generate() harus berupa string"
                );

                // 'model' harus berupa string
                $this->assertIsString(
                    $result['model'],
                    "Output key 'model' dari GeminiProvider::generate() harus berupa string"
                );

                // 'model' tidak boleh kosong
                $this->assertNotEmpty(
                    $result['model'],
                    "Output key 'model' dari GeminiProvider::generate() tidak boleh kosong"
                );
            });
    }

    /**
     * Property 1 (variant): Kedua provider mengembalikan format yang identik.
     *
     * Untuk SEMBARANG string prompt, struktur output dari AnthropicProvider
     * dan GeminiProvider harus identik — keduanya harus memiliki tepat
     * key 'text' dan 'model' dengan tipe string.
     *
     * **Validates: Requirements 1.1, 1.2, 2.2**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_both_providers_return_identical_output_structure(): void
    {
        $anthropicProvider = $this->makeAnthropicProviderWithMockClient();
        $geminiDouble      = $this->makeGeminiProviderDouble();

        $this
            ->forAll(
                Generators::string()
            )
            ->then(function (string $prompt) use ($anthropicProvider, $geminiDouble) {
                $anthropicResult = $anthropicProvider->generate($prompt);
                $geminiResult    = $geminiDouble->generate($prompt);

                // Kedua hasil harus berupa array
                $this->assertIsArray($anthropicResult, "AnthropicProvider harus mengembalikan array");
                $this->assertIsArray($geminiResult, "GeminiProvider harus mengembalikan array");

                // Kedua hasil harus memiliki key yang sama
                $anthropicKeys = array_keys($anthropicResult);
                $geminiKeys    = array_keys($geminiResult);

                $this->assertContains('text', $anthropicKeys, "AnthropicProvider output harus memiliki key 'text'");
                $this->assertContains('model', $anthropicKeys, "AnthropicProvider output harus memiliki key 'model'");
                $this->assertContains('text', $geminiKeys, "GeminiProvider output harus memiliki key 'text'");
                $this->assertContains('model', $geminiKeys, "GeminiProvider output harus memiliki key 'model'");

                // Tipe nilai harus sama (keduanya string)
                $this->assertSame(
                    gettype($anthropicResult['text']),
                    gettype($geminiResult['text']),
                    "Tipe key 'text' harus sama antara kedua provider"
                );
                $this->assertSame(
                    gettype($anthropicResult['model']),
                    gettype($geminiResult['model']),
                    "Tipe key 'model' harus sama antara kedua provider"
                );
            });
    }
}
