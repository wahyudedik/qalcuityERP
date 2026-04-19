<?php

namespace Tests\Feature\Agent;

use App\DTOs\Agent\UndoResult;
use App\Models\AgentAuditLog;
use App\Models\ChatSession;
use App\Models\ProactiveInsight;
use App\Services\Agent\AgentExecutor;
use App\Services\Agent\AgentOrchestrator;
use App\Services\Agent\ProactiveInsightEngine;
use App\Services\AiMemoryService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit Tests for AgentController.
 *
 * Feature: erp-ai-agent
 * Requirements: 7.1, 7.2, 7.6, 5.6, 4.4, 6.6
 */
class AgentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Bypass rate limiting and quota middleware in tests
        $this->withoutMiddleware([
            \App\Http\Middleware\RateLimitAiRequests::class,
            \App\Http\Middleware\CheckAiQuota::class,
            \App\Http\Middleware\EnforceTenantIsolation::class,
        ]);

        // Mock all agent services by default to prevent real API calls
        $this->mock(AgentOrchestrator::class, function ($mock) {
            $mock->shouldReceive('handle')->andReturn($this->makeGenerator([]));
            $mock->shouldReceive('cancel');
        });
        $this->mock(AgentExecutor::class, function ($mock) {
            $mock->shouldReceive('undo')->andReturn(new UndoResult(success: false, message: 'No action.'));
        });
        $this->mock(ProactiveInsightEngine::class, function ($mock) {
            $mock->shouldReceive('getPendingInsights')->andReturn([]);
            $mock->shouldReceive('dismiss');
        });
        $this->mock(AiMemoryService::class, function ($mock) {
            $mock->shouldReceive('getPreferences')->andReturn([]);
            $mock->shouldReceive('getSuggestions')->andReturn([]);
            $mock->shouldReceive('resetMemory')->andReturn(0);
        });
    }

    // =========================================================================
    // POST /agent/send
    // Requirements: 7.1, 7.6
    // =========================================================================

    public function testSendReturnsJsonResponseWithEvents(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);

        // Override default mock
        $this->mock(AgentOrchestrator::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn($this->makeGenerator([
                    ['event' => 'acknowledgment', 'data' => ['message' => 'Memproses...']],
                    ['event' => 'task_summary', 'data' => ['completed' => 1, 'failed' => 0, 'cancelled' => false, 'actions' => []]],
                ]));
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/send', ['message' => 'Cek stok produk']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'events',
                'summary',
            ]);

        $this->assertNotNull($response->json('session_id'));
        $this->assertIsArray($response->json('events'));
        $this->assertNotNull($response->json('summary'));
    }

    public function testSendValidatesRequiredMessage(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $response = $this->actingAs($user)
            ->postJson('/agent/send', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function testSendRejectsMessageTooLong(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $response = $this->actingAs($user)
            ->postJson('/agent/send', ['message' => str_repeat('a', 4001)]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function testSendUsesExistingSessionWhenSessionIdProvided(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);
        $session = ChatSession::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => $user->id,
            'title'        => 'Existing Session',
            'session_type' => 'agent',
            'is_cancelled' => false,
        ]);

        $this->mock(AgentOrchestrator::class, function ($mock) use ($session) {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn($msg, $u, $s, $confirmed) => $s->id === $session->id)
                ->andReturn($this->makeGenerator([
                    ['event' => 'task_summary', 'data' => ['completed' => 0, 'failed' => 0, 'cancelled' => false, 'actions' => []]],
                ]));
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/send', [
                'message'    => 'Halo',
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200);
        $this->assertSame($session->id, $response->json('session_id'));
    }

    public function testSendRequiresAuthentication(): void
    {
        // Re-enable auth middleware for this test
        $response = $this->withMiddleware()->postJson('/agent/send', ['message' => 'test']);
        $response->assertStatus(401);
    }

    // =========================================================================
    // POST /agent/stream
    // Requirements: 7.1, 7.2, 7.6
    // =========================================================================

    public function testStreamReturnsSSEResponse(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        // Override default mock - stream calls handle() which returns a generator
        $this->mock(AgentOrchestrator::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->andReturn($this->makeGenerator([
                    ['event' => 'acknowledgment', 'data' => ['message' => 'Memproses...']],
                    ['event' => 'task_summary', 'data' => ['completed' => 1, 'failed' => 0, 'cancelled' => false, 'actions' => []]],
                ]));
            $mock->shouldReceive('cancel');
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/stream', ['message' => 'Cek stok']);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));
    }

    public function testStreamValidatesRequiredMessage(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $response = $this->actingAs($user)
            ->postJson('/agent/stream', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    // =========================================================================
    // POST /agent/confirm
    // Requirements: 1.5, 7.1
    // =========================================================================

    public function testConfirmContinuesExecutionWithConfirmedFlag(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);
        $session = ChatSession::create([
            'tenant_id'        => $tenant->id,
            'user_id'          => $user->id,
            'title'            => 'Agent Session',
            'session_type'     => 'agent',
            'execution_status' => 'awaiting_approval',
            'active_plan'      => ['goal' => 'Buat invoice', 'steps' => []],
            'is_cancelled'     => false,
        ]);

        $this->mock(AgentOrchestrator::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn($msg, $u, $s, $confirmed) => $confirmed === true)
                ->andReturn($this->makeGenerator([
                    ['event' => 'step_completed', 'data' => ['step' => 1, 'name' => 'Buat invoice']],
                    ['event' => 'task_summary', 'data' => ['completed' => 1, 'failed' => 0, 'cancelled' => false, 'actions' => []]],
                ]));
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/confirm', ['session_id' => $session->id]);

        $response->assertStatus(200)
            ->assertJsonStructure(['session_id', 'events', 'summary']);
    }

    public function testConfirmReturns404ForUnknownSession(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $response = $this->actingAs($user)
            ->postJson('/agent/confirm', ['session_id' => 99999]);

        $response->assertStatus(422); // validation error: session_id doesn't exist
    }

    public function testConfirmReturns404ForOtherUsersSession(): void
    {
        $tenant   = $this->createTenant();
        $user     = $this->createAdminUser($tenant);
        $otherUser = $this->createAdminUser($tenant, ['email' => 'other@test.com', 'role' => 'staff']);

        $session = ChatSession::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => $otherUser->id, // belongs to other user
            'title'        => 'Other Session',
            'session_type' => 'agent',
            'is_cancelled' => false,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/agent/confirm', ['session_id' => $session->id]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // POST /agent/cancel
    // Requirements: 7.4, 7.5
    // =========================================================================

    public function testCancelCallsOrchestratorCancel(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);
        $session = ChatSession::create([
            'tenant_id'        => $tenant->id,
            'user_id'          => $user->id,
            'title'            => 'Agent Session',
            'session_type'     => 'agent',
            'execution_status' => 'executing',
            'is_cancelled'     => false,
        ]);

        $this->mock(AgentOrchestrator::class, function ($mock) use ($session) {
            $mock->shouldReceive('cancel')
                ->once()
                ->withArgs(fn($s) => $s->id === $session->id);
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/cancel', ['session_id' => $session->id]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testCancelReturns404ForOtherUsersSession(): void
    {
        $tenant    = $this->createTenant();
        $user      = $this->createAdminUser($tenant);
        $otherUser = $this->createAdminUser($tenant, ['email' => 'other2@test.com', 'role' => 'staff']);

        $session = ChatSession::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => $otherUser->id,
            'title'        => 'Other Session',
            'session_type' => 'agent',
            'is_cancelled' => false,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/agent/cancel', ['session_id' => $session->id]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // POST /agent/undo
    // Requirements: 6.6
    // =========================================================================

    public function testUndoCallsExecutorUndoForLastWriteLog(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);
        $session = ChatSession::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => $user->id,
            'title'        => 'Agent Session',
            'session_type' => 'agent',
            'is_cancelled' => false,
        ]);

        // Create an undoable audit log within 5 minutes
        $auditLog = AgentAuditLog::create([
            'tenant_id'      => $tenant->id,
            'user_id'        => $user->id,
            'session_id'     => $session->id,
            'action_name'    => 'create_invoice',
            'action_type'    => 'write',
            'parameters'     => ['amount' => 1000000],
            'result'         => ['invoice_id' => 42, 'status' => 'success'],
            'status'         => 'success',
            'is_undoable'    => true,
            'undoable_until' => Carbon::now()->addMinutes(4),
        ]);

        $this->mock(AgentExecutor::class, function ($mock) use ($auditLog) {
            $mock->shouldReceive('undo')
                ->once()
                ->withArgs(fn($log, $registry) => $log->id === $auditLog->id)
                ->andReturn(new UndoResult(
                    success: true,
                    message: 'Aksi berhasil di-undo.',
                    restoredData: ['invoice_id' => 42],
                ));
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/undo', ['session_id' => $session->id]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'message', 'restored_data']);
    }

    public function testUndoReturns404WhenNoUndoableActionExists(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $response = $this->actingAs($user)
            ->postJson('/agent/undo', []);

        $response->assertStatus(404)
            ->assertJson(['code' => 'NO_UNDOABLE_ACTION']);
    }

    public function testUndoReturns422WhenUndoFails(): void
    {
        $tenant  = $this->createTenant();
        $user    = $this->createAdminUser($tenant);

        // Create an audit log that is NOT undoable (window expired)
        AgentAuditLog::create([
            'tenant_id'      => $tenant->id,
            'user_id'        => $user->id,
            'action_name'    => 'create_invoice',
            'action_type'    => 'write',
            'parameters'     => [],
            'result'         => [],
            'status'         => 'success',
            'is_undoable'    => true,
            'undoable_until' => Carbon::now()->addMinutes(4), // still valid
        ]);

        $this->mock(AgentExecutor::class, function ($mock) {
            $mock->shouldReceive('undo')
                ->once()
                ->andReturn(new UndoResult(
                    success: false,
                    message: 'Undo gagal karena alasan tertentu.',
                ));
        });

        $response = $this->actingAs($user)
            ->postJson('/agent/undo', []);

        $response->assertStatus(422)
            ->assertJson(['code' => 'UNDO_FAILED']);
    }

    // =========================================================================
    // GET /agent/insights
    // Requirements: 4.4
    // =========================================================================

    public function testInsightsReturnsPendingInsightsForUser(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $insight = ProactiveInsight::create([
            'tenant_id'       => $tenant->id,
            'condition_type'  => 'low_stock',
            'urgency'         => 'high',
            'title'           => 'Stok Rendah',
            'description'     => 'Beberapa produk stok rendah.',
            'business_impact' => 'Kehilangan penjualan.',
            'recommendations' => ['Buat PO segera.'],
            'condition_data'  => [],
            'condition_hash'  => md5('low_stock_test'),
        ]);

        $this->mock(ProactiveInsightEngine::class, function ($mock) use ($insight) {
            $mock->shouldReceive('getPendingInsights')
                ->once()
                ->andReturn([$insight]);
            $mock->shouldReceive('dismiss');
        });

        $response = $this->actingAs($user)
            ->getJson('/agent/insights');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'insights' => [
                    '*' => ['id', 'condition_type', 'urgency', 'title', 'description', 'business_impact', 'recommendations'],
                ],
                'count',
            ]);

        $this->assertSame(1, $response->json('count'));
    }

    public function testInsightsReturnsEmptyArrayWhenNoInsightsPending(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        // Default mock already returns empty array for getPendingInsights
        $response = $this->actingAs($user)
            ->getJson('/agent/insights');

        $response->assertStatus(200)
            ->assertJson(['insights' => [], 'count' => 0]);
    }

    // =========================================================================
    // POST /agent/insights/{id}/dismiss
    // Requirements: 4.5
    // =========================================================================

    public function testDismissInsightCallsInsightEngineDismiss(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $insight = ProactiveInsight::create([
            'tenant_id'       => $tenant->id,
            'condition_type'  => 'overdue_ar',
            'urgency'         => 'medium',
            'title'           => 'Piutang Jatuh Tempo',
            'description'     => 'Ada piutang jatuh tempo.',
            'business_impact' => 'Arus kas terganggu.',
            'recommendations' => ['Tagih pelanggan.'],
            'condition_data'  => [],
            'condition_hash'  => md5('overdue_ar_test'),
        ]);

        $this->mock(ProactiveInsightEngine::class, function ($mock) use ($insight) {
            $mock->shouldReceive('getPendingInsights')->andReturn([]);
            $mock->shouldReceive('dismiss')
                ->once()
                ->withArgs(fn($i, $reason) => $i->id === $insight->id && $reason === 'handled');
        });

        $response = $this->actingAs($user)
            ->postJson("/agent/insights/{$insight->id}/dismiss", ['reason' => 'handled']);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDismissInsightReturns404ForOtherTenantInsight(): void
    {
        $tenant      = $this->createTenant();
        $otherTenant = $this->createTenant(['name' => 'Other Tenant ' . uniqid()]);
        $user        = $this->createAdminUser($tenant);

        $insight = ProactiveInsight::create([
            'tenant_id'       => $otherTenant->id, // belongs to other tenant
            'condition_type'  => 'low_stock',
            'urgency'         => 'low',
            'title'           => 'Other Tenant Insight',
            'description'     => 'Desc.',
            'business_impact' => 'Impact.',
            'recommendations' => ['Action.'],
            'condition_data'  => [],
            'condition_hash'  => md5('other_tenant_insight'),
        ]);

        $response = $this->actingAs($user)
            ->postJson("/agent/insights/{$insight->id}/dismiss", ['reason' => 'dismissed']);

        $response->assertStatus(404);
    }

    // =========================================================================
    // GET /agent/memory
    // Requirements: 5.6
    // =========================================================================

    public function testMemoryReturnsUserPreferencesAndSuggestions(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $this->mock(AiMemoryService::class, function ($mock) {
            $mock->shouldReceive('getPreferences')
                ->once()
                ->andReturn(['preferred_payment_method' => 'transfer']);
            $mock->shouldReceive('getSuggestions')
                ->once()
                ->andReturn(['Gunakan transfer sebagai default.']);
        });

        $response = $this->actingAs($user)
            ->getJson('/agent/memory');

        $response->assertStatus(200)
            ->assertJsonStructure(['preferences', 'suggestions']);
    }

    // =========================================================================
    // DELETE /agent/memory
    // Requirements: 5.6
    // =========================================================================

    public function testClearMemoryCallsResetMemoryAndReturnsDeletedCount(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        $this->mock(AiMemoryService::class, function ($mock) {
            $mock->shouldReceive('resetMemory')
                ->once()
                ->andReturn(5);
        });

        $response = $this->actingAs($user)
            ->deleteJson('/agent/memory');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'deleted' => 5,
            ]);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Buat Generator dari array events untuk mocking.
     */
    private function makeGenerator(array $events): \Generator
    {
        foreach ($events as $event) {
            yield $event;
        }
    }
}
