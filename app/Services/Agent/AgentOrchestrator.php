<?php

namespace App\Services\Agent;

use App\DTOs\Agent\AgentPlan;
use App\DTOs\Agent\ExecutionContext;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\AiMemoryService;
use App\Services\ERP\ToolRegistry;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

/**
 * AgentOrchestrator — Task 8
 *
 * Mengorkestrasi seluruh alur eksekusi ERP AI Agent:
 * build ErpContext → load memory → detect skills → plan → approval gate
 * → eksekusi langkah per langkah → update memory → kirim summary.
 *
 * Mengembalikan Generator untuk SSE streaming.
 *
 * Requirements: 1.2, 1.5, 7.1, 7.2, 7.3, 7.4, 7.5, 7.6
 */
class AgentOrchestrator
{
    public function __construct(
        private readonly AgentPlanner        $planner,
        private readonly AgentExecutor       $executor,
        private readonly AgentContextBuilder $contextBuilder,
        private readonly SkillRouter         $skillRouter,
        private readonly AiMemoryService     $memory,
        private readonly GeminiService       $gemini,
    ) {}

    /**
     * Handle pesan user dan orkestrasi eksekusi.
     * Mengembalikan Generator untuk SSE streaming.
     *
     * Alur:
     * 1. Yield acknowledgment segera (< 2 detik) — Requirement 7.6
     * 2. Build ErpContext via AgentContextBuilder
     * 3. Load memory via AiMemoryService
     * 4. Detect skills via SkillRouter
     * 5. Cek apakah perlu planning
     * 6. Jika perlu: plan → yield plan_summary
     * 7. Jika ada write ops dan belum confirmed: yield approval_required → STOP
     * 8. Eksekusi langkah per langkah dengan cek is_cancelled
     * 9. Update memory jika semua langkah berhasil
     * 10. Yield task_summary
     */
    public function handle(
        string $message,
        User $user,
        ChatSession $session,
        bool $confirmed = false,
    ): \Generator {
        // ── 1. Acknowledgment awal (< 2 detik) ───────────────────────────────
        yield ['event' => 'acknowledgment', 'data' => ['message' => 'Memproses permintaan...']];

        try {
            $tenantId = $user->tenant_id;

            // ── 2. Build ErpContext ───────────────────────────────────────────
            $activeModules = $this->resolveActiveModules($session);
            $erpContext    = $this->contextBuilder->build($tenantId, $activeModules);

            // Simpan snapshot ke session
            $session->erp_context_snapshot = $erpContext->kpiSummary;
            $session->save();

            // ── 3. Load memory ────────────────────────────────────────────────
            $memoryContext = $this->memory->buildMemoryContext($tenantId, $user->id);

            // ── 4. Detect skills ──────────────────────────────────────────────
            $skills      = $this->skillRouter->detectSkills($message, $activeModules);
            $skillPrompt = $this->skillRouter->buildSkillPrompt($skills, $erpContext);

            // ── 5. Cek apakah perlu planning ──────────────────────────────────
            $requiresPlanning = $this->planner->requiresPlanning($message);

            if (!$requiresPlanning) {
                // Single-turn: langsung jawab via GeminiService
                yield from $this->handleSingleTurn($message, $erpContext, $memoryContext, $skillPrompt, $session);
                return;
            }

            // ── 6. Plan ───────────────────────────────────────────────────────
            $session->execution_status = 'planning';
            $session->save();

            $toolRegistry  = new ToolRegistry($tenantId, $user->id);
            $availableTools = $toolRegistry->getDeclarations();

            $plan = $this->planner->plan(
                instruction: $message,
                context: $erpContext,
                availableTools: $availableTools,
                language: 'id',
            );

            // Simpan plan ke session
            $session->active_plan = $this->serializePlan($plan);
            $session->save();

            // Yield plan_summary
            yield [
                'event' => 'plan_summary',
                'data'  => [
                    'plan'          => $this->serializePlan($plan),
                    'has_write_ops' => $plan->hasWriteOps,
                ],
            ];

            // ── 7. Approval Gate ──────────────────────────────────────────────
            if ($plan->hasWriteOps && !$confirmed) {
                $session->execution_status = 'awaiting_approval';
                $session->save();

                yield [
                    'event' => 'approval_required',
                    'data'  => ['plan' => $this->serializePlan($plan)],
                ];

                return; // STOP — tunggu konfirmasi user
            }

            // ── 8. Eksekusi langkah per langkah ──────────────────────────────
            $session->execution_status = 'executing';
            $session->save();

            $this->executor->setUser($user, $tenantId, $session->id);

            $executionContext = new ExecutionContext();
            $completedSteps  = [];
            $failedStep      = null;
            $allSucceeded    = true;

            foreach ($plan->steps as $step) {
                // Cek flag is_cancelled sebelum setiap langkah
                $session->refresh();
                if ($session->is_cancelled) {
                    break;
                }

                // Yield step_started
                yield [
                    'event' => 'step_started',
                    'data'  => [
                        'step' => $step->order,
                        'name' => $step->name,
                    ],
                ];

                // Eksekusi langkah
                $stepResult = $this->executor->executeStep($step, $executionContext, $toolRegistry);

                if ($stepResult->isSuccess()) {
                    $completedSteps[] = [
                        'step'   => $step->order,
                        'name'   => $step->name,
                        'status' => 'success',
                        'output' => $stepResult->output,
                    ];

                    yield [
                        'event' => 'step_completed',
                        'data'  => [
                            'step'   => $step->order,
                            'name'   => $step->name,
                            'output' => $stepResult->output,
                        ],
                    ];
                } else {
                    $allSucceeded = false;
                    $failedStep   = [
                        'step'  => $step->order,
                        'name'  => $step->name,
                        'error' => $stepResult->errorMessage,
                    ];

                    yield [
                        'event' => 'step_failed',
                        'data'  => [
                            'step'  => $step->order,
                            'name'  => $step->name,
                            'error' => $stepResult->errorMessage,
                        ],
                    ];

                    // Fail-fast: hentikan eksekusi setelah langkah gagal
                    break;
                }
            }

            // ── 9. Update memory jika semua langkah berhasil ─────────────────
            if ($allSucceeded && !$session->is_cancelled) {
                try {
                    $this->saveTaskPattern($tenantId, $user->id, $plan);
                } catch (\Throwable $e) {
                    Log::warning('AgentOrchestrator: gagal menyimpan task pattern ke memory', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update status session
            $session->execution_status = $session->is_cancelled ? 'cancelled' : ($allSucceeded ? 'completed' : 'completed');
            $session->save();

            // ── 10. Yield task_summary ────────────────────────────────────────
            $actions = array_map(fn($s) => [
                'step'   => $s['step'],
                'name'   => $s['name'],
                'status' => $s['status'],
            ], $completedSteps);

            yield [
                'event' => 'task_summary',
                'data'  => [
                    'completed' => count($completedSteps),
                    'failed'    => $failedStep !== null ? 1 : 0,
                    'cancelled' => $session->is_cancelled,
                    'actions'   => $actions,
                    'failed_step' => $failedStep,
                ],
            ];

        } catch (\Throwable $e) {
            Log::error('AgentOrchestrator: error tidak terduga', [
                'error'      => $e->getMessage(),
                'session_id' => $session->id,
                'user_id'    => $user->id,
            ]);

            yield [
                'event' => 'step_failed',
                'data'  => [
                    'step'  => 0,
                    'error' => 'Terjadi kesalahan tidak terduga: ' . $e->getMessage(),
                ],
            ];

            yield [
                'event' => 'task_summary',
                'data'  => [
                    'completed'   => 0,
                    'failed'      => 1,
                    'cancelled'   => false,
                    'actions'     => [],
                    'failed_step' => ['error' => $e->getMessage()],
                ],
            ];
        }
    }

    /**
     * Batalkan eksekusi yang sedang berjalan untuk session tertentu.
     * Set flag is_cancelled = true; AgentOrchestrator akan cek flag ini
     * sebelum setiap langkah.
     *
     * Requirements: 7.4, 7.5
     */
    public function cancel(ChatSession $session): void
    {
        $session->is_cancelled     = true;
        $session->execution_status = 'cancelled';
        $session->save();

        Log::info('AgentOrchestrator: session dibatalkan', [
            'session_id' => $session->id,
        ]);
    }

    // ─── Private: Single-Turn Handler ────────────────────────────────────────

    /**
     * Handle single-turn response langsung via GeminiService.
     * Digunakan ketika instruksi tidak memerlukan multi-step planning.
     */
    private function handleSingleTurn(
        string $message,
        \App\DTOs\Agent\ErpContext $erpContext,
        string $memoryContext,
        string $skillPrompt,
        ChatSession $session,
    ): \Generator {
        $session->execution_status = 'completed';
        $session->save();

        try {
            $contextPrompt = $erpContext->toSystemPrompt();
            $fullContext   = implode("\n\n", array_filter([
                $contextPrompt,
                $memoryContext,
                $skillPrompt,
            ]));

            $response = $this->gemini
                ->withTenantContext($fullContext)
                ->generate($message);

            $text = $response['text'] ?? $response['message'] ?? 'Tidak ada respons dari AI.';

            yield [
                'event' => 'task_summary',
                'data'  => [
                    'completed'   => 1,
                    'failed'      => 0,
                    'cancelled'   => false,
                    'actions'     => [],
                    'response'    => $text,
                    'single_turn' => true,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('AgentOrchestrator: single-turn gagal', [
                'error' => $e->getMessage(),
            ]);

            yield [
                'event' => 'task_summary',
                'data'  => [
                    'completed'   => 0,
                    'failed'      => 1,
                    'cancelled'   => false,
                    'actions'     => [],
                    'error'       => $e->getMessage(),
                    'single_turn' => true,
                ],
            ];
        }
    }

    // ─── Private: Helpers ─────────────────────────────────────────────────────

    /**
     * Resolve daftar modul aktif dari session atau metadata tenant.
     */
    private function resolveActiveModules(ChatSession $session): array
    {
        $snapshot = $session->erp_context_snapshot;

        if (is_array($snapshot) && isset($snapshot['active_modules'])) {
            return $snapshot['active_modules'];
        }

        // Default modules yang selalu aktif
        return ['accounting', 'inventory', 'sales', 'hrm'];
    }

    /**
     * Serialize AgentPlan ke array untuk disimpan ke session dan dikirim via SSE.
     */
    private function serializePlan(AgentPlan $plan): array
    {
        return [
            'goal'          => $plan->goal,
            'summary'       => $plan->summary,
            'has_write_ops' => $plan->hasWriteOps,
            'language'      => $plan->language,
            'steps'         => array_map(fn($step) => [
                'order'          => $step->order,
                'name'           => $step->name,
                'tool_name'      => $step->toolName,
                'args'           => $step->args,
                'is_write_op'    => $step->isWriteOp,
                'depends_on_step' => $step->dependsOnStep,
            ], $plan->steps),
        ];
    }

    /**
     * Simpan pola task yang berhasil ke AiMemoryService.
     * Dipanggil setelah semua langkah berhasil dieksekusi.
     */
    private function saveTaskPattern(int $tenantId, int $userId, AgentPlan $plan): void
    {
        // Gunakan recordAction untuk menyimpan pola task
        $this->memory->recordAction(
            tenantId: $tenantId,
            userId: $userId,
            action: 'task_pattern_' . md5($plan->goal),
            context: [
                'value' => $plan->goal,
                'name'  => $plan->summary,
            ],
        );
    }
}
