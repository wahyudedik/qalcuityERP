<?php

namespace App\Services\ERP;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Workflow;
use App\Models\WorkflowExecutionLog;
use App\Services\Agent\CrossModuleQueryService;
use Illuminate\Support\Facades\Log;

/**
 * WorkflowTools — Task 13 & 14
 *
 * Tool cross-module yang mengeksekusi query paralel ke beberapa modul ERP
 * sekaligus dan mengkorelasikan hasilnya. Didaftarkan di ToolRegistry.
 *
 * Kombinasi yang didukung (Task 13):
 *  1. query_akuntansi_inventory  — Akuntansi + Inventory
 *  2. query_akuntansi_hrm        — Akuntansi + HRM
 *  3. query_penjualan_crm_inventory — Penjualan + CRM + Inventory
 *  4. query_hrm_payroll_absensi  — HRM + Payroll + Absensi
 *  5. query_project_keuangan     — Project + Keuangan
 *
 * Automation Builder Tools (Task 14):
 *  6. list_workflows   — Daftar workflow aktif tenant
 *  7. trigger_workflow — Picu workflow berdasarkan nama/ID
 *
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 10.1, 10.2, 10.3, 10.4, 10.5
 */
class WorkflowTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            // ── 1. Akuntansi + Inventory ──────────────────────────────────────
            [
                'name' => 'query_akuntansi_inventory',
                'description' => 'Analisis lintas modul: korelasikan data keuangan (pendapatan, pengeluaran, profit) '
                    .'dengan kondisi stok (stok kritis, nilai persediaan) untuk insight bisnis terintegrasi. '
                    .'Gunakan untuk: "bagaimana kondisi keuangan dan stok bulan ini?", '
                    .'"analisis profit vs nilai persediaan", "laporan keuangan dan inventory terpadu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode analisis: today, this_week, this_month, last_month, this_year, atau YYYY-MM. Default: this_month'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar modul aktif tenant (opsional). Jika tidak diisi, semua modul dianggap aktif.',
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 2. Akuntansi + HRM ────────────────────────────────────────────
            [
                'name' => 'query_akuntansi_hrm',
                'description' => 'Analisis lintas modul: korelasikan data keuangan dengan data SDM untuk analisis '
                    .'biaya tenaga kerja, produktivitas karyawan, dan pendapatan per karyawan. '
                    .'Gunakan untuk: "berapa pendapatan per karyawan?", '
                    .'"analisis biaya SDM vs pendapatan", "laporan keuangan dan HR terpadu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode analisis: today, this_week, this_month, last_month, this_year, atau YYYY-MM. Default: this_month'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar modul aktif tenant (opsional).',
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 3. Penjualan + CRM + Inventory ───────────────────────────────
            [
                'name' => 'query_penjualan_crm_inventory',
                'description' => 'Analisis lintas modul: korelasikan pipeline penjualan, data CRM (leads, pipeline), '
                    .'dan ketersediaan stok untuk analisis peluang bisnis dan kesiapan fulfillment. '
                    .'Gunakan untuk: "bagaimana pipeline sales dan stok kita?", '
                    .'"analisis leads CRM vs kemampuan fulfillment", "laporan penjualan, CRM, dan inventory terpadu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode analisis: today, this_week, this_month, last_month, this_year, atau YYYY-MM. Default: this_month'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar modul aktif tenant (opsional).',
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 4. HRM + Payroll + Absensi ────────────────────────────────────
            [
                'name' => 'query_hrm_payroll_absensi',
                'description' => 'Analisis lintas modul: korelasikan data karyawan, penggajian, dan kehadiran '
                    .'untuk analisis produktivitas dan biaya SDM secara menyeluruh. '
                    .'Gunakan untuk: "laporan SDM lengkap bulan ini", '
                    .'"analisis kehadiran dan penggajian", "berapa rata-rata gaji dan tingkat kehadiran karyawan?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode dalam format YYYY-MM. Default: bulan ini'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar modul aktif tenant (opsional).',
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 5. Project + Keuangan ─────────────────────────────────────────
            [
                'name' => 'query_project_keuangan',
                'description' => 'Analisis lintas modul: korelasikan data proyek (progress, budget, realisasi) '
                    .'dengan data keuangan (pendapatan, pengeluaran, piutang) untuk analisis profitabilitas proyek. '
                    .'Gunakan untuk: "bagaimana kondisi proyek dan keuangan kita?", '
                    .'"analisis budget proyek vs arus kas", "laporan project dan keuangan terpadu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode analisis: today, this_week, this_month, last_month, this_year, atau YYYY-MM. Default: this_month'],
                        'status' => ['type' => 'string', 'description' => 'Filter status proyek: planning, active, on_hold, completed, cancelled (opsional)'],
                        'active_modules' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar modul aktif tenant (opsional).',
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 6. List Workflows (Task 14) ───────────────────────────────────
            [
                'name' => 'list_workflows',
                'description' => 'Tampilkan daftar workflow otomatis (Automation Builder) yang tersedia untuk tenant ini. '
                    .'Mengembalikan nama, deskripsi, status aktif/nonaktif, tipe trigger, dan jumlah eksekusi. '
                    .'Gunakan untuk: "workflow apa saja yang ada?", "tampilkan daftar otomasi", '
                    .'"workflow mana yang aktif?", "ada automation apa di sistem?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'description' => 'Filter status: all (default), active, inactive.',
                            'enum' => ['all', 'active', 'inactive'],
                        ],
                    ],
                    'required' => [],
                ],
            ],

            // ── 7. Trigger Workflow (Task 14) ─────────────────────────────────
            [
                'name' => 'trigger_workflow',
                'description' => 'Picu (jalankan) sebuah workflow otomatis dari Automation Builder berdasarkan nama atau ID. '
                    .'Jika workflow tidak aktif, agent akan menginformasikan statusnya dan menawarkan alternatif. '
                    .'Jika aksi memerlukan approval, agent akan menginisiasi proses approval. '
                    .'Gunakan untuk: "jalankan workflow reorder stok", "trigger automation pengiriman notifikasi", '
                    .'"aktifkan workflow approval PO".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'workflow_name' => [
                            'type' => 'string',
                            'description' => 'Nama workflow yang akan dipicu (pencarian parsial diperbolehkan).',
                        ],
                        'workflow_id' => [
                            'type' => 'integer',
                            'description' => 'ID workflow yang akan dipicu (opsional, lebih presisi dari nama).',
                        ],
                        'parameters' => [
                            'type' => 'object',
                            'description' => 'Parameter konteks yang diteruskan ke workflow (opsional). '
                                .'Contoh: {"product_id": 5, "quantity": 100}.',
                        ],
                        'requires_approval' => [
                            'type' => 'boolean',
                            'description' => 'Set true jika aksi ini memerlukan approval workflow sebelum dieksekusi.',
                        ],
                        'model_type' => [
                            'type' => 'string',
                            'description' => 'Tipe model yang memerlukan approval (opsional, contoh: "PurchaseOrder").',
                        ],
                        'model_id' => [
                            'type' => 'integer',
                            'description' => 'ID model yang memerlukan approval (opsional).',
                        ],
                        'amount' => [
                            'type' => 'number',
                            'description' => 'Nilai transaksi untuk pengecekan approval workflow (opsional).',
                        ],
                    ],
                    'required' => [],
                ],
            ],
        ];
    }

    // =========================================================================
    // Tool Executors
    // =========================================================================

    public function queryAkuntansiInventory(array $args): array
    {
        $service = $this->makeService($args);

        return $service->queryAkuntansiInventory($args);
    }

    public function queryAkuntansiHrm(array $args): array
    {
        $service = $this->makeService($args);

        return $service->queryAkuntansiHrm($args);
    }

    public function queryPenjualanCrmInventory(array $args): array
    {
        $service = $this->makeService($args);

        return $service->queryPenjualanCrmInventory($args);
    }

    public function queryHrmPayrollAbsensi(array $args): array
    {
        $service = $this->makeService($args);

        return $service->queryHrmPayrollAbsensi($args);
    }

    public function queryProjectKeuangan(array $args): array
    {
        $service = $this->makeService($args);

        return $service->queryProjectKeuangan($args);
    }

    // ── Task 14: Automation Builder Tools ────────────────────────────────────

    /**
     * list_workflows — Requirement 10.3
     * Kembalikan daftar workflow tenant beserta status dan metadata.
     */
    public function listWorkflows(array $args): array
    {
        try {
            $statusFilter = $args['status'] ?? 'all';

            $query = Workflow::where('tenant_id', $this->tenantId)
                ->with(['actions'])
                ->orderBy('name');

            if ($statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('is_active', false);
            }

            $workflows = $query->get();

            if ($workflows->isEmpty()) {
                return [
                    'status' => 'success',
                    'message' => 'Belum ada workflow yang dikonfigurasi untuk tenant ini.',
                    'data' => [],
                    'total' => 0,
                ];
            }

            $data = $workflows->map(function (Workflow $wf) {
                return [
                    'id' => $wf->id,
                    'name' => $wf->name,
                    'description' => $wf->description,
                    'status' => $wf->is_active ? 'aktif' : 'nonaktif',
                    'trigger_type' => $wf->trigger_type,
                    'trigger_config' => $wf->trigger_config,
                    'action_count' => $wf->actions->count(),
                    'execution_count' => $wf->execution_count,
                    'last_executed_at' => $wf->last_executed_at?->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray();

            $activeCount = $workflows->where('is_active', true)->count();
            $inactiveCount = $workflows->where('is_active', false)->count();

            return [
                'status' => 'success',
                'message' => "Ditemukan {$workflows->count()} workflow ({$activeCount} aktif, {$inactiveCount} nonaktif).",
                'data' => $data,
                'total' => $workflows->count(),
                'active_count' => $activeCount,
                'inactive_count' => $inactiveCount,
            ];

        } catch (\Throwable $e) {
            Log::error('WorkflowTools::listWorkflows error', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Gagal mengambil daftar workflow: '.$e->getMessage(),
            ];
        }
    }

    /**
     * trigger_workflow — Requirements 10.1, 10.2, 10.4, 10.5
     *
     * Picu workflow berdasarkan nama atau ID.
     * - Jika workflow nonaktif → informasikan user + tawarkan alternatif (Req 10.5)
     * - Jika memerlukan approval → inisiasi ApprovalRequest (Req 10.4)
     * - Jika berhasil → catat eksekusi dan kembalikan hasil (Req 10.1, 10.2)
     */
    public function triggerWorkflow(array $args): array
    {
        try {
            // ── 1. Resolve workflow ───────────────────────────────────────────
            $workflow = $this->resolveWorkflow($args);

            if (! $workflow) {
                $identifier = $args['workflow_id'] ?? $args['workflow_name'] ?? '(tidak diketahui)';

                return [
                    'status' => 'error',
                    'message' => "Workflow \"{$identifier}\" tidak ditemukan. Gunakan tool list_workflows untuk melihat daftar workflow yang tersedia.",
                ];
            }

            // ── 2. Cek apakah workflow aktif (Requirement 10.5) ───────────────
            if (! $workflow->is_active) {
                $alternatives = $this->findAlternativeWorkflows($workflow);

                $altMessage = '';
                if (! empty($alternatives)) {
                    $altNames = implode(', ', array_column($alternatives, 'name'));
                    $altMessage = " Alternatif workflow yang aktif: {$altNames}.";
                } else {
                    $altMessage = ' Tidak ada workflow alternatif yang aktif saat ini. Anda dapat mengaktifkan workflow ini melalui menu Automation Builder.';
                }

                return [
                    'status' => 'inactive_workflow',
                    'message' => "Workflow \"{$workflow->name}\" saat ini dalam kondisi **nonaktif** dan tidak dapat dijalankan.{$altMessage}",
                    'workflow' => [
                        'id' => $workflow->id,
                        'name' => $workflow->name,
                        'description' => $workflow->description,
                        'status' => 'nonaktif',
                    ],
                    'alternatives' => $alternatives,
                ];
            }

            // ── 3. Cek apakah memerlukan approval (Requirement 10.4) ──────────
            if (! empty($args['requires_approval']) || $this->workflowRequiresApproval($args)) {
                return $this->initiateApprovalProcess($workflow, $args);
            }

            // ── 4. Eksekusi workflow (Requirement 10.1) ───────────────────────
            $context = array_merge($args['parameters'] ?? [], [
                'triggered_by' => 'ai_agent',
                'triggered_by_user' => $this->userId,
                'tenant_id' => $this->tenantId,
            ]);

            $success = $workflow->execute($context);

            // ── 5. Ambil log eksekusi terakhir (Requirement 10.2) ─────────────
            $lastLog = WorkflowExecutionLog::where('workflow_id', $workflow->id)
                ->where('tenant_id', $this->tenantId)
                ->latest('started_at')
                ->first();

            if ($success) {
                return [
                    'status' => 'success',
                    'message' => "Workflow \"{$workflow->name}\" berhasil dijalankan.",
                    'data' => [
                        'workflow_id' => $workflow->id,
                        'workflow_name' => $workflow->name,
                        'execution_status' => $lastLog?->status ?? 'success',
                        'started_at' => $lastLog?->started_at?->format('Y-m-d H:i:s'),
                        'completed_at' => $lastLog?->completed_at?->format('Y-m-d H:i:s'),
                        'duration_ms' => $lastLog?->duration_ms,
                        'execution_log_id' => $lastLog?->id,
                    ],
                ];
            }

            return [
                'status' => 'error',
                'message' => "Workflow \"{$workflow->name}\" gagal dijalankan. "
                    .($lastLog?->error_message ?? 'Periksa log eksekusi untuk detail lebih lanjut.'),
                'data' => [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->name,
                    'error' => $lastLog?->error_message,
                    'execution_log_id' => $lastLog?->id,
                ],
            ];

        } catch (\Throwable $e) {
            Log::error('WorkflowTools::triggerWorkflow error', [
                'tenant_id' => $this->tenantId,
                'args' => $args,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Gagal memicu workflow: '.$e->getMessage(),
            ];
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Resolve workflow dari args (by ID atau nama parsial).
     */
    private function resolveWorkflow(array $args): ?Workflow
    {
        if (! empty($args['workflow_id'])) {
            return Workflow::where('tenant_id', $this->tenantId)
                ->find((int) $args['workflow_id']);
        }

        if (! empty($args['workflow_name'])) {
            return Workflow::where('tenant_id', $this->tenantId)
                ->where('name', 'like', '%'.$args['workflow_name'].'%')
                ->orderBy('is_active', 'desc') // aktif lebih diprioritaskan
                ->first();
        }

        return null;
    }

    /**
     * Cari workflow aktif lain yang mungkin relevan sebagai alternatif.
     */
    private function findAlternativeWorkflows(Workflow $inactiveWorkflow): array
    {
        return Workflow::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where('id', '!=', $inactiveWorkflow->id)
            ->orderBy('execution_count', 'desc')
            ->limit(3)
            ->get(['id', 'name', 'description', 'trigger_type'])
            ->map(fn ($wf) => [
                'id' => $wf->id,
                'name' => $wf->name,
                'description' => $wf->description,
                'trigger_type' => $wf->trigger_type,
            ])
            ->toArray();
    }

    /**
     * Cek apakah args mengindikasikan kebutuhan approval workflow.
     * Requirement 10.4: jika ada model_type + model_id + amount, cek ApprovalWorkflow.
     */
    private function workflowRequiresApproval(array $args): bool
    {
        if (empty($args['model_type']) || empty($args['model_id'])) {
            return false;
        }

        $amount = (float) ($args['amount'] ?? 0);
        if ($amount <= 0) {
            return false;
        }

        return ApprovalWorkflow::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->exists();
    }

    /**
     * Inisiasi proses approval dan kembalikan respons informatif.
     * Requirement 10.4.
     */
    private function initiateApprovalProcess(Workflow $workflow, array $args): array
    {
        $amount = (float) ($args['amount'] ?? 0);
        $modelType = $args['model_type'] ?? null;
        $modelId = (int) ($args['model_id'] ?? 0);

        // Cari approval workflow yang berlaku
        $approvalWorkflow = null;
        if ($amount > 0) {
            $approvalWorkflow = ApprovalWorkflow::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->where('min_amount', '<=', $amount)
                ->where(function ($q) use ($amount) {
                    $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
                })
                ->orderBy('min_amount', 'desc')
                ->first();
        }

        // Buat ApprovalRequest jika ada approval workflow dan model yang valid
        $approvalRequest = null;
        if ($approvalWorkflow && $modelType && $modelId) {
            // Cek apakah sudah ada pending request
            $existing = ApprovalRequest::where('tenant_id', $this->tenantId)
                ->where('model_type', $modelType)
                ->where('model_id', $modelId)
                ->where('status', 'pending')
                ->first();

            if (! $existing) {
                $approvalRequest = ApprovalRequest::create([
                    'tenant_id' => $this->tenantId,
                    'workflow_id' => $approvalWorkflow->id,
                    'requested_by' => $this->userId,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'status' => 'pending',
                    'amount' => $amount,
                    'notes' => "Approval diminta oleh AI Agent untuk workflow: {$workflow->name}",
                ]);
            } else {
                $approvalRequest = $existing;
            }
        }

        $approverRoles = $approvalWorkflow?->approver_roles
            ? implode(', ', $approvalWorkflow->approver_roles)
            : 'approver yang berwenang';

        $message = "Aksi ini memerlukan persetujuan sebelum workflow \"{$workflow->name}\" dapat dijalankan. "
            ."Permintaan approval telah dikirimkan kepada {$approverRoles}. "
            .'Workflow akan dieksekusi secara otomatis setelah mendapat persetujuan.';

        return [
            'status' => 'pending_approval',
            'message' => $message,
            'data' => [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'approval_request_id' => $approvalRequest?->id,
                'approval_workflow' => $approvalWorkflow ? [
                    'id' => $approvalWorkflow->id,
                    'name' => $approvalWorkflow->name,
                    'approver_roles' => $approvalWorkflow->approver_roles,
                ] : null,
                'amount' => $amount > 0 ? $amount : null,
            ],
        ];
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function makeService(array $args): CrossModuleQueryService
    {
        $activeModules = $args['active_modules'] ?? [];

        return new CrossModuleQueryService($this->tenantId, $activeModules);
    }
}
