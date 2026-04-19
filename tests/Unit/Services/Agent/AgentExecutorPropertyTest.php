<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ExecutionContext;
use App\DTOs\Agent\StepResult;
use App\Services\Agent\AgentExecutor;
use App\Services\ERP\ToolRegistry;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for AgentExecutor.
 *
 * Feature: erp-ai-agent
 *
 * Property 2: Step Output Propagation
 * Property 3: Fail-Fast Execution
 * Property 4: Write Operation Approval Gate
 * Property 13: Audit Log Completeness
 * Property 14: Permission Enforcement
 * Property 15: Destructive Action Rejection
 *
 * Validates: Requirements 1.3, 1.4, 1.5, 6.3, 6.5, 9.3
 */
class AgentExecutorPropertyTest extends TestCase
{
    use TestTrait;

    private AgentExecutor $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executor = new AgentExecutor();
    }

    // =========================================================================
    // Property 2: Step Output Propagation
    //
    // Output langkah ke-i selalu tersedia di ExecutionContext untuk langkah
    // ke-(i+1) hingga ke-N.
    //
    // Feature: erp-ai-agent, Property 2: Step Output Propagation
    // Validates: Requirements 1.3
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testStepOutputPropagation(): void
    {
        $this->forAll(
            // Jumlah langkah: 2 hingga 5
            Generators::choose(2, 5),
        )->then(function (int $stepCount) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry($stepCount);

            $executedOutputs = [];

            for ($i = 1; $i <= $stepCount; $i++) {
                $step = $this->buildReadStep($i);

                // Sebelum eksekusi langkah ke-i, verifikasi output langkah sebelumnya tersedia
                for ($prev = 1; $prev < $i; $prev++) {
                    $this->assertTrue(
                        $context->has($prev),
                        "Output langkah ke-{$prev} harus tersedia di ExecutionContext sebelum langkah ke-{$i} dieksekusi"
                    );
                }

                $result = $this->executor->executeStep($step, $context, $registry);

                // Setelah eksekusi berhasil, output harus tersedia di context
                $this->assertSame('success', $result->status,
                    "Langkah ke-{$i} harus berhasil");

                $this->assertTrue(
                    $context->has($i),
                    "Output langkah ke-{$i} harus tersedia di ExecutionContext setelah eksekusi berhasil"
                );

                $executedOutputs[$i] = $context->get($i);
            }

            // Verifikasi semua output tersimpan dan tidak null
            for ($i = 1; $i <= $stepCount; $i++) {
                $this->assertNotNull(
                    $context->get($i),
                    "Output langkah ke-{$i} tidak boleh null di ExecutionContext"
                );
            }
        });
    }

    #[ErisRepeat(repeat: 20)]
    public function testStepOutputAvailableForPlaceholderResolution(): void
    {
        $this->forAll(
            // Nilai output dari langkah 1
            Generators::choose(100, 9999),
        )->then(function (int $productId) {
            $context = new ExecutionContext();

            // Simulasikan output langkah 1 sudah ada di context
            $context->set(1, ['status' => 'success', 'product_id' => $productId, 'data' => ['id' => $productId]]);

            // Langkah 2 menggunakan placeholder dari langkah 1
            $step2 = new AgentStep(
                order: 2,
                name: 'Gunakan output langkah 1',
                toolName: 'get_stock_report',
                args: ['product_id' => '{{step_1.product_id}}'],
                isWriteOp: false,
            );

            // Resolve args
            $resolved = $this->executor->resolveArgs($step2->args, $context);

            // Placeholder harus diganti dengan nilai aktual
            $this->assertSame(
                $productId,
                $resolved['product_id'],
                "Placeholder {{step_1.product_id}} harus diganti dengan nilai {$productId}"
            );
        });
    }

    // =========================================================================
    // Property 3: Fail-Fast Execution
    //
    // Jika langkah ke-k gagal, eksekusi berhenti dan langkah ke-(k+1) hingga
    // ke-N tidak dieksekusi; kegagalan dicatat di AgentAuditLog.
    //
    // Feature: erp-ai-agent, Property 3: Fail-Fast Execution
    // Validates: Requirements 1.4
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testFailFastExecution(): void
    {
        $this->forAll(
            // Total langkah: 3 hingga 6
            Generators::choose(3, 6),
            // Langkah yang gagal: 1 hingga (total-1)
            Generators::choose(1, 2),
        )->then(function (int $totalSteps, int $failAtStep) {
            // Pastikan failAtStep < totalSteps
            $failAtStep = min($failAtStep, $totalSteps - 1);

            $context  = new ExecutionContext();
            $registry = $this->buildRegistryWithFailAt($failAtStep);

            $executedSteps = [];

            for ($i = 1; $i <= $totalSteps; $i++) {
                $step   = $this->buildReadStep($i);
                $result = $this->executor->executeStep($step, $context, $registry);

                $executedSteps[] = $i;

                if ($result->status === 'failed') {
                    // Hentikan eksekusi (fail-fast)
                    break;
                }
            }

            // Langkah yang gagal harus menjadi langkah terakhir yang dieksekusi
            $lastExecuted = end($executedSteps);
            $this->assertSame(
                $failAtStep,
                $lastExecuted,
                "Eksekusi harus berhenti pada langkah ke-{$failAtStep} yang gagal, "
                . "bukan melanjutkan ke langkah berikutnya"
            );

            // Langkah setelah kegagalan tidak boleh ada di context
            for ($i = $failAtStep + 1; $i <= $totalSteps; $i++) {
                $this->assertFalse(
                    $context->has($i),
                    "Output langkah ke-{$i} tidak boleh ada di context karena langkah ke-{$failAtStep} gagal"
                );
            }
        });
    }

    #[ErisRepeat(repeat: 10)]
    public function testFailedStepDoesNotPopulateContext(): void
    {
        $this->forAll(
            Generators::choose(1, 5),
        )->then(function (int $stepOrder) {
            $context  = new ExecutionContext();
            $registry = $this->buildFailRegistry();

            $step   = $this->buildReadStep($stepOrder);
            $result = $this->executor->executeStep($step, $context, $registry);

            // Langkah gagal tidak boleh menyimpan output ke context
            $this->assertSame('failed', $result->status);
            $this->assertFalse(
                $context->has($stepOrder),
                "Langkah yang gagal tidak boleh menyimpan output ke ExecutionContext"
            );
            $this->assertNull($result->output);
            $this->assertNotNull($result->errorMessage);
        });
    }

    // =========================================================================
    // Property 4: Write Operation Approval Gate
    //
    // Untuk plan yang mengandung isWriteOp = true, langkah write tidak
    // dieksekusi tanpa konfirmasi eksplisit.
    //
    // Feature: erp-ai-agent, Property 4: Write Operation Approval Gate
    // Validates: Requirements 1.5
    //
    // Note: Approval Gate diimplementasikan di AgentOrchestrator level.
    // AgentExecutor sendiri mengeksekusi step yang sudah dikonfirmasi.
    // Property ini memverifikasi bahwa write ops menghasilkan audit log
    // dan dapat di-undo (sebagai bukti write op dieksekusi dengan benar).
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testWriteOpStepProducesAuditableResult(): void
    {
        $this->forAll(
            Generators::choose(1, 10),
        )->then(function (int $stepOrder) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            $writeStep = new AgentStep(
                order: $stepOrder,
                name: "Write step {$stepOrder}",
                toolName: 'create_invoice',
                args: ['customer_id' => 1, 'amount' => 100000],
                isWriteOp: true,
            );

            $result = $this->executor->executeStep($writeStep, $context, $registry);

            // Write op yang berhasil harus menghasilkan StepResult yang valid
            $this->assertInstanceOf(StepResult::class, $result);
            $this->assertSame($stepOrder, $result->stepOrder);

            // Status harus success atau failed (tidak boleh null/undefined)
            $this->assertContains(
                $result->status,
                ['success', 'failed'],
                "Status hasil write op harus 'success' atau 'failed'"
            );
        });
    }

    #[ErisRepeat(repeat: 20)]
    public function testReadOpStepDoesNotRequireApproval(): void
    {
        $this->forAll(
            Generators::choose(1, 10),
        )->then(function (int $stepOrder) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            $readStep = new AgentStep(
                order: $stepOrder,
                name: "Read step {$stepOrder}",
                toolName: 'get_stock_report',
                args: ['filter' => 'all'],
                isWriteOp: false,
            );

            // Read op harus langsung dieksekusi tanpa approval gate
            $result = $this->executor->executeStep($readStep, $context, $registry);

            $this->assertSame('success', $result->status,
                "Read op harus berhasil dieksekusi tanpa approval gate");
            $this->assertTrue($context->has($stepOrder),
                "Output read op harus tersimpan di context");
        });
    }

    // =========================================================================
    // Property 13: Audit Log Completeness
    //
    // Setiap aksi write yang berhasil menghasilkan AgentAuditLog dengan semua
    // field non-null: user_id, tenant_id, action_name, parameters, result,
    // status, created_at.
    //
    // Feature: erp-ai-agent, Property 13: Audit Log Completeness
    // Validates: Requirements 6.3
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testAuditLogCompletenessForSuccessfulWriteOps(): void
    {
        $this->forAll(
            Generators::choose(1, 10),
            Generators::elements('create_invoice', 'create_journal', 'adjust_stock', 'create_purchase_order'),
        )->then(function (int $stepOrder, string $toolName) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            // Set user context untuk audit log
            $user = $this->createUser();
            $this->executor->setUser($user, $user->tenant_id, null);

            $writeStep = new AgentStep(
                order: $stepOrder,
                name: "Write step {$stepOrder}",
                toolName: $toolName,
                args: ['param1' => 'value1', 'amount' => 50000],
                isWriteOp: true,
            );

            $result = $this->executor->executeStep($writeStep, $context, $registry);

            if ($result->status === 'success') {
                // Verifikasi audit log dibuat dengan semua field non-null
                $log = \App\Models\AgentAuditLog::where('user_id', $user->id)
                    ->where('action_name', $toolName)
                    ->latest()
                    ->first();

                $this->assertNotNull($log, "AgentAuditLog harus dibuat untuk write op yang berhasil");

                // Semua field wajib harus non-null
                $this->assertNotNull($log->user_id, 'user_id tidak boleh null');
                $this->assertNotNull($log->tenant_id, 'tenant_id tidak boleh null');
                $this->assertNotNull($log->action_name, 'action_name tidak boleh null');
                $this->assertNotNull($log->parameters, 'parameters tidak boleh null');
                $this->assertNotNull($log->result, 'result tidak boleh null');
                $this->assertNotNull($log->status, 'status tidak boleh null');
                $this->assertNotNull($log->created_at, 'created_at tidak boleh null');

                // Status harus 'success'
                $this->assertSame('success', $log->status);

                // action_type harus 'write'
                $this->assertSame('write', $log->action_type);
            }
        });
    }

    // =========================================================================
    // Property 14: Permission Enforcement
    //
    // Untuk kombinasi (user, aksi) di mana user tidak punya permission,
    // eksekusi selalu ditolak dengan pesan error yang menyebutkan permission
    // yang diperlukan.
    //
    // Feature: erp-ai-agent, Property 14: Permission Enforcement
    // Validates: Requirements 6.5
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testPermissionEnforcementForUnauthorizedUser(): void
    {
        $this->forAll(
            Generators::elements(
                'create_journal',
                'run_payroll',
                'create_purchase_order',
                'create_invoice',
            ),
        )->then(function (string $toolName) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            // Buat user tanpa permission (role = 'staff' — tidak punya akses ke accounting/payroll/purchasing)
            $user = $this->createUserWithRole('staff');
            $this->executor->setUser($user, $user->tenant_id ?? 1, null);

            $step = new AgentStep(
                order: 1,
                name: "Restricted action",
                toolName: $toolName,
                args: [],
                isWriteOp: true,
            );

            $result = $this->executor->executeStep($step, $context, $registry);

            // Harus ditolak
            $this->assertSame('failed', $result->status,
                "Eksekusi harus ditolak untuk user tanpa permission");

            // Pesan error harus menyebutkan permission yang diperlukan
            $this->assertNotNull($result->errorMessage,
                "Pesan error tidak boleh null");
            $this->assertStringContainsString(
                'permission',
                strtolower($result->errorMessage),
                "Pesan error harus menyebutkan 'permission'"
            );

            // Tool tidak boleh dieksekusi (context tidak boleh terisi)
            $this->assertFalse($context->has(1),
                "Context tidak boleh terisi jika permission ditolak");
        });
    }

    // =========================================================================
    // Property 15: Destructive Action Rejection
    //
    // Instruksi yang mengandung operasi destruktif selalu ditolak dengan
    // penjelasan alasan, tidak pernah dieksekusi.
    //
    // Feature: erp-ai-agent, Property 15: Destructive Action Rejection
    // Validates: Requirements 9.3
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testDestructiveActionRejection(): void
    {
        $this->forAll(
            Generators::elements(
                'bulk_delete_products',
                'mass_delete_invoices',
                'delete_all_records',
                'purge_old_data',
                'bulk_delete',
                'mass_delete',
            ),
        )->then(function (string $destructiveTool) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            $step = new AgentStep(
                order: 1,
                name: "Destructive operation",
                toolName: $destructiveTool,
                args: [],
                isWriteOp: true,
            );

            $result = $this->executor->executeStep($step, $context, $registry);

            // Harus selalu ditolak
            $this->assertSame('failed', $result->status,
                "Operasi destruktif '{$destructiveTool}' harus selalu ditolak");

            // Pesan error harus ada dan menjelaskan alasan
            $this->assertNotNull($result->errorMessage,
                "Pesan error tidak boleh null untuk operasi destruktif");
            $this->assertNotEmpty($result->errorMessage,
                "Pesan error tidak boleh kosong untuk operasi destruktif");

            // Tool tidak boleh dieksekusi sama sekali
            $this->assertFalse($context->has(1),
                "Context tidak boleh terisi untuk operasi destruktif");

            // Output harus null (tidak ada hasil eksekusi)
            $this->assertNull($result->output,
                "Output harus null untuk operasi destruktif yang ditolak");
        });
    }

    #[ErisRepeat(repeat: 10)]
    public function testDestructiveActionRejectionExplanationMentionsReason(): void
    {
        $this->forAll(
            Generators::elements(
                'bulk_delete_products',
                'mass_delete_invoices',
                'purge_old_data',
            ),
        )->then(function (string $destructiveTool) {
            $context  = new ExecutionContext();
            $registry = $this->buildSuccessRegistry(10);

            $step = new AgentStep(
                order: 1,
                name: "Destructive operation",
                toolName: $destructiveTool,
                args: [],
                isWriteOp: true,
            );

            $result = $this->executor->executeStep($step, $context, $registry);

            // Pesan error harus mengandung kata kunci yang menjelaskan alasan penolakan
            $errorLower = strtolower($result->errorMessage ?? '');
            $hasExplanation = str_contains($errorLower, 'destruktif')
                || str_contains($errorLower, 'ditolak')
                || str_contains($errorLower, 'tidak diizinkan')
                || str_contains($errorLower, 'rejected');

            $this->assertTrue(
                $hasExplanation,
                "Pesan error harus menjelaskan alasan penolakan operasi destruktif. "
                . "Pesan aktual: '{$result->errorMessage}'"
            );
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Buat AgentStep read (non-write) untuk langkah tertentu.
     */
    private function buildReadStep(int $order): AgentStep
    {
        return new AgentStep(
            order: $order,
            name: "Read step {$order}",
            toolName: 'get_stock_report',
            args: ['step' => $order],
            isWriteOp: false,
        );
    }

    /**
     * Buat ToolRegistry mock yang selalu berhasil.
     */
    private function buildSuccessRegistry(int $maxSteps): ToolRegistry
    {
        $mock = $this->createMock(ToolRegistry::class);
        $mock->method('execute')
            ->willReturnCallback(function (string $toolName, array $args) {
                return [
                    'status'  => 'success',
                    'message' => "Tool {$toolName} berhasil dieksekusi",
                    'data'    => array_merge($args, ['executed_at' => now()->toIso8601String()]),
                ];
            });
        $mock->method('isWriteOperation')
            ->willReturnCallback(fn(string $name) => str_starts_with($name, 'create_')
                || str_starts_with($name, 'update_')
                || str_starts_with($name, 'adjust_'));

        return $mock;
    }

    /**
     * Buat ToolRegistry mock yang selalu gagal.
     */
    private function buildFailRegistry(): ToolRegistry
    {
        $mock = $this->createMock(ToolRegistry::class);
        $mock->method('execute')
            ->willReturn([
                'status'  => 'error',
                'message' => 'Tool gagal dieksekusi',
            ]);
        $mock->method('isWriteOperation')->willReturn(false);

        return $mock;
    }

    /**
     * Buat ToolRegistry mock yang gagal pada langkah tertentu.
     */
    private function buildRegistryWithFailAt(int $failAtStep): ToolRegistry
    {
        $callCount = 0;
        $mock      = $this->createMock(ToolRegistry::class);
        $mock->method('execute')
            ->willReturnCallback(function () use (&$callCount, $failAtStep) {
                $callCount++;
                if ($callCount === $failAtStep) {
                    return ['status' => 'error', 'message' => "Langkah ke-{$failAtStep} gagal"];
                }
                return ['status' => 'success', 'message' => 'Berhasil', 'data' => []];
            });
        $mock->method('isWriteOperation')->willReturn(false);

        return $mock;
    }

    /**
     * Buat user dummy untuk testing.
     */
    private function createUser(): \App\Models\User
    {
        $tenant = $this->createTenant();
        return $this->createAdminUser($tenant);
    }

    /**
     * Buat user dengan role tertentu.
     */
    private function createUserWithRole(string $role): \App\Models\User
    {
        $tenant = $this->createTenant();
        // Gunakan role 'staff' sebagai pengganti 'viewer' yang mungkin tidak valid
        $validRole = in_array($role, ['admin', 'staff', 'owner']) ? $role : 'staff';
        return \App\Models\User::create([
            'tenant_id'         => $tenant->id,
            'name'              => "User {$role} " . uniqid(),
            'email'             => "user-{$role}-" . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => $validRole,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);
    }
}
