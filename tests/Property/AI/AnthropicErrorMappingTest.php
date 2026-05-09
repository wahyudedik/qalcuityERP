<?php

namespace Tests\Property\AI;

use App\Exceptions\RateLimitException;
use App\Services\AI\Providers\AnthropicProvider;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for Anthropic Error Mapping.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 2.4, 2.6**
 *
 * Property 7: Error rate limit Anthropic selalu menghasilkan exception dengan kode yang benar.
 *
 * Untuk SEMBARANG HTTP error code dalam set {429, 529}:
 *   → AnthropicProvider melempar RateLimitException
 *
 * Untuk SEMBARANG HTTP error code dalam set {500, 503}:
 *   → AnthropicProvider melempar RuntimeException
 */
class AnthropicErrorMappingTest extends TestCase
{
    use TestTrait;

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat instance AnthropicProvider tanpa memerlukan API key nyata.
     */
    private function makeProvider(): AnthropicProvider
    {
        Config::set('ai.providers.anthropic.api_key', 'fake-key-for-error-mapping-test');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
        ]);
        Config::set('ai.providers.anthropic.max_tokens', 8192);
        Config::set('ai.providers.anthropic.timeout', 60);

        return new AnthropicProvider;
    }

    /**
     * Inject mock Guzzle client ke AnthropicProvider via reflection.
     */
    private function injectMockClient(AnthropicProvider $provider, Client $mockClient): void
    {
        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('client');
        $prop->setAccessible(true);
        $prop->setValue($provider, $mockClient);
    }

    /**
     * Buat Guzzle Client yang selalu melempar ClientException dengan status code tertentu.
     *
     * Digunakan untuk mensimulasikan error 4xx (429, 529 sebagai client error).
     */
    private function makeClientThatThrowsClientException(int $statusCode): Client
    {
        $mockRequest = new Request('POST', 'https://api.anthropic.com/v1/messages');
        $mockResponse = new Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => ['type' => 'rate_limit_error', 'message' => 'Rate limit exceeded']])
        );
        $exception = new ClientException(
            "Client error: `POST https://api.anthropic.com/v1/messages` resulted in a `{$statusCode}` response",
            $mockRequest,
            $mockResponse
        );

        $handler = function ($request, $options) use ($exception) {
            return new RejectedPromise($exception);
        };

        $handlerStack = HandlerStack::create($handler);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * Buat Guzzle Client yang selalu melempar ServerException dengan status code tertentu.
     *
     * Digunakan untuk mensimulasikan error 5xx (500, 503).
     */
    private function makeClientThatThrowsServerException(int $statusCode): Client
    {
        $mockRequest = new Request('POST', 'https://api.anthropic.com/v1/messages');
        $mockResponse = new Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => ['type' => 'server_error', 'message' => 'Internal server error']])
        );
        $exception = new ServerException(
            "Server error: `POST https://api.anthropic.com/v1/messages` resulted in a `{$statusCode}` response",
            $mockRequest,
            $mockResponse
        );

        $handler = function ($request, $options) use ($exception) {
            return new RejectedPromise($exception);
        };

        $handlerStack = HandlerStack::create($handler);

        return new Client(['handler' => $handlerStack]);
    }

    // ─── Unit Tests (Example-Based) ───────────────────────────────

    /**
     * Test bahwa HTTP 429 (ClientException) melempar RateLimitException.
     *
     * **Validates: Requirements 2.4**
     */
    public function test_rate_limit_codes_throw_rate_limit_exception(): void
    {
        foreach ([429, 529] as $statusCode) {
            $provider = $this->makeProvider();
            $this->injectMockClient($provider, $this->makeClientThatThrowsClientException($statusCode));

            $thrown = null;
            try {
                $provider->generate('test prompt');
            } catch (RateLimitException $e) {
                $thrown = $e;
            } catch (\Throwable $e) {
                $this->fail(
                    "HTTP {$statusCode} (ClientException) harus melempar RateLimitException, ".
                        'bukan '.get_class($e).': '.$e->getMessage()
                );
            }

            $this->assertInstanceOf(
                RateLimitException::class,
                $thrown,
                "HTTP {$statusCode} (ClientException) harus melempar RateLimitException"
            );
        }
    }

    /**
     * Test bahwa HTTP 500 dan 503 (ServerException) melempar RuntimeException.
     *
     * **Validates: Requirements 2.6**
     */
    public function test_server_error_codes_throw_runtime_exception(): void
    {
        foreach ([500, 503] as $statusCode) {
            $provider = $this->makeProvider();
            $this->injectMockClient($provider, $this->makeClientThatThrowsServerException($statusCode));

            $thrown = null;
            try {
                $provider->generate('test prompt');
            } catch (RateLimitException $e) {
                $this->fail(
                    "HTTP {$statusCode} (ServerException) tidak boleh melempar RateLimitException. ".
                        'Harus melempar RuntimeException.'
                );
            } catch (\RuntimeException $e) {
                $thrown = $e;
            } catch (\Throwable $e) {
                $this->fail(
                    "HTTP {$statusCode} (ServerException) harus melempar RuntimeException, ".
                        'bukan '.get_class($e).': '.$e->getMessage()
                );
            }

            $this->assertInstanceOf(
                \RuntimeException::class,
                $thrown,
                "HTTP {$statusCode} (ServerException) harus melempar RuntimeException"
            );
        }
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 7a: Untuk SEMBARANG kode dalam {429, 529} yang dikirim sebagai
     * ClientException, AnthropicProvider SELALU melempar RateLimitException.
     *
     * **Validates: Requirements 2.4**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_any_rate_limit_client_error_code_throws_rate_limit_exception(): void
    {
        $this
            ->forAll(
                Generators::elements([429, 529])
            )
            ->then(function (int $statusCode) {
                $provider = $this->makeProvider();
                $this->injectMockClient($provider, $this->makeClientThatThrowsClientException($statusCode));

                $thrown = null;
                try {
                    $provider->generate('test prompt untuk property test');
                } catch (RateLimitException $e) {
                    $thrown = $e;
                } catch (\Throwable $e) {
                    $this->fail(
                        "HTTP {$statusCode} (ClientException) harus melempar RateLimitException, ".
                            'bukan '.get_class($e).': '.$e->getMessage()
                    );
                }

                $this->assertInstanceOf(
                    RateLimitException::class,
                    $thrown,
                    "HTTP {$statusCode} (ClientException) harus selalu melempar RateLimitException. ".
                        'Property 7 dilanggar.'
                );
            });
    }

    /**
     * Property 7b: Untuk SEMBARANG kode dalam {500, 503} yang dikirim sebagai
     * ServerException, AnthropicProvider SELALU melempar RuntimeException
     * (bukan RateLimitException).
     *
     * **Validates: Requirements 2.6**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_any_server_error_code_throws_runtime_exception(): void
    {
        $this
            ->forAll(
                Generators::elements([500, 503])
            )
            ->then(function (int $statusCode) {
                $provider = $this->makeProvider();
                $this->injectMockClient($provider, $this->makeClientThatThrowsServerException($statusCode));

                $thrown = null;
                try {
                    $provider->generate('test prompt untuk property test');
                } catch (RateLimitException $e) {
                    $this->fail(
                        "HTTP {$statusCode} (ServerException) tidak boleh melempar RateLimitException. ".
                            'Harus melempar RuntimeException. Property 7 dilanggar.'
                    );
                } catch (\RuntimeException $e) {
                    $thrown = $e;
                } catch (\Throwable $e) {
                    $this->fail(
                        "HTTP {$statusCode} (ServerException) harus melempar RuntimeException, ".
                            'bukan '.get_class($e).': '.$e->getMessage()
                    );
                }

                $this->assertInstanceOf(
                    \RuntimeException::class,
                    $thrown,
                    "HTTP {$statusCode} (ServerException) harus selalu melempar RuntimeException. ".
                        'Property 7 dilanggar.'
                );
            });
    }

    /**
     * Property 7c: HTTP 529 sebagai ServerException juga melempar RateLimitException.
     *
     * HTTP 529 adalah kode non-standar Anthropic untuk "overloaded".
     * AnthropicProvider menanganinya di handleServerException() juga —
     * jika Guzzle mengklasifikasikannya sebagai ServerException, tetap
     * harus melempar RateLimitException.
     *
     * **Validates: Requirements 2.4, 2.6**
     */
    public function test_http_529_as_server_exception_throws_rate_limit_exception(): void
    {
        $provider = $this->makeProvider();
        $this->injectMockClient($provider, $this->makeClientThatThrowsServerException(529));

        $thrown = null;
        try {
            $provider->generate('test prompt untuk 529 server exception');
        } catch (RateLimitException $e) {
            $thrown = $e;
        } catch (\Throwable $e) {
            $this->fail(
                'HTTP 529 (ServerException) harus melempar RateLimitException, '.
                    'bukan '.get_class($e).': '.$e->getMessage()
            );
        }

        $this->assertInstanceOf(
            RateLimitException::class,
            $thrown,
            "HTTP 529 (ServerException) harus melempar RateLimitException karena Anthropic 'overloaded'"
        );
    }

    /**
     * Property 7d: Exception yang dilempar untuk rate limit codes adalah
     * instance dari RateLimitException (bukan RuntimeException biasa).
     *
     * Memastikan bahwa untuk SEMBARANG kode dalam {429, 529}, exception yang
     * dilempar adalah RateLimitException — bukan subclass lain atau RuntimeException.
     * Ini memungkinkan caller membedakan rate limit dari error server lainnya.
     *
     * **Validates: Requirements 2.4**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_rate_limit_exception_is_correct_type(): void
    {
        $this
            ->forAll(
                Generators::elements([429, 529])
            )
            ->then(function (int $statusCode) {
                $provider = $this->makeProvider();
                $this->injectMockClient($provider, $this->makeClientThatThrowsClientException($statusCode));

                $thrown = null;
                try {
                    $provider->generate('test prompt');
                } catch (RateLimitException $e) {
                    $thrown = $e;
                } catch (\Throwable $e) {
                    $this->fail(
                        "HTTP {$statusCode} harus melempar RateLimitException, ".
                            'bukan '.get_class($e)
                    );
                }

                $this->assertNotNull(
                    $thrown,
                    "RateLimitException harus dilempar untuk HTTP {$statusCode}"
                );

                // Exception harus merupakan instance RateLimitException
                $this->assertInstanceOf(
                    RateLimitException::class,
                    $thrown,
                    "Exception untuk HTTP {$statusCode} harus berupa RateLimitException. ".
                        'Property 7 dilanggar.'
                );
            });
    }
}
