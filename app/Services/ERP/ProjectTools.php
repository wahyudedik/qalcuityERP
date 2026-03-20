<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Str;

class ProjectTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_project',
                'description' => 'Buat proyek baru. Gunakan untuk: '
                    . '"buat proyek pembangunan rumah A budget 200 juta", '
                    . '"buat project website toko online 5 juta", '
                    . '"buat proyek renovasi kantor budget 50 juta untuk client PT Maju".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'          => ['type' => 'string',  'description' => 'Nama proyek'],
                        'budget'        => ['type' => 'number',  'description' => 'Total anggaran/budget (Rupiah)'],
                        'type'          => ['type' => 'string',  'description' => 'Jenis proyek: construction, it, service, general (default: general)'],
                        'customer_name' => ['type' => 'string',  'description' => 'Nama client/customer (opsional)'],
                        'start_date'    => ['type' => 'string',  'description' => 'Tanggal mulai YYYY-MM-DD (opsional)'],
                        'end_date'      => ['type' => 'string',  'description' => 'Tanggal selesai/deadline YYYY-MM-DD (opsional)'],
                        'description'   => ['type' => 'string',  'description' => 'Deskripsi proyek (opsional)'],
                        'tasks'         => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'   => ['type' => 'string',  'description' => 'Nama task/pekerjaan'],
                                    'budget' => ['type' => 'number',  'description' => 'Anggaran task (opsional)'],
                                    'weight' => ['type' => 'integer', 'description' => 'Bobot task untuk kalkulasi progress (default: 1)'],
                                ],
                                'required' => ['name'],
                            ],
                            'description' => 'Daftar task/pekerjaan dalam proyek (opsional)',
                        ],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name'        => 'get_project_status',
                'description' => 'Lihat status dan progress sebuah proyek. Gunakan untuk: '
                    . '"progress proyek berapa persen?", "status proyek rumah A", '
                    . '"update proyek X", "detail proyek Y".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
            [
                'name'        => 'update_project_progress',
                'description' => 'Update progress atau status proyek/task. Gunakan untuk: '
                    . '"proyek rumah A progress 60%", "task pondasi selesai", '
                    . '"update status proyek X jadi active", "tandai task Y done".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string',  'description' => 'Nama atau nomor proyek'],
                        'progress'     => ['type' => 'number',  'description' => 'Progress manual 0-100 persen (opsional, jika tidak diisi dihitung dari task)'],
                        'status'       => ['type' => 'string',  'description' => 'Status baru proyek: planning, active, on_hold, completed, cancelled (opsional)'],
                        'task_name'    => ['type' => 'string',  'description' => 'Nama task yang ingin diupdate statusnya (opsional)'],
                        'task_status'  => ['type' => 'string',  'description' => 'Status task baru: todo, in_progress, done, cancelled (opsional)'],
                        'notes'        => ['type' => 'string',  'description' => 'Catatan update (opsional)'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
            [
                'name'        => 'add_project_expense',
                'description' => 'Catat pengeluaran/biaya proyek. Gunakan untuk: '
                    . '"pengeluaran semen proyek A 5 juta", "beli material 3 juta untuk proyek rumah", '
                    . '"catat biaya tukang 2 juta proyek X", "pengeluaran proyek Y: cat 500 ribu".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'description'  => ['type' => 'string', 'description' => 'Keterangan pengeluaran'],
                        'amount'       => ['type' => 'number', 'description' => 'Jumlah pengeluaran (Rupiah)'],
                        'category'     => ['type' => 'string', 'description' => 'Kategori: material, labor, equipment, overhead, other (default: material)'],
                        'date'         => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (default: hari ini)'],
                        'reference'    => ['type' => 'string', 'description' => 'Nomor referensi/nota (opsional)'],
                    ],
                    'required' => ['project_name', 'description', 'amount'],
                ],
            ],
            [
                'name'        => 'log_timesheet',
                'description' => 'Catat jam kerja (timesheet) untuk sebuah proyek. Gunakan untuk: '
                    . '"catat kerja 5 jam hari ini proyek X", "log 8 jam proyek website", '
                    . '"timesheet proyek A: 3 jam desain UI", "kerja 6 jam di proyek rumah".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'hours'        => ['type' => 'number', 'description' => 'Jumlah jam kerja'],
                        'description'  => ['type' => 'string', 'description' => 'Deskripsi pekerjaan yang dilakukan'],
                        'date'         => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (default: hari ini)'],
                        'hourly_rate'  => ['type' => 'number', 'description' => 'Tarif per jam dalam Rupiah (opsional)'],
                    ],
                    'required' => ['project_name', 'hours', 'description'],
                ],
            ],
            [
                'name'        => 'get_project_summary',
                'description' => 'Ringkasan semua proyek aktif atau laporan proyek per periode. Gunakan untuk: '
                    . '"daftar proyek aktif", "laporan proyek bulan ini", '
                    . '"semua proyek yang sedang berjalan", "ringkasan proyek".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status: planning, active, on_hold, completed, cancelled (opsional)'],
                        'period' => ['type' => 'string', 'description' => 'Filter periode: today, this_week, this_month (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'add_project_task',
                'description' => 'Tambah task/pekerjaan baru ke proyek yang sudah ada. Gunakan untuk: '
                    . '"tambah task finishing ke proyek rumah A", '
                    . '"buat task testing untuk proyek website", '
                    . '"tambah pekerjaan instalasi listrik ke proyek X".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string',  'description' => 'Nama atau nomor proyek'],
                        'task_name'    => ['type' => 'string',  'description' => 'Nama task/pekerjaan'],
                        'budget'       => ['type' => 'number',  'description' => 'Anggaran task (opsional)'],
                        'weight'       => ['type' => 'integer', 'description' => 'Bobot task untuk progress (default: 1)'],
                        'due_date'     => ['type' => 'string',  'description' => 'Deadline task YYYY-MM-DD (opsional)'],
                        'assigned_to'  => ['type' => 'string',  'description' => 'Nama karyawan yang ditugaskan (opsional)'],
                    ],
                    'required' => ['project_name', 'task_name'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createProject(array $args): array
    {
        $customer = null;
        if (!empty($args['customer_name'])) {
            $customer = Customer::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['customer_name']}%")
                ->first();
        }

        $number  = 'PRJ-' . strtoupper(Str::random(6));
        $budget  = $args['budget'] ?? 0;
        $type    = $args['type'] ?? 'general';

        $project = Project::create([
            'tenant_id'   => $this->tenantId,
            'user_id'     => $this->userId,
            'customer_id' => $customer?->id,
            'number'      => $number,
            'name'        => $args['name'],
            'description' => $args['description'] ?? null,
            'type'        => $type,
            'status'      => 'planning',
            'start_date'  => $args['start_date'] ?? null,
            'end_date'    => $args['end_date'] ?? null,
            'budget'      => $budget,
            'actual_cost' => 0,
            'progress'    => 0,
        ]);

        // Buat tasks jika ada
        $taskCount = 0;
        if (!empty($args['tasks'])) {
            foreach ($args['tasks'] as $t) {
                ProjectTask::create([
                    'project_id' => $project->id,
                    'tenant_id'  => $this->tenantId,
                    'name'       => $t['name'],
                    'budget'     => $t['budget'] ?? 0,
                    'weight'     => $t['weight'] ?? 1,
                    'status'     => 'todo',
                ]);
                $taskCount++;
            }
        }

        $budgetStr  = $budget > 0 ? "\nBudget: **Rp " . number_format($budget, 0, ',', '.') . "**" : '';
        $clientStr  = $customer ? "\nClient: **{$customer->name}**" : '';
        $dateStr    = !empty($args['end_date']) ? "\nDeadline: **" . \Carbon\Carbon::parse($args['end_date'])->format('d M Y') . "**" : '';
        $taskStr    = $taskCount > 0 ? "\n{$taskCount} task ditambahkan." : '';

        return [
            'status'  => 'success',
            'message' => "Proyek **{$project->name}** berhasil dibuat.\n"
                . "Nomor: **{$number}**"
                . $budgetStr . $clientStr . $dateStr . $taskStr . "\n"
                . "Status: **Planning** — gunakan `update_project_progress` untuk mulai proyek.",
            'data' => [
                'number'  => $number,
                'name'    => $project->name,
                'budget'  => $budget,
                'tasks'   => $taskCount,
            ],
        ];
    }

    public function getProjectStatus(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (!$project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $project->load(['customer', 'tasks', 'expenses', 'timesheets']);

        $tasks = $project->tasks->map(fn($t) => [
            'nama'   => $t->name,
            'status' => $t->status,
            'bobot'  => $t->weight,
            'budget' => $t->budget > 0 ? 'Rp ' . number_format($t->budget, 0, ',', '.') : '-',
        ])->toArray();

        $totalHours = $project->timesheets->sum('hours');
        $variance   = $project->budgetVariance();
        $usedPct    = $project->budgetUsedPercent();

        return [
            'status' => 'success',
            'data'   => [
                'nomor'          => $project->number,
                'nama'           => $project->name,
                'client'         => $project->customer?->name ?? '-',
                'status'         => $project->status,
                'progress'       => $project->progress . '%',
                'budget'         => 'Rp ' . number_format($project->budget, 0, ',', '.'),
                'realisasi'      => 'Rp ' . number_format($project->actual_cost, 0, ',', '.'),
                'sisa_budget'    => 'Rp ' . number_format($variance, 0, ',', '.'),
                'budget_terpakai'=> $usedPct . '%',
                'status_budget'  => $variance >= 0 ? 'ON BUDGET' : 'OVER BUDGET',
                'mulai'          => $project->start_date?->format('d M Y') ?? '-',
                'deadline'       => $project->end_date?->format('d M Y') ?? '-',
                'total_jam_kerja'=> $totalHours . ' jam',
                'tasks'          => $tasks,
            ],
        ];
    }

    public function updateProjectProgress(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (!$project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $messages = [];

        // Update task status jika ada
        if (!empty($args['task_name']) && !empty($args['task_status'])) {
            $task = $project->tasks()
                ->where('name', 'like', "%{$args['task_name']}%")
                ->first();

            if ($task) {
                $task->update(['status' => $args['task_status']]);
                $statusLabel = match ($args['task_status']) {
                    'done'        => 'Selesai ✅',
                    'in_progress' => 'Sedang Dikerjakan',
                    'cancelled'   => 'Dibatalkan',
                    default       => $args['task_status'],
                };
                $messages[] = "Task **{$task->name}** → **{$statusLabel}**";

                // Recalculate progress dari task
                $project->recalculateProgress();
                $project->refresh();
            } else {
                $messages[] = "Task \"{$args['task_name']}\" tidak ditemukan di proyek ini.";
            }
        }

        // Override progress manual jika diberikan
        if (isset($args['progress'])) {
            $progress = max(0, min(100, (float) $args['progress']));
            $project->update(['progress' => $progress]);
            $messages[] = "Progress diupdate ke **{$progress}%**";
        }

        // Update status proyek
        if (!empty($args['status'])) {
            $newStatus = $args['status'];
            $allowed   = Project::VALID_TRANSITIONS[$project->status] ?? [];

            if (!in_array($newStatus, $allowed)) {
                $allowedStr = empty($allowed) ? 'tidak ada (status final)' : implode(', ', $allowed);
                return [
                    'status'  => 'error',
                    'message' => "Tidak bisa mengubah status proyek dari **{$project->status}** ke **{$newStatus}**.\n"
                        . "Transisi yang diizinkan: **{$allowedStr}**.",
                ];
            }

            $project->update(['status' => $newStatus]);
            $messages[] = "Status proyek → **{$newStatus}**";
        }

        if (!empty($args['notes'])) {
            $project->update(['notes' => $args['notes']]);
        }

        $project->refresh();

        return [
            'status'  => 'success',
            'message' => "Proyek **{$project->name}** diperbarui.\n"
                . implode("\n", $messages) . "\n"
                . "Progress saat ini: **{$project->progress}%** | Status: **{$project->status}**",
            'data' => [
                'number'   => $project->number,
                'progress' => $project->progress,
                'status'   => $project->status,
            ],
        ];
    }

    public function addProjectExpense(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (!$project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $expense = ProjectExpense::create([
            'project_id'  => $project->id,
            'tenant_id'   => $this->tenantId,
            'user_id'     => $this->userId,
            'category'    => $args['category'] ?? 'material',
            'description' => $args['description'],
            'amount'      => $args['amount'],
            'date'        => $args['date'] ?? today()->toDateString(),
            'reference'   => $args['reference'] ?? null,
            'notes'       => $args['notes'] ?? null,
        ]);

        // Update actual_cost proyek
        $project->recalculateActualCost();
        $project->refresh();

        $variance  = $project->budgetVariance();
        $usedPct   = $project->budgetUsedPercent();
        $budgetWarn = $variance < 0
            ? "\n⚠️ **OVER BUDGET** sebesar Rp " . number_format(abs($variance), 0, ',', '.')
            : ($usedPct >= 80 ? "\n⚠️ Budget sudah terpakai **{$usedPct}%**" : '');

        return [
            'status'  => 'success',
            'message' => "Pengeluaran **Rp " . number_format($args['amount'], 0, ',', '.') . "** untuk proyek **{$project->name}** berhasil dicatat.\n"
                . "Kategori: **{$expense->category}** — {$expense->description}\n"
                . "Total realisasi: **Rp " . number_format($project->actual_cost, 0, ',', '.') . "** / Budget: Rp " . number_format($project->budget, 0, ',', '.')
                . $budgetWarn,
            'data' => [
                'project'     => $project->name,
                'amount'      => $args['amount'],
                'actual_cost' => $project->actual_cost,
                'budget'      => $project->budget,
                'variance'    => $variance,
            ],
        ];
    }

    public function logTimesheet(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (!$project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $hours       = (float) $args['hours'];
        $hourlyRate  = (float) ($args['hourly_rate'] ?? 0);
        $laborCost   = $hours * $hourlyRate;

        Timesheet::create([
            'project_id'  => $project->id,
            'tenant_id'   => $this->tenantId,
            'user_id'     => $this->userId,
            'date'        => $args['date'] ?? today()->toDateString(),
            'hours'       => $hours,
            'description' => $args['description'],
            'hourly_rate' => $hourlyRate,
        ]);

        // Jika ada tarif, tambahkan ke project expense sebagai labor cost
        if ($laborCost > 0) {
            ProjectExpense::create([
                'project_id'  => $project->id,
                'tenant_id'   => $this->tenantId,
                'user_id'     => $this->userId,
                'category'    => 'labor',
                'description' => "Timesheet: {$args['description']} ({$hours} jam)",
                'amount'      => $laborCost,
                'date'        => $args['date'] ?? today()->toDateString(),
            ]);
            $project->recalculateActualCost();
        }

        // Total jam proyek
        $totalHours = Timesheet::where('project_id', $project->id)->sum('hours');

        $costMsg = $laborCost > 0
            ? "\nBiaya tenaga kerja: **Rp " . number_format($laborCost, 0, ',', '.') . "**"
            : '';

        return [
            'status'  => 'success',
            'message' => "Timesheet **{$hours} jam** untuk proyek **{$project->name}** berhasil dicatat.\n"
                . "Pekerjaan: {$args['description']}"
                . $costMsg . "\n"
                . "Total jam proyek: **{$totalHours} jam**",
            'data' => [
                'project'     => $project->name,
                'hours'       => $hours,
                'total_hours' => $totalHours,
                'labor_cost'  => $laborCost,
            ],
        ];
    }

    public function getProjectSummary(array $args): array
    {
        $query = Project::where('tenant_id', $this->tenantId)
            ->with(['customer', 'tasks']);

        if (!empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (!empty($args['period'])) {
            $query = $this->applyPeriod($query, $args['period']);
        }

        $projects = $query->latest()->get();

        if ($projects->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada proyek yang ditemukan.'];
        }

        $totalBudget     = $projects->sum('budget');
        $totalActualCost = $projects->sum('actual_cost');
        $byStatus        = $projects->groupBy('status')->map->count();

        $list = $projects->map(fn($p) => [
            'nomor'      => $p->number,
            'nama'       => $p->name,
            'client'     => $p->customer?->name ?? '-',
            'status'     => $p->status,
            'progress'   => $p->progress . '%',
            'budget'     => 'Rp ' . number_format($p->budget, 0, ',', '.'),
            'realisasi'  => 'Rp ' . number_format($p->actual_cost, 0, ',', '.'),
            'deadline'   => $p->end_date?->format('d M Y') ?? '-',
        ])->toArray();

        return [
            'status' => 'success',
            'data'   => [
                'total_proyek'   => $projects->count(),
                'per_status'     => $byStatus->toArray(),
                'total_budget'   => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                'total_realisasi'=> 'Rp ' . number_format($totalActualCost, 0, ',', '.'),
                'projects'       => $list,
            ],
        ];
    }

    public function addProjectTask(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (!$project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        // Cari user yang ditugaskan jika ada
        $assignedTo = null;
        if (!empty($args['assigned_to'])) {
            $assignedUser = User::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['assigned_to']}%")
                ->first();
            $assignedTo = $assignedUser?->id;
        }

        $task = ProjectTask::create([
            'project_id'  => $project->id,
            'tenant_id'   => $this->tenantId,
            'assigned_to' => $assignedTo,
            'name'        => $args['task_name'],
            'budget'      => $args['budget'] ?? 0,
            'weight'      => $args['weight'] ?? 1,
            'due_date'    => $args['due_date'] ?? null,
            'status'      => 'todo',
        ]);

        $assignMsg = $assignedTo ? " — ditugaskan ke **{$args['assigned_to']}**" : '';
        $dueMsg    = !empty($args['due_date'])
            ? ", deadline: **" . \Carbon\Carbon::parse($args['due_date'])->format('d M Y') . "**"
            : '';

        return [
            'status'  => 'success',
            'message' => "Task **{$task->name}** berhasil ditambahkan ke proyek **{$project->name}**{$assignMsg}{$dueMsg}.",
            'data'    => ['task' => $task->name, 'project' => $project->name],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function findProject(string $nameOrNumber): ?Project
    {
        return Project::where('tenant_id', $this->tenantId)
            ->where(fn($q) => $q->where('name', 'like', "%{$nameOrNumber}%")
                ->orWhere('number', $nameOrNumber)
                ->orWhere('number', 'like', "%{$nameOrNumber}%"))
            ->with(['tasks', 'expenses', 'timesheets'])
            ->first();
    }

    protected function applyPeriod($query, string $period)
    {
        return match ($period) {
            'today'      => $query->whereDate('created_at', today()),
            'this_week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default      => $query,
        };
    }
}
