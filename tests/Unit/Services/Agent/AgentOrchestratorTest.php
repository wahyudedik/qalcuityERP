<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\AgentPlan;
use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ErpContext;
use App\DTOs\Agent\StepResult;
use App\Models\ChatSession;
use App\Services\Agent\AgentContextBuilder;
use App\Services\Agent\AgentExecutor;
use App\Services\Agent\AgentOrchestrator;
use App\Services\Agent\AgentPlanner;
use App\Services\Agent\SkillRouter;
use App\Services\AiMemoryService;
use App\Services\GeminiService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit Tests for AgentOrchestrator (end-to-end dengan mock).
 *
 * Feature: erp-ai-agent
 * Requirements: 1.2, 1.5, 7.4, 7.5
 */
class AgentOrchestratorTest extends TestCase
{
    private AgentPlanner $planner;

    private AgentExecutor $executor;

    private AgentContextBuilder $contextBuilder;

    private SkillRouter $skillRouter;

    private AiMemoryService $memory;

    private GeminiService $gemini;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planner = $this->createMock(AgentPlanner::class);
        $this->executor = $this->createMock(AgentExecutor::class);
        $this->contextBuilder = $this->createMock(AgentContextBuilder::class);
        $this->skillRouter = $this->createMock(SkillRouter::class);
        $this->memory = $this->createMock(AiMemoryService::class);
        $this->gemini = $this->createMock(GeminiService::class);

        // Default stubs untuk contextBuilder dan skillRouter
        $this->contextBuilder->method('build')->willReturn($this->makeErpContext());
        $this->skillRouter->method('detectSkills')->willReturn([]);
        $this->skillRouter->method('buildSkillPrompt')->willReturn('');
        $this->memory->method('buildMemoryContext')->willReturn('');
    }

    // =========================================================================
    // Test 1: Happy path multi-step (2 read-only steps)
    // Requirements: 1.2, 7.1, 7.3
    // =========================================================================

    public function test_happy_path_multi_step_yields_all_expected_events(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [
            $this->makeStep(1, 'Cek stok', 'check_stock', false),
            $this->makeStep(2, 'Buat laporan', 'generate_report', false),
        ];

        $plan = new AgentPlan(
            goal: 'Cek stok dan buat laporan',
            steps: $steps,
            summary: 'Cek stok lalu buat laporan',
            hasWriteOps: false,
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);

        // Executor selalu berhasil
        $this->executor->method('setUser');
        $this->executor->method('executeStep')
            ->willReturnCallback(fn ($step, $ctx, $reg) => new StepResult(
                stepOrder: $step->order,
                status: 'success',
                output: ['data' => 'ok'],
            ));

        $orchestrator = $this->makeOrchestrator();
        $events = $this->collectEvents($orchestrator->handle('cek stok dan buat laporan', $user, $session));

        $eventNames = array_column($events, 'event');

        $this->assertContains('acknowledgment', $eventNames);
        $this->assertContains('plan_summary', $eventNames);
        $this->assertContains('step_started', $eventNames);
        $this->assertContains('step_completed', $eventNames);
        $this->assertContains('task_summary', $eventNames);

        // Harus ada 2x step_started dan 2x step_completed
        $this->assertSame(2, count(array_filter($events, fn ($e) => $e['event'] === 'step_started')));
        $this->assertSame(2, count(array_filter($events, fn ($e) => $e['event'] === 'step_completed')));

        // task_summary harus menunjukkan completed=2, failed=0
        $summary = $this->findEvent($events, 'task_summary');
        $this->assertSame(2, $summary['data']['completed']);
        $this->assertSame(0, $summary['data']['failed']);
    }

    // =========================================================================
    // Test 2: Approval Gate — write ops tanpa konfirmasi
    // Requirements: 1.5, 7.4
    // =========================================================================

    public function test_approval_gate_stops_execution_when_write_ops_without_confirmation(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [
            $this->makeStep(1, 'Buat invoice', 'create_invoice', true), // write op
        ];

        $plan = new AgentPlan(
            goal: 'Buat invoice',
            steps: $steps,
            summary: 'Buat invoice baru',
            hasWriteOps: true,
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);

        // executeStep TIDAK boleh dipanggil
        $this->executor->expects($this->never())->method('executeStep');

        $orchestrator = $this->makeOrchestrator();
        $events = $this->collectEvents(
            $orchestrator->handle('buat invoice', $user, $session, confirmed: false)
        );

        $eventNames = array_column($events, 'event');

        $this->assertContains('acknowledgment', $eventNames);
        $this->assertContains('plan_summary', $eventNames);
        $this->assertContains('approval_required', $eventNames);

        // Tidak boleh ada step_started atau task_summary
        $this->assertNotContains('step_started', $eventNames);
        $this->assertNotContains('task_summary', $eventNames);

        // Session harus dalam status awaiting_approval
        $session->refresh();
        $this->assertSame('awaiting_approval', $session->execution_status);
    }

    // =========================================================================
    // Test 3: Approval Gate — write ops dengan konfirmasi
    // Requirements: 1.5
    // =========================================================================

    public function test_approval_gate_executes_steps_when_confirmed(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [
            $this->makeStep(1, 'Buat invoice', 'create_invoice', true),
        ];

        $plan = new AgentPlan(
            goal: 'Buat invoice',
            steps: $steps,
            summary: 'Buat invoice baru',
            hasWriteOps: true,
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);

        $this->executor->method('setUser');
        $this->executor->method('executeStep')
            ->willReturn(new StepResult(stepOrder: 1, status: 'success', output: ['invoice_id' => 1]));

        $orchestrator = $this->makeOrchestrator();
        $events = $this->collectEvents(
            $orchestrator->handle('buat invoice', $user, $session, confirmed: true)
        );

        $eventNames = array_column($events, 'event');

        // Tidak boleh ada approval_required
        $this->assertNotContains('approval_required', $eventNames);

        // Harus ada step_started dan step_completed
        $this->assertContains('step_started', $eventNames);
        $this->assertContains('step_completed', $eventNames);
        $this->assertContains('task_summary', $eventNames);
    }

    // =========================================================================
    // Test 4: Cancellation — langkah berikutnya tidak dieksekusi setelah cancel
    // Requirements: 7.4, 7.5
    // =========================================================================

    public function test_cancellation_stops_execution_after_current_step(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [
            $this->makeStep(1, 'Langkah 1', 'tool_1', false),
            $this->makeStep(2, 'Langkah 2', 'tool_2', false),
            $this->makeStep(3, 'Langkah 3', 'tool_3', false),
        ];

        $plan = new AgentPlan(
            goal: 'Multi step',
            steps: $steps,
            summary: 'Tiga langkah',
            hasWriteOps: false,
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);

        $orchestrator = $this->makeOrchestrator();

        // Setelah langkah 1 selesai, set is_cancelled = true
        $callCount = 0;
        $this->executor->method('setUser');
        $this->executor->method('executeStep')
            ->willReturnCallback(function ($step, $ctx, $reg) use ($session, &$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    // Setelah langkah pertama, batalkan session
                    $session->is_cancelled = true;
                    $session->save();
                }

                return new StepResult(stepOrder: $step->order, status: 'success', output: ['ok' => true]);
            });

        $events = $this->collectEvents($orchestrator->handle('multi step', $user, $session));

        // Hanya 1 langkah yang dieksekusi
        $this->assertSame(1, $callCount, 'Hanya 1 langkah yang harus dieksekusi setelah cancel');

        $eventNames = array_column($events, 'event');

        // task_summary harus tetap dikirim
        $this->assertContains('task_summary', $eventNames);

        $summary = $this->findEvent($events, 'task_summary');
        $this->assertTrue($summary['data']['cancelled']);
        $this->assertSame(1, $summary['data']['completed']);
    }

    // =========================================================================
    // Test 5: Fail-fast — langkah berikutnya tidak dieksekusi setelah gagal
    // Requirements: 1.4
    // =========================================================================

    public function test_fail_fast_stops_execution_after_failed_step(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [
            $this->makeStep(1, 'Langkah 1', 'tool_1', false),
            $this->makeStep(2, 'Langkah 2 (gagal)', 'tool_2', false),
            $this->makeStep(3, 'Langkah 3', 'tool_3', false),
        ];

        $plan = new AgentPlan(
            goal: 'Multi step dengan kegagalan',
            steps: $steps,
            summary: 'Tiga langkah, langkah 2 gagal',
            hasWriteOps: false,
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);

        $callCount = 0;
        $this->executor->method('setUser');
        $this->executor->method('executeStep')
            ->willReturnCallback(function ($step, $ctx, $reg) use (&$callCount) {
                $callCount++;
                if ($step->order === 2) {
                    return new StepResult(
                        stepOrder: 2,
                        status: 'failed',
                        output: null,
                        errorMessage: 'Tool gagal dieksekusi',
                    );
                }

                return new StepResult(stepOrder: $step->order, status: 'success', output: ['ok' => true]);
            });

        $orchestrator = $this->makeOrchestrator();
        $events = $this->collectEvents($orchestrator->handle('multi step', $user, $session));

        // Hanya 2 langkah yang dieksekusi (langkah 3 tidak dieksekusi)
        $this->assertSame(2, $callCount, 'Langkah 3 tidak boleh dieksekusi setelah langkah 2 gagal');

        $eventNames = array_column($events, 'event');

        // Harus ada step_failed event
        $this->assertContains('step_failed', $eventNames);

        // task_summary harus menunjukkan failed=1
        $summary = $this->findEvent($events, 'task_summary');
        $this->assertSame(1, $summary['data']['completed']); // langkah 1 berhasil
        $this->assertSame(1, $summary['data']['failed']);    // langkah 2 gagal
    }

    // =========================================================================
    // Test 6: Single-turn (tidak perlu planning)
    // Requirements: 1.2
    // =========================================================================

    public function test_single_turn_calls_gemini_directly_without_planning(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $this->planner->method('requiresPlanning')->willReturn(false);

        // plan() TIDAK boleh dipanggil
        $this->planner->expects($this->never())->method('plan');

        // GeminiService harus dipanggil
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('withTenantContext')->willReturnSelf();
        $geminiMock->method('generate')->willReturn(['text' => 'Ini jawaban langsung dari AI.']);

        $orchestrator = new AgentOrchestrator(
            planner: $this->planner,
            executor: $this->executor,
            contextBuilder: $this->contextBuilder,
            skillRouter: $this->skillRouter,
            memory: $this->memory,
            gemini: $geminiMock,
        );

        $events = $this->collectEvents($orchestrator->handle('apa kabar?', $user, $session));

        $eventNames = array_column($events, 'event');

        // Harus ada acknowledgment dan task_summary
        $this->assertContains('acknowledgment', $eventNames);
        $this->assertContains('task_summary', $eventNames);

        // Tidak boleh ada plan_summary
        $this->assertNotContains('plan_summary', $eventNames);

        // task_summary harus menandai single_turn = true
        $summary = $this->findEvent($events, 'task_summary');
        $this->assertTrue($summary['data']['single_turn'] ?? false);
    }

    // =========================================================================
    // Test 7: cancel() method
    // Requirements: 7.4
    // =========================================================================

    public function test_cancel_method_sets_is_cancelled_flag(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $this->assertFalse($session->is_cancelled);

        $orchestrator = $this->makeOrchestrator();
        $orchestrator->cancel($session);

        $session->refresh();
        $this->assertTrue($session->is_cancelled);
        $this->assertSame('cancelled', $session->execution_status);
    }

    // =========================================================================
    // Test 8: Acknowledgment dikirim pertama kali
    // Requirements: 7.6
    // =========================================================================

    public function test_acknowledgment_is_first_event(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $this->planner->method('requiresPlanning')->willReturn(false);

        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('withTenantContext')->willReturnSelf();
        $geminiMock->method('generate')->willReturn(['text' => 'Jawaban.']);

        $orchestrator = new AgentOrchestrator(
            planner: $this->planner,
            executor: $this->executor,
            contextBuilder: $this->contextBuilder,
            skillRouter: $this->skillRouter,
            memory: $this->memory,
            gemini: $geminiMock,
        );

        $events = $this->collectEvents($orchestrator->handle('halo', $user, $session));

        $this->assertNotEmpty($events);
        $this->assertSame('acknowledgment', $events[0]['event'],
            'Event pertama harus selalu acknowledgment');
    }

    // =========================================================================
    // Test 9: Plan tanpa write ops tidak memerlukan approval
    // Requirements: 1.5
    // =========================================================================

    public function test_plan_without_write_ops_does_not_require_approval(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createAdminUser($tenant);
        $session = $this->createAgentSession($tenant->id, $user->id);

        $steps = [$this->makeStep(1, 'Baca data', 'read_data', false)];

        $plan = new AgentPlan(
            goal: 'Baca data',
            steps: $steps,
            summary: 'Baca data saja',
            hasWriteOps: false, // tidak ada write ops
            language: 'id',
        );

        $this->planner->method('requiresPlanning')->willReturn(true);
        $this->planner->method('plan')->willReturn($plan);
        $this->executor->method('setUser');
        $this->executor->method('executeStep')
            ->willReturn(new StepResult(stepOrder: 1, status: 'success', output: ['data' => []]));

        $orchestrator = $this->makeOrchestrator();
        $events = $this->collectEvents(
            $orchestrator->handle('baca data', $user, $session, confirmed: false)
        );

        $eventNames = array_column($events, 'event');

        // Tidak boleh ada approval_required
        $this->assertNotContains('approval_required', $eventNames);

        // Harus langsung dieksekusi
        $this->assertContains('step_started', $eventNames);
        $this->assertContains('step_completed', $eventNames);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makeOrchestrator(): AgentOrchestrator
    {
        return new AgentOrchestrator(
            planner: $this->planner,
            executor: $this->executor,
            contextBuilder: $this->contextBuilder,
            skillRouter: $this->skillRouter,
            memory: $this->memory,
            gemini: $this->gemini,
        );
    }

    private function makeErpContext(): ErpContext
    {
        return new ErpContext(
            tenantId: 1,
            kpiSummary: [
                'revenue' => 1000000,
                'critical_stock' => 0,
                'overdue_ar' => 0,
                'active_employees' => 5,
            ],
            activeModules: ['accounting', 'inventory', 'sales'],
            accountingPeriod: 'Jan 2025',
            industrySkills: [],
            builtAt: Carbon::now(),
        );
    }

    private function makeStep(int $order, string $name, string $toolName, bool $isWriteOp): AgentStep
    {
        return new AgentStep(
            order: $order,
            name: $name,
            toolName: $toolName,
            args: [],
            isWriteOp: $isWriteOp,
        );
    }

    private function createAgentSession(int $tenantId, int $userId): ChatSession
    {
        return ChatSession::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'title' => 'Test Agent Session',
            'session_type' => 'agent',
            'execution_status' => 'planning',
            'is_cancelled' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Kumpulkan semua event dari Generator menjadi array.
     */
    private function collectEvents(\Generator $generator): array
    {
        $events = [];
        foreach ($generator as $event) {
            $events[] = $event;
        }

        return $events;
    }

    /**
     * Cari event pertama dengan nama tertentu.
     */
    private function findEvent(array $events, string $eventName): ?array
    {
        foreach ($events as $event) {
            if ($event['event'] === $eventName) {
                return $event;
            }
        }

        return null;
    }
}
