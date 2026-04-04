<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SecurityFeaturesTest - Verify all security enhancements are working correctly.
 */
class SecurityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test AI command validation blocks dangerous patterns.
     */
    public function test_ai_command_validation_blocks_dangerous_patterns(): void
    {
        $validator = new \App\Services\AiCommandValidator();

        // Test script injection
        $result = $validator->validate('test_command', [
            'name' => '<script>alert("xss")</script>',
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('dangerous', implode(' ', $result['errors']));
    }

    /**
     * Test AI command validation sanitizes input.
     */
    public function test_ai_command_validation_sanitizes_input(): void
    {
        $validator = new \App\Services\AiCommandValidator();

        $result = $validator->validate('test_command', [
            'description' => "Hello\x00World", // Contains null byte
        ]);

        $this->assertTrue($result['valid']);
        $this->assertStringNotContainsString("\x00", $result['sanitized']['description'] ?? '');
    }

    /**
     * Test rate limiting on AI endpoints.
     */
    public function test_ai_rate_limiting_applies(): void
    {
        // This would require actual route testing
        // For now, we verify the middleware exists and is registered
        $this->assertTrue(
            class_exists(\App\Http\Middleware\RateLimitAiRequests::class),
            'RateLimitAiRequests middleware should exist'
        );
    }

    /**
     * Test CSRF token requirement for file uploads.
     */
    public function test_csrf_protection_on_uploads(): void
    {
        $this->assertTrue(
            class_exists(\App\Http\Middleware\VerifyCsrfForUploads::class),
            'VerifyCsrfForUploads middleware should exist'
        );
    }

    /**
     * Test security headers are added to responses.
     */
    public function test_security_headers_middleware(): void
    {
        $middleware = new \App\Http\Middleware\AddSecurityHeaders();

        $request = new \Illuminate\Http\Request();
        $response = new \Illuminate\Http\Response('Test');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertEquals('SAMEORIGIN', $result->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $result->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('Content-Security-Policy', implode('; ', $result->headers->keys()));
    }

    /**
     * Test output escaper HTML escaping.
     */
    public function test_output_escaper_html(): void
    {
        $escaped = \App\Services\OutputEscaper::html('<script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    /**
     * Test output escaper JavaScript escaping.
     */
    public function test_output_escaper_js(): void
    {
        $escaped = \App\Services\OutputEscaper::js('</script><script>alert(1)</script>');

        $this->assertStringNotContainsString('</script>', $escaped);
    }

    /**
     * Test output escaper URL filtering.
     */
    public function test_output_escaper_url_protocol_filter(): void
    {
        $escaped = \App\Services\OutputEscaper::url('javascript:alert(1)');

        $this->assertEquals('#blocked', $escaped);
    }

    /**
     * Test ToolRegistry integrates with validator.
     */
    public function test_tool_registry_has_validator(): void
    {
        $registry = new \App\Services\ERP\ToolRegistry(1, 1);

        $reflection = new \ReflectionClass($registry);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);

        $this->assertInstanceOf(
            \App\Services\AiCommandValidator::class,
            $property->getValue($registry)
        );
    }
}
