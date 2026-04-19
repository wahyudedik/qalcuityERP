<?php

namespace Tests\Unit\Services\Agent;

use App\DTOs\Agent\AgentPlan;
use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ErpContext;
use App\Services\Agent\AgentPlanner;
use App\Services\GeminiService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit Tests for AgentPlanner.
 *
 * Feature: erp-ai-agent
 * Requirements: 1.1, 1.6
 */
class AgentPlannerTest extends TestCase
{
    private ErpContext $context;
    private array $tools;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new ErpContext(
            tenantId: 1,
            kpiSummary: [
                'revenue'          => 50000000.0,
                'critical_stock'   => 3,
                'overdue_ar'       => 10000000.0,
                'active_employees' => 25,
            ],
            activeModules: ['accounting', 'inventory', 'hrm', 'sales'],
            accountingPeriod: 'Januari 2025',
            industrySkills: ['Akuntansi & Keuangan'],
            builtAt: Carbon::now(),
        );

        $this->tools = [
            ['name' => 'get_stock_report', 'description' => 'Ambil laporan stok'],
            ['name' => 'get_sales_report', 'description' => 'Ambil laporan penjualan'],
            ['name' => 'create_journal', 'description' => 'Buat jurnal akuntansi'],
            ['name' => 'answer', 'description' => 'Jawab langsung'],
        ];
    }

    // =========================================================================
    // Happy Path: instruksi multi-step menghasilkan plan terurut
    // =========================================================================

    public function testMultiStepInstructionProducesOrderedPlan(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Analisis stok dan buat laporan',
                    'summary' => 'Cek stok kritis lalu buat laporan penjualan',
                    'steps'   => [
                        [
                            'order'         => 1,
                            'name'          => 'Cek stok kritis',
                            'toolName'      => 'get_stock_report',
                            'args'          => ['filter' => 'critical'],
                            'isWriteOp'     => false,
                            'dependsOnStep' => null,
                        ],
                        [
                            'order'         => 2,
                            'name'          => 'Buat laporan penjualan',
                            'toolName'      => 'get_sales_report',
                            'args'          => ['period' => 'this_month'],
                            'isWriteOp'     => false,
                            'dependsOnStep' => 'Cek stok kritis',
                        ],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan(
            'Cek stok kritis kemudian buat laporan penjualan',
            $this->context,
            $this->tools,
            'id'
        );

        $this->assertInstanceOf(AgentPlan::class, $plan);
        $this->assertCount(2, $plan->steps);
        $this->assertSame('id', $plan->language);
        $this->assertSame('Analisis stok dan buat laporan', $plan->goal);

        // Verifikasi urutan
        $this->assertSame(1, $plan->steps[0]->order);
        $this->assertSame(2, $plan->steps[1]->order);
        $this->assertSame('get_stock_report', $plan->steps[0]->toolName);
        $this->assertSame('get_sales_report', $plan->steps[1]->toolName);
    }

    public function testPlanWithWriteOpsSetHasWriteOpsTrue(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Buat jurnal',
                    'summary' => 'Buat jurnal penyesuaian',
                    'steps'   => [
                        [
                            'order'         => 1,
                            'name'          => 'Buat jurnal penyesuaian',
                            'toolName'      => 'create_journal',
                            'args'          => ['type' => 'adjustment'],
                            'isWriteOp'     => true,
                            'dependsOnStep' => null,
                        ],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Buat jurnal penyesuaian stok', $this->context, $this->tools, 'id');

        $this->assertTrue($plan->hasWriteOps);
        $this->assertTrue($plan->steps[0]->isWriteOp);
    }

    public function testPlanWithNoWriteOpsSetHasWriteOpsFalse(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Cek stok',
                    'summary' => 'Laporan stok',
                    'steps'   => [
                        [
                            'order'     => 1,
                            'name'      => 'Cek stok',
                            'toolName'  => 'get_stock_report',
                            'args'      => [],
                            'isWriteOp' => false,
                        ],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Cek stok saat ini', $this->context, $this->tools, 'id');

        $this->assertFalse($plan->hasWriteOps);
    }

    // =========================================================================
    // Edge Case: instruksi kosong
    // =========================================================================

    public function testEmptyInstructionReturnsFallbackSingleStepPlan(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        // Gemini tidak boleh dipanggil untuk instruksi kosong
        $geminiMock->expects($this->never())->method('generate');

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('', $this->context, $this->tools, 'id');

        $this->assertInstanceOf(AgentPlan::class, $plan);
        $this->assertCount(1, $plan->steps);
        $this->assertSame('answer', $plan->steps[0]->toolName);
        $this->assertFalse($plan->hasWriteOps);
    }

    public function testEmptyInstructionEnglishFallback(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->expects($this->never())->method('generate');

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('', $this->context, $this->tools, 'en');

        $this->assertCount(1, $plan->steps);
        $this->assertSame('en', $plan->language);
        $this->assertSame('answer', $plan->steps[0]->toolName);
    }

    // =========================================================================
    // Edge Case: plan dengan 1 langkah
    // =========================================================================

    public function testSingleStepPlanIsValid(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Cek stok',
                    'summary' => 'Satu langkah saja',
                    'steps'   => [
                        [
                            'order'     => 1,
                            'name'      => 'Ambil laporan stok',
                            'toolName'  => 'get_stock_report',
                            'args'      => [],
                            'isWriteOp' => false,
                        ],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Cek stok sekarang', $this->context, $this->tools, 'id');

        $this->assertCount(1, $plan->steps);
        $this->assertSame(1, $plan->steps[0]->order);
        $this->assertNotEmpty($plan->steps[0]->name);
        $this->assertNotEmpty($plan->steps[0]->toolName);
    }

    // =========================================================================
    // Edge Case: plan dengan tepat 10 langkah
    // =========================================================================

    public function testPlanWithExactlyTenStepsIsValid(): void
    {
        $steps = [];
        for ($i = 1; $i <= 10; $i++) {
            $steps[] = [
                'order'     => $i,
                'name'      => "Langkah {$i}",
                'toolName'  => 'get_stock_report',
                'args'      => ['step' => $i],
                'isWriteOp' => false,
            ];
        }

        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text'  => json_encode(['goal' => 'Test 10 langkah', 'summary' => '10 langkah', 'steps' => $steps]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Instruksi dengan 10 langkah', $this->context, $this->tools, 'id');

        $this->assertCount(10, $plan->steps);
        $this->assertSame(1, $plan->steps[0]->order);
        $this->assertSame(10, $plan->steps[9]->order);
    }

    public function testPlanTruncatesMoreThanTenSteps(): void
    {
        $steps = [];
        for ($i = 1; $i <= 15; $i++) {
            $steps[] = [
                'order'     => $i,
                'name'      => "Langkah {$i}",
                'toolName'  => 'get_stock_report',
                'args'      => [],
                'isWriteOp' => false,
            ];
        }

        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text'  => json_encode(['goal' => 'Test truncate', 'summary' => '15 langkah', 'steps' => $steps]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Instruksi sangat kompleks', $this->context, $this->tools, 'id');

        $this->assertLessThanOrEqual(10, count($plan->steps));
    }

    // =========================================================================
    // Fallback: Gemini gagal → retry → fallback single-turn
    // =========================================================================

    public function testGeminiFailureRetryAndFallbackToSingleTurn(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        // Gemini selalu throw exception (kedua percobaan gagal)
        $geminiMock->expects($this->exactly(2))
            ->method('generate')
            ->willThrowException(new \RuntimeException('Gemini API error'));

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan(
            'Cek stok kemudian buat laporan',
            $this->context,
            $this->tools,
            'id'
        );

        // Harus fallback ke single-turn
        $this->assertInstanceOf(AgentPlan::class, $plan);
        $this->assertCount(1, $plan->steps);
        $this->assertSame('answer', $plan->steps[0]->toolName);
        $this->assertFalse($plan->hasWriteOps);
    }

    public function testGeminiFirstFailRetrySucceeds(): void
    {
        $validPlanJson = json_encode([
            'goal'    => 'Cek stok',
            'summary' => 'Laporan stok',
            'steps'   => [
                [
                    'order'     => 1,
                    'name'      => 'Cek stok',
                    'toolName'  => 'get_stock_report',
                    'args'      => [],
                    'isWriteOp' => false,
                ],
            ],
        ]);

        $geminiMock = $this->createMock(GeminiService::class);
        // Percobaan pertama gagal, retry berhasil
        $geminiMock->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \RuntimeException('First attempt failed')),
                ['text' => $validPlanJson, 'model' => 'gemini-pro']
            );

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan(
            'Cek stok kemudian buat laporan',
            $this->context,
            $this->tools,
            'id'
        );

        // Retry berhasil → plan valid dengan 1 langkah
        $this->assertCount(1, $plan->steps);
        $this->assertSame('get_stock_report', $plan->steps[0]->toolName);
    }

    public function testGeminiReturnsInvalidJsonFallsBackToSingleTurn(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn(['text' => 'ini bukan JSON valid', 'model' => 'gemini-pro']);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan(
            'Cek stok kemudian buat laporan',
            $this->context,
            $this->tools,
            'id'
        );

        $this->assertCount(1, $plan->steps);
        $this->assertSame('answer', $plan->steps[0]->toolName);
    }

    public function testGeminiReturnsEmptyStepsFallsBackToSingleTurn(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text'  => json_encode(['goal' => 'test', 'summary' => 'test', 'steps' => []]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan(
            'Cek stok kemudian buat laporan',
            $this->context,
            $this->tools,
            'id'
        );

        $this->assertCount(1, $plan->steps);
        $this->assertSame('answer', $plan->steps[0]->toolName);
    }

    // =========================================================================
    // requiresPlanning() tests
    // =========================================================================

    public function testRequiresPlanningReturnsTrueForMultiStepKeywordsIndonesian(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $planner    = new AgentPlanner($geminiMock);

        $multiStepInstructions = [
            'Cek stok kemudian buat laporan',
            'Pertama analisis piutang, lalu buat invoice',
            'Bandingkan revenue bulan ini dengan bulan lalu',
            'Analisis data penjualan dan korelasikan dengan stok',
        ];

        foreach ($multiStepInstructions as $instruction) {
            $this->assertTrue(
                $planner->requiresPlanning($instruction),
                "requiresPlanning() harus true untuk: '{$instruction}'"
            );
        }
    }

    public function testRequiresPlanningReturnsTrueForMultiStepKeywordsEnglish(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $planner    = new AgentPlanner($geminiMock);

        $multiStepInstructions = [
            'Check stock then create a report',
            'First analyze receivables, then create invoice',
            'Compare this month revenue with last month',
            'Analyze sales data and correlate with inventory',
        ];

        foreach ($multiStepInstructions as $instruction) {
            $this->assertTrue(
                $planner->requiresPlanning($instruction),
                "requiresPlanning() harus true untuk: '{$instruction}'"
            );
        }
    }

    public function testRequiresPlanningReturnsFalseForSimpleInstructions(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $planner    = new AgentPlanner($geminiMock);

        $simpleInstructions = [
            'Apa itu jurnal akuntansi?',
            'Berapa stok produk A?',
            'Show me the dashboard',
            '',
        ];

        foreach ($simpleInstructions as $instruction) {
            $this->assertFalse(
                $planner->requiresPlanning($instruction),
                "requiresPlanning() harus false untuk: '{$instruction}'"
            );
        }
    }

    public function testRequiresPlanningReturnsTrueForLongInstructions(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $planner    = new AgentPlanner($geminiMock);

        // Instruksi panjang > 100 karakter tanpa keyword multi-step
        $longInstruction = 'Tolong berikan saya informasi lengkap mengenai kondisi keuangan perusahaan saat ini secara mendetail dan komprehensif';

        $this->assertTrue($planner->requiresPlanning($longInstruction));
    }

    public function testRequiresPlanningReturnsFalseForEmptyInstruction(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $planner    = new AgentPlanner($geminiMock);

        $this->assertFalse($planner->requiresPlanning(''));
        $this->assertFalse($planner->requiresPlanning('   '));
    }

    // =========================================================================
    // Language support tests
    // =========================================================================

    public function testPlanPreservesLanguageInResult(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Check stock',
                    'summary' => 'Stock report',
                    'steps'   => [
                        [
                            'order'     => 1,
                            'name'      => 'Get stock report',
                            'toolName'  => 'get_stock_report',
                            'args'      => [],
                            'isWriteOp' => false,
                        ],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);

        $planId = $planner->plan('Check stock then create report', $this->context, $this->tools, 'id');
        $this->assertSame('id', $planId->language);

        $planEn = $planner->plan('Check stock then create report', $this->context, $this->tools, 'en');
        $this->assertSame('en', $planEn->language);
    }

    public function testPlanHandlesMarkdownWrappedJson(): void
    {
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => "```json\n" . json_encode([
                    'goal'    => 'Cek stok',
                    'summary' => 'Laporan stok',
                    'steps'   => [
                        [
                            'order'     => 1,
                            'name'      => 'Cek stok',
                            'toolName'  => 'get_stock_report',
                            'args'      => [],
                            'isWriteOp' => false,
                        ],
                    ],
                ]) . "\n```",
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Cek stok kemudian buat laporan', $this->context, $this->tools, 'id');

        $this->assertCount(1, $plan->steps);
        $this->assertSame('get_stock_report', $plan->steps[0]->toolName);
    }

    public function testPlanStepsAreSortedByOrder(): void
    {
        // Gemini mengembalikan steps dalam urutan terbalik
        $geminiMock = $this->createMock(GeminiService::class);
        $geminiMock->method('generate')
            ->willReturn([
                'text' => json_encode([
                    'goal'    => 'Test urutan',
                    'summary' => 'Test',
                    'steps'   => [
                        ['order' => 3, 'name' => 'Langkah 3', 'toolName' => 'get_ar_report', 'args' => [], 'isWriteOp' => false],
                        ['order' => 1, 'name' => 'Langkah 1', 'toolName' => 'get_stock_report', 'args' => [], 'isWriteOp' => false],
                        ['order' => 2, 'name' => 'Langkah 2', 'toolName' => 'get_sales_report', 'args' => [], 'isWriteOp' => false],
                    ],
                ]),
                'model' => 'gemini-pro',
            ]);

        $planner = new AgentPlanner($geminiMock);
        $plan    = $planner->plan('Instruksi multi-step', $this->context, $this->tools, 'id');

        $this->assertSame(1, $plan->steps[0]->order);
        $this->assertSame(2, $plan->steps[1]->order);
        $this->assertSame(3, $plan->steps[2]->order);
    }
}
