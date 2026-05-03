<?php

namespace Tests\Property\AI;

use App\Services\AI\AiProviderRouter;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\ProviderSwitcher;
use App\Services\GeminiService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

/**
 * Property-Based Tests untuk Delegasi GeminiService ke AiProviderRouter.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
 *
 * Property 6: GeminiService mendelegasikan ke AiProviderRouter.
 *
 * Untuk SEMBARANG argumen method (prompt, history, imageData, mimeType):
 *   - GeminiService::generate(prompt) mengembalikan hasil yang IDENTIK dengan
 *     AiProviderRouter::generate(prompt).
 *   - GeminiService::chat(prompt, history) mengembalikan hasil yang IDENTIK dengan
 *     AiProviderRouter::chat(prompt, history).
 *   - GeminiService::generateWithImage(prompt, imageData, mimeType) mengembalikan
 *     hasil yang IDENTIK dengan AiProviderRouter::generateWithImage(prompt, imageData, mimeType).
 */
class GeminiServiceDelegationTest extends TestCase
{
    use TestTrait;

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat instance ProviderSwitcher dengan ArrayStore (in-memory cache)
     * yang terisolasi — tidak ada state yang bocor antar iterasi.
     */
    private function makeSwitcher(): ProviderSwitcher
    {
        $cache = new Repository(new ArrayStore());

        return new ProviderSwitcher($cache);
    }

    /**
     * Buat mock GeminiProvider menggunakan Mockery.
     * Mock ini merekam panggilan dan mengembalikan respons tetap.
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
     * Buat pasangan (AiProviderRouter, GeminiService) yang berbagi mock router yang sama.
     *
     * Pendekatan: buat mock AiProviderRouter yang merekam panggilan dan mengembalikan
     * respons tetap. GeminiService dibuat dengan mock router yang sama, sehingga
     * kita bisa memverifikasi bahwa GeminiService mendelegasikan ke router dengan
     * argumen yang sama dan mengembalikan hasil yang identik.
     *
     * @return array{router: AiProviderRouter&\Mockery\MockInterface, service: GeminiService}
     */
    private function makeRouterAndService(array $fixedResponse): array
    {
        $routerMock = Mockery::mock(AiProviderRouter::class);
        $routerMock->shouldReceive('withTenantContext')->andReturnSelf()->byDefault();
        $routerMock->shouldReceive('withLanguage')->andReturnSelf()->byDefault();

        $geminiMock = $this->makeGeminiMock();

        $service = new GeminiService($routerMock, $geminiMock);

        return ['router' => $routerMock, 'service' => $service];
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
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 6a: Untuk SEMBARANG string prompt,
     * GeminiService::generate(prompt) mengembalikan hasil yang IDENTIK dengan
     * AiProviderRouter::generate(prompt).
     *
     * Pendekatan:
     *   - Buat mock AiProviderRouter yang mengembalikan respons tetap untuk generate()
     *   - Buat GeminiService dengan mock router tersebut
     *   - Panggil GeminiService::generate(prompt)
     *   - Assert bahwa router dipanggil dengan argumen yang sama
     *   - Assert bahwa hasil GeminiService identik dengan respons router
     *
     * **Validates: Requirements 6.1, 6.2, 6.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_generate_delegates_to_router_with_same_args_and_returns_same_result(): void
    {
        $this
            ->forAll(
                Generators::string()
            )
            ->then(function (string $prompt) {
                $fixedResponse = ['text' => 'respons dari router untuk: ' . substr($prompt, 0, 20), 'model' => 'gemini-2.5-flash'];

                ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($fixedResponse);

                // Ekspektasi: router harus dipanggil dengan prompt yang sama
                $routerMock
                    ->shouldReceive('generate')
                    ->once()
                    ->with($prompt, [])
                    ->andReturn($fixedResponse);

                $result = $service->generate($prompt);

                $this->assertSame(
                    $fixedResponse,
                    $result,
                    sprintf(
                        "GeminiService::generate('%s') harus mengembalikan hasil yang identik " .
                            "dengan AiProviderRouter::generate('%s'). Property 6 dilanggar.",
                        mb_substr($prompt, 0, 50),
                        mb_substr($prompt, 0, 50)
                    )
                );
            });
    }

    /**
     * Property 6b: Untuk SEMBARANG string prompt dan history percakapan,
     * GeminiService::chat(prompt, history) mengembalikan hasil yang IDENTIK dengan
     * AiProviderRouter::chat(prompt, history).
     *
     * Generator history menghasilkan array of associative arrays dengan:
     *   - 'role': 'user' atau 'model'
     *   - 'text': string acak
     *
     * **Validates: Requirements 6.1, 6.2, 6.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_chat_delegates_to_router_with_same_args_and_returns_same_result(): void
    {
        $this
            ->forAll(
                Generators::string(),
                Generators::seq(
                    Generators::associative([
                        'role' => Generators::elements(['user', 'model']),
                        'text' => Generators::string(),
                    ])
                )
            )
            ->then(function (string $prompt, array $history) {
                $fixedResponse = ['text' => 'respons chat dari router', 'model' => 'gemini-2.5-flash'];

                ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($fixedResponse);

                // Ekspektasi: router harus dipanggil dengan prompt dan history yang sama
                $routerMock
                    ->shouldReceive('chat')
                    ->once()
                    ->with($prompt, $history, [])
                    ->andReturn($fixedResponse);

                $result = $service->chat($prompt, $history);

                $this->assertSame(
                    $fixedResponse,
                    $result,
                    sprintf(
                        "GeminiService::chat('%s', history[%d]) harus mengembalikan hasil yang identik " .
                            "dengan AiProviderRouter::chat('%s', history[%d]). Property 6 dilanggar.",
                        mb_substr($prompt, 0, 50),
                        count($history),
                        mb_substr($prompt, 0, 50),
                        count($history)
                    )
                );
            });
    }

    /**
     * Property 6c: Untuk SEMBARANG string prompt, imageData, dan mimeType,
     * GeminiService::generateWithImage(prompt, imageData, mimeType) mengembalikan
     * hasil yang IDENTIK dengan AiProviderRouter::generateWithImage(prompt, imageData, mimeType).
     *
     * Generator mimeType menggunakan set terbatas dari MIME type gambar yang valid.
     *
     * **Validates: Requirements 6.1, 6.2, 6.4**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_generate_with_image_delegates_to_router_with_same_args_and_returns_same_result(): void
    {
        $this
            ->forAll(
                Generators::string(),
                Generators::string(),
                Generators::elements(['image/jpeg', 'image/png', 'image/webp'])
            )
            ->then(function (string $prompt, string $imageData, string $mimeType) {
                $fixedResponse = ['text' => 'respons vision dari router', 'model' => 'gemini-2.5-flash'];

                ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($fixedResponse);

                // Ekspektasi: router harus dipanggil dengan argumen yang sama
                $routerMock
                    ->shouldReceive('generateWithImage')
                    ->once()
                    ->with($prompt, $imageData, $mimeType)
                    ->andReturn($fixedResponse);

                $result = $service->generateWithImage($prompt, $imageData, $mimeType);

                $this->assertSame(
                    $fixedResponse,
                    $result,
                    sprintf(
                        "GeminiService::generateWithImage('%s', imageData, '%s') harus mengembalikan " .
                            "hasil yang identik dengan AiProviderRouter::generateWithImage(...). " .
                            "Property 6 dilanggar.",
                        mb_substr($prompt, 0, 50),
                        $mimeType
                    )
                );
            });
    }

    // ─── Edge Case Tests ──────────────────────────────────────────

    /**
     * Edge case: generate() dengan prompt kosong tetap didelegasikan ke router.
     *
     * **Validates: Requirements 6.2**
     */
    public function test_generate_with_empty_prompt_still_delegates(): void
    {
        $fixedResponse = ['text' => '', 'model' => 'gemini-2.5-flash'];

        ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($fixedResponse);

        $routerMock
            ->shouldReceive('generate')
            ->once()
            ->with('', [])
            ->andReturn($fixedResponse);

        $result = $service->generate('');

        $this->assertSame(
            $fixedResponse,
            $result,
            "GeminiService::generate('') harus mendelegasikan ke router meskipun prompt kosong."
        );
    }

    /**
     * Edge case: chat() dengan history kosong tetap didelegasikan ke router.
     *
     * **Validates: Requirements 6.2**
     */
    public function test_chat_with_empty_history_still_delegates(): void
    {
        $fixedResponse = ['text' => 'respons', 'model' => 'gemini-2.5-flash'];

        ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($fixedResponse);

        $routerMock
            ->shouldReceive('chat')
            ->once()
            ->with('halo', [], [])
            ->andReturn($fixedResponse);

        $result = $service->chat('halo', []);

        $this->assertSame(
            $fixedResponse,
            $result,
            "GeminiService::chat('halo', []) harus mendelegasikan ke router dengan history kosong."
        );
    }

    /**
     * Edge case: Hasil router dikembalikan apa adanya tanpa modifikasi.
     *
     * Memverifikasi bahwa GeminiService tidak memodifikasi hasil dari router
     * sebelum mengembalikannya ke pemanggil.
     *
     * **Validates: Requirements 6.3**
     */
    public function test_router_result_returned_without_modification(): void
    {
        $routerResponse = [
            'text'  => 'Ini adalah respons dari router yang tidak boleh dimodifikasi.',
            'model' => 'gemini-2.5-flash-lite',
        ];

        ['router' => $routerMock, 'service' => $service] = $this->makeRouterAndService($routerResponse);

        $routerMock
            ->shouldReceive('generate')
            ->once()
            ->andReturn($routerResponse);

        $result = $service->generate('test prompt');

        $this->assertSame(
            $routerResponse['text'],
            $result['text'],
            "Key 'text' dalam hasil GeminiService harus identik dengan hasil router."
        );

        $this->assertSame(
            $routerResponse['model'],
            $result['model'],
            "Key 'model' dalam hasil GeminiService harus identik dengan hasil router."
        );
    }
}
