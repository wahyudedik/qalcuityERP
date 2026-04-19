<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\AgentPlan;
use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ErpContext;
use App\Services\Agent\AgentPlanner;
use App\Services\GeminiService;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for AgentPlanner.
 *
 * Feature: erp-ai-agent
 *
 * Property 1: Plan Step Count Invariant
 *
 * Validates: Requirements 1.1
 */
class AgentPlannerPropertyTest extends TestCase
{
    use TestTrait;

    private AgentPlanner $planner;

    /** Instruksi multi-step dalam Bahasa Indonesia */
    private array $indonesianInstructions = [
        'Cek stok produk kemudian buat laporan stok kritis',
        'Analisis piutang jatuh tempo lalu buat invoice pengingat',
        'Pertama cek karyawan aktif, kemudian buat laporan payroll bulan ini',
        'Bandingkan revenue bulan ini dengan bulan lalu dan buat ringkasan',
        'Cek stok, analisis penjualan, dan buat rekomendasi pembelian',
        'Buat jurnal penyesuaian stok kemudian posting ke buku besar',
        'Analisis data penjualan, korelasikan dengan stok, dan buat laporan',
        'Cek piutang jatuh tempo, kirim pengingat, dan update status invoice',
        'Buat purchase order untuk produk kritis lalu konfirmasi ke supplier',
        'Analisis kinerja karyawan kemudian buat laporan evaluasi bulanan',
    ];

    /** Instruksi multi-step dalam Bahasa Inggris */
    private array $englishInstructions = [
        'Check stock levels then create a critical stock report',
        'Analyze overdue receivables and then create reminder invoices',
        'First check active employees, then create this month payroll report',
        'Compare this month revenue with last month and create a summary',
        'Check stock, analyze sales, and create purchase recommendations',
        'Create stock adjustment journal then post to general ledger',
        'Analyze sales data, correlate with inventory, and create report',
        'Check overdue receivables, send reminders, and update invoice status',
        'Create purchase order for critical products then confirm to supplier',
        'Analyze employee performance then create monthly evaluation report',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Mock GeminiService untuk mengembalikan plan JSON yang valid
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturnCallback(function (string $prompt) {
                return ['text' => $this->generateMockPlanJson($prompt), 'model' => 'gemini-pro'];
            });

        $this->planner = new AgentPlanner($geminiMock);
    }

    // =========================================================================
    // Property 1: Plan Step Count Invariant
    //
    // Untuk instruksi apapun yang memerlukan planning, plan() menghasilkan
    // AgentPlan dengan 1–10 langkah, setiap langkah memiliki name, toolName,
    // dan args yang valid.
    //
    // Feature: erp-ai-agent, Property 1: Plan Step Count Invariant
    // Validates: Requirements 1.1
    // =========================================================================

    #[ErisRepeat(repeat: 20)]
    public function testPlanStepCountInvariantIndonesian(): void
    {
        $allInstructions = $this->indonesianInstructions;

        $this->forAll(
            Generators::elements(...$allInstructions),
        )->then(function (string $instruction) {
            $context = $this->buildMockContext();
            $tools   = $this->buildMockTools();

            $plan = $this->planner->plan($instruction, $context, $tools, 'id');

            $this->assertInstanceOf(
                AgentPlan::class,
                $plan,
                'plan() harus mengembalikan instance AgentPlan'
            );

            $stepCount = count($plan->steps);

            // ── Assert: jumlah langkah antara 1 dan 10 ──
            $this->assertGreaterThanOrEqual(
                1,
                $stepCount,
                "AgentPlan harus memiliki minimal 1 langkah, dapat: {$stepCount}"
            );
            $this->assertLessThanOrEqual(
                10,
                $stepCount,
                "AgentPlan tidak boleh memiliki lebih dari 10 langkah, dapat: {$stepCount}"
            );

            // ── Assert: setiap langkah memiliki struktur yang valid ──
            foreach ($plan->steps as $index => $step) {
                $this->assertInstanceOf(
                    AgentStep::class,
                    $step,
                    "Langkah ke-{$index} harus berupa AgentStep"
                );

                $this->assertNotEmpty(
                    $step->name,
                    "Langkah ke-{$index} harus memiliki name yang tidak kosong"
                );

                $this->assertNotEmpty(
                    $step->toolName,
                    "Langkah ke-{$index} harus memiliki toolName yang tidak kosong"
                );

                $this->assertIsArray(
                    $step->args,
                    "Langkah ke-{$index} harus memiliki args berupa array"
                );

                $this->assertIsInt(
                    $step->order,
                    "Langkah ke-{$index} harus memiliki order berupa integer"
                );

                $this->assertGreaterThanOrEqual(
                    1,
                    $step->order,
                    "Langkah ke-{$index} harus memiliki order >= 1"
                );
            }
        });
    }

    #[ErisRepeat(repeat: 20)]
    public function testPlanStepCountInvariantEnglish(): void
    {
        $allInstructions = $this->englishInstructions;

        $this->forAll(
            Generators::elements(...$allInstructions),
        )->then(function (string $instruction) {
            $context = $this->buildMockContext();
            $tools   = $this->buildMockTools();

            $plan = $this->planner->plan($instruction, $context, $tools, 'en');

            $this->assertInstanceOf(AgentPlan::class, $plan);

            $stepCount = count($plan->steps);

            $this->assertGreaterThanOrEqual(1, $stepCount);
            $this->assertLessThanOrEqual(10, $stepCount);

            foreach ($plan->steps as $index => $step) {
                $this->assertInstanceOf(AgentStep::class, $step);
                $this->assertNotEmpty($step->name);
                $this->assertNotEmpty($step->toolName);
                $this->assertIsArray($step->args);
            }
        });
    }

    #[ErisRepeat(repeat: 20)]
    public function testPlanStepCountInvariantWithVariableStepCount(): void
    {
        $this->forAll(
            // Generate jumlah langkah antara 1 dan 10
            Generators::choose(1, 10),
            // Generate instruksi dari pool
            Generators::elements(...$this->indonesianInstructions),
        )->then(function (int $targetSteps, string $instruction) {
            // Mock Gemini yang mengembalikan plan dengan jumlah langkah tertentu
            $geminiMock = $this->createMock(GeminiService::class);
            $geminiMock->method('generate')
                ->willReturn(['text' => $this->generateMockPlanJsonWithSteps($targetSteps), 'model' => 'gemini-pro']);

            $planner = new AgentPlanner($geminiMock);
            $context = $this->buildMockContext();
            $tools   = $this->buildMockTools();

            $plan = $planner->plan($instruction, $context, $tools, 'id');

            $stepCount = count($plan->steps);

            // Invariant: selalu antara 1 dan 10
            $this->assertGreaterThanOrEqual(1, $stepCount);
            $this->assertLessThanOrEqual(10, $stepCount);

            // Jika target <= 10, jumlah langkah harus sesuai target
            $this->assertSame(
                $targetSteps,
                $stepCount,
                "Plan dengan target {$targetSteps} langkah harus menghasilkan tepat {$targetSteps} langkah"
            );
        });
    }

    #[ErisRepeat(repeat: 10)]
    public function testPlanNeverExceedsMaxStepsEvenIfGeminiReturnsMore(): void
    {
        $this->forAll(
            // Generate jumlah langkah yang melebihi batas (11-20)
            Generators::choose(11, 20),
        )->then(function (int $excessiveSteps) {
            // Mock Gemini yang mengembalikan lebih dari 10 langkah
            $geminiMock = $this->createMock(GeminiService::class);
            $geminiMock->method('generate')
                ->willReturn(['text' => $this->generateMockPlanJsonWithSteps($excessiveSteps), 'model' => 'gemini-pro']);

            $planner = new AgentPlanner($geminiMock);
            $context = $this->buildMockContext();
            $tools   = $this->buildMockTools();

            $plan = $planner->plan('instruksi kompleks dengan banyak langkah', $context, $tools, 'id');

            // Invariant: tidak pernah lebih dari 10 langkah
            $this->assertLessThanOrEqual(
                10,
                count($plan->steps),
                "Plan tidak boleh memiliki lebih dari 10 langkah meskipun Gemini mengembalikan {$excessiveSteps} langkah"
            );
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function buildMockContext(): ErpContext
    {
        return new ErpContext(
            tenantId: 1,
            kpiSummary: [
                'revenue'          => 50000000.0,
                'critical_stock'   => 3,
                'overdue_ar'       => 10000000.0,
                'active_employees' => 25,
            ],
            activeModules: ['accounting', 'inventory', 'hrm', 'sales'],
            accountingPeriod: 'Januari 2025',
            industrySkills: ['Akuntansi & Keuangan', 'Inventory & Gudang'],
            builtAt: Carbon::now(),
        );
    }

    private function buildMockTools(): array
    {
        return [
            ['name' => 'get_stock_report', 'description' => 'Ambil laporan stok'],
            ['name' => 'get_sales_report', 'description' => 'Ambil laporan penjualan'],
            ['name' => 'get_ar_report', 'description' => 'Ambil laporan piutang'],
            ['name' => 'get_employee_list', 'description' => 'Ambil daftar karyawan'],
            ['name' => 'create_journal', 'description' => 'Buat jurnal akuntansi'],
            ['name' => 'create_invoice', 'description' => 'Buat invoice'],
            ['name' => 'create_purchase_order', 'description' => 'Buat purchase order'],
            ['name' => 'answer', 'description' => 'Jawab langsung tanpa tool'],
        ];
    }

    /**
     * Generate mock plan JSON dengan jumlah langkah default (2-5).
     */
    private function generateMockPlanJson(string $prompt): string
    {
        $stepCount = rand(2, 5);
        return $this->generateMockPlanJsonWithSteps($stepCount);
    }

    /**
     * Generate mock plan JSON dengan jumlah langkah tertentu.
     */
    private function generateMockPlanJsonWithSteps(int $stepCount): string
    {
        $tools = ['get_stock_report', 'get_sales_report', 'get_ar_report', 'get_employee_list', 'create_journal'];
        $steps = [];

        for ($i = 1; $i <= $stepCount; $i++) {
            $tool = $tools[($i - 1) % count($tools)];
            $steps[] = [
                'order'         => $i,
                'name'          => "Langkah {$i}",
                'toolName'      => $tool,
                'args'          => ['param' => "value_{$i}"],
                'isWriteOp'     => str_starts_with($tool, 'create'),
                'dependsOnStep' => $i > 1 ? "Langkah " . ($i - 1) : null,
            ];
        }

        return json_encode([
            'goal'    => 'Tujuan plan mock',
            'summary' => "Plan dengan {$stepCount} langkah",
            'steps'   => $steps,
        ]);
    }
}
