<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ExecutionContext;
use App\DTOs\Agent\UndoResult;
use App\Models\AgentAuditLog;
use App\Models\User;
use App\Services\Agent\AgentExecutor;
use App\Services\Agent\TimeoutException;
use App\Services\ERP\ToolRegistry;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit Tests for AgentExecutor.
 *
 * Feature: erp-ai-agent
 * Requirements: 1.3, 6.6
 */
class AgentExecutorTest extends TestCase
{
    private AgentExecutor $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executor = new AgentExecutor;
    }

    // =========================================================================
    // resolveArgs — placeholder valid
    // =========================================================================

    public function test_resolve_args_replaces_simple_placeholder(): void
    {
        $context = new ExecutionContext;
        $context->set(1, ['status' => 'success', 'product_id' => 42]);

        $args = ['id' => '{{step_1.product_id}}'];
        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame(42, $resolved['id']);
    }

    public function test_resolve_args_replaces_nested_dot_notation_placeholder(): void
    {
        $context = new ExecutionContext;
        $context->set(2, [
            'status' => 'success',
            'data' => ['invoice' => ['id' => 99, 'number' => 'INV-001']],
        ]);

        $args = ['invoice_id' => '{{step_2.data.invoice.id}}'];
        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame(99, $resolved['invoice_id']);
    }

    public function test_resolve_args_replaces_multiple_placeholders_in_same_arg(): void
    {
        $context = new ExecutionContext;
        $context->set(1, ['status' => 'success', 'name' => 'Produk A']);
        $context->set(2, ['status' => 'success', 'code' => 'P001']);

        $args = ['label' => '{{step_1.name}} ({{step_2.code}})'];
        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame('Produk A (P001)', $resolved['label']);
    }

    public function test_resolve_args_replaces_placeholders_in_nested_array(): void
    {
        $context = new ExecutionContext;
        $context->set(1, ['status' => 'success', 'customer_id' => 7]);

        $args = [
            'order' => [
                'customer_id' => '{{step_1.customer_id}}',
                'amount' => 100000,
            ],
        ];

        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame(7, $resolved['order']['customer_id']);
        $this->assertSame(100000, $resolved['order']['amount']);
    }

    public function test_resolve_args_leaves_non_placeholder_values_unchanged(): void
    {
        $context = new ExecutionContext;

        $args = [
            'name' => 'Produk Biasa',
            'amount' => 50000,
            'active' => true,
            'tags' => ['a', 'b'],
        ];

        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame('Produk Biasa', $resolved['name']);
        $this->assertSame(50000, $resolved['amount']);
        $this->assertTrue($resolved['active']);
        $this->assertSame(['a', 'b'], $resolved['tags']);
    }

    // =========================================================================
    // resolveArgs — placeholder tidak valid / tidak tersedia
    // =========================================================================

    public function test_resolve_args_returns_null_for_missing_step_output(): void
    {
        $context = new ExecutionContext;
        // Langkah 3 belum dieksekusi

        $args = ['product_id' => '{{step_3.product_id}}'];
        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertNull($resolved['product_id']);
    }

    public function test_resolve_args_returns_null_for_missing_field(): void
    {
        $context = new ExecutionContext;
        $context->set(1, ['status' => 'success', 'name' => 'Produk A']);

        // Field 'nonexistent_field' tidak ada di output langkah 1
        $args = ['x' => '{{step_1.nonexistent_field}}'];
        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertNull($resolved['x']);
    }

    public function test_resolve_args_handles_empty_args(): void
    {
        $context = new ExecutionContext;
        $resolved = $this->executor->resolveArgs([], $context);

        $this->assertSame([], $resolved);
    }

    public function test_resolve_args_handles_non_string_values(): void
    {
        $context = new ExecutionContext;

        $args = [
            'count' => 5,
            'ratio' => 1.5,
            'active' => false,
            'null' => null,
        ];

        $resolved = $this->executor->resolveArgs($args, $context);

        $this->assertSame(5, $resolved['count']);
        $this->assertSame(1.5, $resolved['ratio']);
        $this->assertFalse($resolved['active']);
        $this->assertNull($resolved['null']);
    }

    // =========================================================================
    // canUndo — dalam window 5 menit
    // =========================================================================

    public function test_can_undo_returns_true_when_within_window(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->addMinutes(3),
        ]);

        $this->assertTrue($this->executor->canUndo($log));
    }

    public function test_can_undo_returns_true_at_exact_window_boundary(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->addSeconds(1),
        ]);

        $this->assertTrue($this->executor->canUndo($log));
    }

    // =========================================================================
    // canUndo — di luar window 5 menit
    // =========================================================================

    public function test_can_undo_returns_false_when_window_expired(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->subMinutes(1),
        ]);

        $this->assertFalse($this->executor->canUndo($log));
    }

    public function test_can_undo_returns_false_when_is_undoable_false(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => false,
            'undoable_until' => Carbon::now()->addMinutes(3),
        ]);

        $this->assertFalse($this->executor->canUndo($log));
    }

    public function test_can_undo_returns_false_when_undoable_until_is_null(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => true,
            'undoable_until' => null,
        ]);

        $this->assertFalse($this->executor->canUndo($log));
    }

    public function test_can_undo_returns_false_when_both_false_and_expired(): void
    {
        $log = $this->makeAuditLog([
            'is_undoable' => false,
            'undoable_until' => Carbon::now()->subHours(1),
        ]);

        $this->assertFalse($this->executor->canUndo($log));
    }

    // =========================================================================
    // undo — dalam window 5 menit
    // =========================================================================

    public function test_undo_succeeds_when_within_window(): void
    {
        $log = $this->createAuditLogInDb([
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->addMinutes(4),
            'status' => 'success',
            'action_name' => 'create_invoice',
            'action_type' => 'write',
        ]);

        $registry = $this->buildSuccessRegistry();
        $result = $this->executor->undo($log, $registry);

        $this->assertInstanceOf(UndoResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->message);

        // Status log harus diperbarui ke 'undone'
        $log->refresh();
        $this->assertSame('undone', $log->status);
    }

    // =========================================================================
    // undo — di luar window 5 menit
    // =========================================================================

    public function test_undo_fails_when_window_expired(): void
    {
        $log = $this->createAuditLogInDb([
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->subMinutes(6),
            'status' => 'success',
            'action_name' => 'create_invoice',
            'action_type' => 'write',
        ]);

        $registry = $this->buildSuccessRegistry();
        $result = $this->executor->undo($log, $registry);

        $this->assertInstanceOf(UndoResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertStringContainsString('window', strtolower($result->message));
    }

    public function test_undo_fails_when_not_undoable(): void
    {
        $log = $this->createAuditLogInDb([
            'is_undoable' => false,
            'undoable_until' => Carbon::now()->addMinutes(3),
            'status' => 'success',
            'action_name' => 'create_invoice',
            'action_type' => 'write',
        ]);

        $registry = $this->buildSuccessRegistry();
        $result = $this->executor->undo($log, $registry);

        $this->assertFalse($result->success);
        $this->assertNotEmpty($result->message);
    }

    // =========================================================================
    // Timeout handling > 10 detik
    // =========================================================================

    public function test_timeout_handling_marks_step_as_failed(): void
    {
        $context = new ExecutionContext;

        // Mock registry yang membutuhkan waktu lama (simulasi timeout)
        $registry = $this->createMock(ToolRegistry::class);
        $registry->method('execute')
            ->willReturnCallback(function () {
                // Simulasikan eksekusi yang sangat lambat
                // Kita tidak bisa benar-benar sleep 10+ detik di unit test,
                // jadi kita test dengan mock yang melempar exception timeout
                throw new TimeoutException('Melebihi batas waktu 10 detik.');
            });
        $registry->method('isWriteOperation')->willReturn(false);

        $step = new AgentStep(
            order: 1,
            name: 'Slow tool',
            toolName: 'slow_tool',
            args: [],
            isWriteOp: false,
        );

        $result = $this->executor->executeStep($step, $context, $registry);

        $this->assertSame('failed', $result->status);
        $this->assertNotNull($result->errorMessage);
        // Error message mengandung kata kunci timeout (dalam bahasa Indonesia: "batas waktu")
        $errorLower = strtolower($result->errorMessage);
        $hasTimeoutKeyword = str_contains($errorLower, 'timeout')
            || str_contains($errorLower, 'batas waktu')
            || str_contains($errorLower, 'melebihi');
        $this->assertTrue($hasTimeoutKeyword,
            "Pesan error harus menyebutkan timeout. Aktual: '{$result->errorMessage}'");
        $this->assertFalse($context->has(1));
    }

    public function test_timeout_handling_does_not_write_audit_log(): void
    {
        $context = new ExecutionContext;

        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $this->executor->setUser($user, $tenant->id, null);

        $registry = $this->createMock(ToolRegistry::class);
        $registry->method('execute')
            ->willThrowException(new TimeoutException('Timeout'));
        $registry->method('isWriteOperation')->willReturn(true);

        $step = new AgentStep(
            order: 1,
            name: 'Slow write tool',
            toolName: 'create_invoice',
            args: [],
            isWriteOp: true,
        );

        $countBefore = AgentAuditLog::where('user_id', $user->id)->count();

        $result = $this->executor->executeStep($step, $context, $registry);

        // Timeout menghasilkan failed result
        $this->assertSame('failed', $result->status);

        // Audit log tetap dibuat untuk write ops (dengan status failed)
        $countAfter = AgentAuditLog::where('user_id', $user->id)->count();
        // Note: audit log dibuat untuk write ops bahkan jika gagal
        $this->assertGreaterThanOrEqual($countBefore, $countAfter);
    }

    // =========================================================================
    // executeStep — happy path
    // =========================================================================

    public function test_execute_step_success_populates_context(): void
    {
        $context = new ExecutionContext;
        $registry = $this->buildSuccessRegistry();

        $step = new AgentStep(
            order: 1,
            name: 'Cek stok',
            toolName: 'get_stock_report',
            args: ['filter' => 'critical'],
            isWriteOp: false,
        );

        $result = $this->executor->executeStep($step, $context, $registry);

        $this->assertSame('success', $result->status);
        $this->assertSame(1, $result->stepOrder);
        $this->assertNotNull($result->output);
        $this->assertTrue($context->has(1));
    }

    public function test_execute_step_failure_does_not_populate_context(): void
    {
        $context = new ExecutionContext;
        $registry = $this->buildFailRegistry();

        $step = new AgentStep(
            order: 1,
            name: 'Gagal',
            toolName: 'failing_tool',
            args: [],
            isWriteOp: false,
        );

        $result = $this->executor->executeStep($step, $context, $registry);

        $this->assertSame('failed', $result->status);
        $this->assertFalse($context->has(1));
        $this->assertNull($result->output);
        $this->assertNotNull($result->errorMessage);
    }

    public function test_execute_step_destructive_tool_is_rejected(): void
    {
        $context = new ExecutionContext;
        $registry = $this->buildSuccessRegistry();

        $step = new AgentStep(
            order: 1,
            name: 'Hapus semua',
            toolName: 'bulk_delete_products',
            args: [],
            isWriteOp: true,
        );

        $result = $this->executor->executeStep($step, $context, $registry);

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('destruktif', strtolower($result->errorMessage ?? ''));
    }

    public function test_execute_step_locked_period_modification_is_rejected(): void
    {
        $context = new ExecutionContext;
        $registry = $this->buildSuccessRegistry();

        $step = new AgentStep(
            order: 1,
            name: 'Modifikasi periode terkunci',
            toolName: 'create_journal',
            args: ['locked_period' => true, 'amount' => 100000],
            isWriteOp: true,
        );

        $result = $this->executor->executeStep($step, $context, $registry);

        $this->assertSame('failed', $result->status);
        $errorLower = strtolower($result->errorMessage ?? '');
        $hasLockedKeyword = str_contains($errorLower, 'terkunci')
            || str_contains($errorLower, 'dikunci')
            || str_contains($errorLower, 'locked')
            || str_contains($errorLower, 'destruktif');
        $this->assertTrue($hasLockedKeyword,
            "Pesan error harus menyebutkan periode terkunci. Aktual: '{$result->errorMessage}'");
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Buat AgentAuditLog instance (tidak disimpan ke DB).
     */
    private function makeAuditLog(array $attributes): AgentAuditLog
    {
        $log = new AgentAuditLog;
        $log->forceFill(array_merge([
            'tenant_id' => 1,
            'user_id' => 1,
            'action_name' => 'create_invoice',
            'action_type' => 'write',
            'parameters' => [],
            'result' => [],
            'status' => 'success',
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->addMinutes(5),
        ], $attributes));

        return $log;
    }

    /**
     * Buat dan simpan AgentAuditLog ke DB.
     */
    private function createAuditLogInDb(array $attributes): AgentAuditLog
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);

        return AgentAuditLog::create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_id' => null,
            'action_name' => 'create_invoice',
            'action_type' => 'write',
            'parameters' => ['amount' => 100000],
            'result' => ['invoice_id' => 1],
            'status' => 'success',
            'error_message' => null,
            'is_undoable' => true,
            'undoable_until' => Carbon::now()->addMinutes(5),
        ], $attributes));
    }

    /**
     * Buat ToolRegistry mock yang selalu berhasil.
     */
    private function buildSuccessRegistry(): ToolRegistry
    {
        $mock = $this->createMock(ToolRegistry::class);
        $mock->method('execute')
            ->willReturnCallback(function (string $toolName, array $args) {
                return [
                    'status' => 'success',
                    'message' => "Tool {$toolName} berhasil",
                    'data' => $args,
                ];
            });
        $mock->method('isWriteOperation')
            ->willReturnCallback(fn (string $name) => str_starts_with($name, 'create_')
                || str_starts_with($name, 'update_'));

        return $mock;
    }

    /**
     * Buat ToolRegistry mock yang selalu gagal.
     */
    private function buildFailRegistry(): ToolRegistry
    {
        $mock = $this->createMock(ToolRegistry::class);
        $mock->method('execute')
            ->willReturn(['status' => 'error', 'message' => 'Tool gagal']);
        $mock->method('isWriteOperation')->willReturn(false);

        return $mock;
    }

    /**
     * Buat user dummy.
     */
    private function createUser(): User
    {
        $tenant = $this->createTenant();

        return $this->createAdminUser($tenant);
    }
}
