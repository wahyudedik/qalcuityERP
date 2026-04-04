<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Services\Ai\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test AI Tool Execution and Gemini Integration
 * 
 * @group ai
 * @group feature
 */
class AiToolExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /**
     * Test basic AI tool execution
     */
    public function test_execute_ai_tool_successfully(): void
    {
        Http::fake([
            '*/generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'This is a sample AI response']]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Analyze sales trends'],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'output',
                    'execution_time',
                    'tokens_used',
                ],
            ]);
    }

    /**
     * Test AI tool execution with invalid input
     */
    public function test_ai_tool_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => '',
                'input' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tool_id');
    }

    /**
     * Test AI tool rate limiting
     */
    public function test_ai_tool_rate_limiting(): void
    {
        Http::fake();

        // Make multiple requests rapidly
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($this->user)
                ->postJson('/api/ai-tools/execute', [
                    'tool_id' => 'data-analysis',
                    'input' => ['query' => 'Test query'],
                ]);
        }

        // Should still work (rate limits are generous in testing)
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Final query'],
            ]);

        // Verify rate limit headers exist
        $response->assertHeader('X-RateLimit-Limit');
    }

    /**
     * Test AI tool execution history tracking
     */
    public function test_ai_execution_history(): void
    {
        Http::fake();

        $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Test query'],
            ]);

        // Check database for execution record
        $this->assertDatabaseHas('ai_tool_executions', [
            'user_id' => $this->user->id,
            'tool_id' => 'data-analysis',
        ])
            ->assertDatabaseCount('ai_tool_executions', 1);
    }

    /**
     * Test AI response caching
     */
    public function test_ai_responses_are_cached(): void
    {
        $callCount = 0;

        Http::fake(function () use (&$callCount) {
            $callCount++;
            return Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'Cached response']]
                        ]
                    ]
                ]
            ], 200);
        });

        // Execute same query twice
        $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Same query'],
            ]);

        $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Same query'],
            ]);

        // Should only call API once due to caching
        $this->assertEquals(1, $callCount, 'AI API should be cached for identical queries');
    }

    /**
     * Test AI tool error handling
     */
    public function test_ai_tool_api_error_handling(): void
    {
        Http::fake([
            '*/generateContent' => Http::response(null, 500),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Test query'],
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'AI service temporarily unavailable',
            ]);
    }

    /**
     * Test AI tool with different tenant isolation
     */
    public function test_ai_tools_respect_tenant_isolation(): void
    {
        Http::fake();

        $tenant2 = Tenant::factory()->create();
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Both tenants execute AI tools
        $this->actingAs($this->user)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Tenant 1 query'],
            ]);

        $this->actingAs($user2)
            ->postJson('/api/ai-tools/execute', [
                'tool_id' => 'data-analysis',
                'input' => ['query' => 'Tenant 2 query'],
            ]);

        // Verify data is separated by tenant
        $this->assertDatabaseHas('ai_tool_executions', [
            'user_id' => $this->user->id,
        ])
            ->assertDatabaseHas('ai_tool_executions', [
                'user_id' => $user2->id,
            ]);
    }
}
