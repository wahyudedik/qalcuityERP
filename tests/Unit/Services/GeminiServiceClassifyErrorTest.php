<?php

namespace Tests\Unit\Services;

use App\Services\GeminiService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

/**
 * Unit tests for GeminiService::classifyError()
 *
 * Feature: gemini-model-auto-switching
 * Requirements: 3.1, 3.2, 2.3
 */
class GeminiServiceClassifyErrorTest extends TestCase
{
    private GeminiService $service;

    private \ReflectionMethod $classifyError;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass constructor (requires real API key) — we only need to test
        // the pure classifyError() logic which has no external dependencies.
        $reflection = new ReflectionClass(GeminiService::class);
        $this->service = $reflection->newInstanceWithoutConstructor();

        $method = $reflection->getMethod('classifyError');
        $method->setAccessible(true);
        $this->classifyError = $method;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────────────

    private function classify(\Throwable $e): ?string
    {
        return $this->classifyError->invoke($this->service, $e);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP 429 → 'rate_limit'
    // Requirements: 3.1
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function it_classifies_http_429_as_rate_limit(): void
    {
        $e = new \RuntimeException('Too Many Requests', 429);

        $this->assertSame('rate_limit', $this->classify($e));
    }

    #[Test]
    public function it_classifies_429_regardless_of_message_content(): void
    {
        // Code takes precedence — must still be rate_limit even if message mentions quota
        $e = new \RuntimeException('quota exceeded but code is 429', 429);

        $this->assertSame('rate_limit', $this->classify($e));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Quota messages → 'quota_exceeded'
    // Requirements: 3.2
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function it_classifies_message_containing_quota_as_quota_exceeded(): void
    {
        $e = new \RuntimeException('You have exceeded your quota for this project', 400);

        $this->assertSame('quota_exceeded', $this->classify($e));
    }

    #[Test]
    public function it_classifies_quota_message_case_insensitive(): void
    {
        $e = new \RuntimeException('QUOTA limit reached', 400);

        $this->assertSame('quota_exceeded', $this->classify($e));
    }

    #[Test]
    public function it_classifies_resource_exhausted_message_as_quota_exceeded(): void
    {
        // RESOURCE_EXHAUSTED with a non-429 code → quota_exceeded via message check
        $e = new \RuntimeException('RESOURCE_EXHAUSTED: daily quota exceeded', 400);

        $this->assertSame('quota_exceeded', $this->classify($e));
    }

    #[Test]
    public function it_classifies_resource_exhausted_lowercase_as_quota_exceeded(): void
    {
        $e = new \RuntimeException('resource_exhausted: limit reached', 400);

        $this->assertSame('quota_exceeded', $this->classify($e));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP 503 → 'service_unavailable'
    // Requirements: 2.3
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function it_classifies_http_503_as_service_unavailable(): void
    {
        $e = new \RuntimeException('Service Unavailable', 503);

        $this->assertSame('service_unavailable', $this->classify($e));
    }

    #[Test]
    public function it_classifies_503_regardless_of_message(): void
    {
        $e = new \RuntimeException('Backend service is down', 503);

        $this->assertSame('service_unavailable', $this->classify($e));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Generic / unclassified errors → null
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function it_returns_null_for_generic_500_error(): void
    {
        $e = new \RuntimeException('Something went wrong', 500);

        $this->assertNull($this->classify($e));
    }

    #[Test]
    public function it_returns_null_for_network_timeout(): void
    {
        $e = new \RuntimeException('Connection timed out', 0);

        $this->assertNull($this->classify($e));
    }

    #[Test]
    public function it_returns_null_for_404_not_found(): void
    {
        $e = new \RuntimeException('Not Found', 404);

        $this->assertNull($this->classify($e));
    }

    #[Test]
    public function it_returns_null_for_401_unauthorized(): void
    {
        $e = new \RuntimeException('Unauthorized', 401);

        $this->assertNull($this->classify($e));
    }

    #[Test]
    public function it_returns_null_for_generic_exception_with_no_code(): void
    {
        $e = new \Exception('An unexpected error occurred');

        $this->assertNull($this->classify($e));
    }
}
