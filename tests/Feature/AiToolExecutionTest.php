<?php

namespace Tests\Feature;

use App\Services\ERP\ToolRegistry;
use Tests\TestCase;

class AiToolExecutionTest extends TestCase
{
    private $tenant;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);
    }

    public function test_tool_registry_executes_read_tool_successfully(): void
    {
        $registry = new ToolRegistry($this->tenant->id, $this->user->id);

        $result = $registry->execute('get_app_guide', [
            'topic' => 'pos',
            'show_examples' => false,
        ]);

        $this->assertEquals('success', $result['status'] ?? null);
        $this->assertNotEmpty($result['message'] ?? null);
        $this->assertStringContainsString('Kasir (POS)', $result['message']);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_tool_registry_returns_error_for_unknown_tool(): void
    {
        $registry = new ToolRegistry($this->tenant->id, $this->user->id);

        $result = $registry->execute('tool_yang_tidak_ada', []);

        $this->assertEquals('error', $result['status'] ?? null);
        $this->assertNotEmpty($result['message'] ?? null);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_tool_registry_does_not_break_with_extra_args(): void
    {
        $registry = new ToolRegistry($this->tenant->id, $this->user->id);

        $result = $registry->execute('get_app_guide', ['force_invalid_method' => true]);

        $this->assertEquals('success', $result['status'] ?? null);

        $this->assertDatabaseMissing('activity_logs', [
            'tenant_id' => $this->tenant->id,
            'is_ai_action' => true,
        ]);
    }
}
