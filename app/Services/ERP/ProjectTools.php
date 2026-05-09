<?php

namespace App\Services\ERP;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectTask;
use App\Models\RabItem;
use App\Models\TaskVolumeLog;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProjectTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'create_project',
                'description' => 'Buat proyek baru. Gunakan untuk: '
                    .'"buat proyek pembangunan rumah A budget 200 juta", '
                    .'"buat project website toko online 5 juta", '
                    .'"buat proyek renovasi kantor budget 50 juta untuk client PT Maju".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string',  'description' => 'Nama proyek'],
                        'budget' => ['type' => 'number',  'description' => 'Total anggaran/budget (Rupiah)'],
                        'type' => ['type' => 'string',  'description' => 'Jenis proyek: construction, it, service, general (default: general)'],
                        'customer_name' => ['type' => 'string',  'description' => 'Nama client/customer (opsional)'],
                        'start_date' => ['type' => 'string',  'description' => 'Tanggal mulai YYYY-MM-DD (opsional)'],
                        'end_date' => ['type' => 'string',  'description' => 'Tanggal selesai/deadline YYYY-MM-DD (opsional)'],
                        'description' => ['type' => 'string',  'description' => 'Deskripsi proyek (opsional)'],
                        'tasks' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string',  'description' => 'Nama task/pekerjaan'],
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
                'name' => 'get_project_status',
                'description' => 'Lihat status dan progress sebuah proyek. Gunakan untuk: '
                    .'"progress proyek berapa persen?", "status proyek rumah A", '
                    .'"update proyek X", "detail proyek Y".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
            [
                'name' => 'update_project_progress',
                'description' => 'Update progress atau status proyek/task. Gunakan untuk: '
                    .'"proyek rumah A progress 60%", "task pondasi selesai", '
                    .'"update status proyek X jadi active", "tandai task Y done".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string',  'description' => 'Nama atau nomor proyek'],
                        'progress' => ['type' => 'number',  'description' => 'Progress manual 0-100 persen (opsional, jika tidak diisi dihitung dari task)'],
                        'status' => ['type' => 'string',  'description' => 'Status baru proyek: planning, active, on_hold, completed, cancelled (opsional)'],
                        'task_name' => ['type' => 'string',  'description' => 'Nama task yang ingin diupdate statusnya (opsional)'],
                        'task_status' => ['type' => 'string',  'description' => 'Status task baru: todo, in_progress, done, cancelled (opsional)'],
                        'notes' => ['type' => 'string',  'description' => 'Catatan update (opsional)'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
            [
                'name' => 'add_project_expense',
                'description' => 'Catat pengeluaran/biaya proyek. Gunakan untuk: '
                    .'"pengeluaran semen proyek A 5 juta", "beli material 3 juta untuk proyek rumah", '
                    .'"catat biaya tukang 2 juta proyek X", "pengeluaran proyek Y: cat 500 ribu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'description' => ['type' => 'string', 'description' => 'Keterangan pengeluaran'],
                        'amount' => ['type' => 'number', 'description' => 'Jumlah pengeluaran (Rupiah)'],
                        'category' => ['type' => 'string', 'description' => 'Kategori: material, labor, equipment, overhead, other (default: material)'],
                        'date' => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (default: hari ini)'],
                        'reference' => ['type' => 'string', 'description' => 'Nomor referensi/nota (opsional)'],
                    ],
                    'required' => ['project_name', 'description', 'amount'],
                ],
            ],
            [
                'name' => 'log_timesheet',
                'description' => 'Catat jam kerja (timesheet) untuk sebuah proyek. Gunakan untuk: '
                    .'"catat kerja 5 jam hari ini proyek X", "log 8 jam proyek website", '
                    .'"timesheet proyek A: 3 jam desain UI", "kerja 6 jam di proyek rumah".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'hours' => ['type' => 'number', 'description' => 'Jumlah jam kerja'],
                        'description' => ['type' => 'string', 'description' => 'Deskripsi pekerjaan yang dilakukan'],
                        'date' => ['type' => 'string', 'description' => 'Tanggal YYYY-MM-DD (default: hari ini)'],
                        'hourly_rate' => ['type' => 'number', 'description' => 'Tarif per jam dalam Rupiah (opsional)'],
                    ],
                    'required' => ['project_name', 'hours', 'description'],
                ],
            ],
            [
                'name' => 'get_project_summary',
                'description' => 'Ringkasan semua proyek aktif atau laporan proyek per periode. Gunakan untuk: '
                    .'"daftar proyek aktif", "laporan proyek bulan ini", '
                    .'"semua proyek yang sedang berjalan", "ringkasan proyek".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status: planning, active, on_hold, completed, cancelled (opsional)'],
                        'period' => ['type' => 'string', 'description' => 'Filter periode: today, this_week, this_month (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'add_project_task',
                'description' => 'Tambah task/pekerjaan baru ke proyek yang sudah ada. '
                    .'Mendukung tracking volume fisik untuk konstruksi. Gunakan untuk: '
                    .'"tambah task pengecoran lantai 1 target 120 m3 proyek rumah A", '
                    .'"buat task galian pondasi 500 m3", '
                    .'"tambah pekerjaan instalasi listrik ke proyek X".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string',  'description' => 'Nama atau nomor proyek'],
                        'task_name' => ['type' => 'string',  'description' => 'Nama task/pekerjaan'],
                        'budget' => ['type' => 'number',  'description' => 'Anggaran task (opsional)'],
                        'weight' => ['type' => 'integer', 'description' => 'Bobot task untuk progress (default: 1)'],
                        'due_date' => ['type' => 'string',  'description' => 'Deadline task YYYY-MM-DD (opsional)'],
                        'assigned_to' => ['type' => 'string',  'description' => 'Nama karyawan yang ditugaskan (opsional)'],
                        'progress_method' => ['type' => 'string',  'description' => 'Metode tracking: "status" (default, berbasis status task) atau "volume" (berbasis volume fisik, untuk konstruksi)'],
                        'target_volume' => ['type' => 'number',  'description' => 'Target volume fisik (wajib jika progress_method=volume). Contoh: 120 untuk 120 m³'],
                        'volume_unit' => ['type' => 'string',  'description' => 'Satuan volume: m3, m2, m, kg, batang, titik, unit, dll'],
                    ],
                    'required' => ['project_name', 'task_name'],
                ],
            ],
            // ── RAB (Rencana Anggaran Biaya) ──────────────────────
            [
                'name' => 'add_rab_item',
                'description' => 'Tambah item RAB (Rencana Anggaran Biaya) ke proyek. '
                    .'Gunakan untuk: "tambah RAB semen 100 sak harga 65000", '
                    .'"RAB pengecoran lantai 1: 120 m3 harga 1.200.000/m3", '
                    .'"buat grup RAB Pekerjaan Struktur".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string',  'description' => 'Nama atau nomor proyek'],
                        'name' => ['type' => 'string',  'description' => 'Uraian pekerjaan / nama item'],
                        'type' => ['type' => 'string',  'description' => 'Tipe: "group" (header/grup) atau "item" (item pekerjaan). Default: item'],
                        'group_name' => ['type' => 'string',  'description' => 'Nama grup parent (opsional, untuk memasukkan item ke dalam grup)'],
                        'code' => ['type' => 'string',  'description' => 'Kode item, misal: I, I.1, I.1.a (opsional)'],
                        'category' => ['type' => 'string',  'description' => 'Kategori: material, labor, equipment, subcontract, overhead (opsional)'],
                        'volume' => ['type' => 'number',  'description' => 'Volume/kuantitas pekerjaan'],
                        'unit' => ['type' => 'string',  'description' => 'Satuan: m3, m2, kg, sak, batang, ls, unit, titik, dll'],
                        'unit_price' => ['type' => 'number',  'description' => 'Harga satuan (Rupiah)'],
                        'coefficient' => ['type' => 'number',  'description' => 'Koefisien pengali (default: 1)'],
                        'notes' => ['type' => 'string',  'description' => 'Catatan tambahan (opsional)'],
                    ],
                    'required' => ['project_name', 'name'],
                ],
            ],
            [
                'name' => 'get_rab',
                'description' => 'Lihat RAB (Rencana Anggaran Biaya) proyek. '
                    .'Gunakan untuk: "lihat RAB proyek rumah A", "total RAB berapa?", '
                    .'"breakdown biaya proyek", "RAB vs realisasi".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
            [
                'name' => 'record_rab_actual',
                'description' => 'Catat realisasi biaya dan volume aktual untuk item RAB. '
                    .'Gunakan untuk: "realisasi pengecoran lantai 1 sudah 80 m3 biaya 90 juta", '
                    .'"update realisasi semen 60 juta".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'item_name' => ['type' => 'string', 'description' => 'Nama item RAB yang akan diupdate'],
                        'actual_cost' => ['type' => 'number', 'description' => 'Realisasi biaya (Rupiah)'],
                        'actual_volume' => ['type' => 'number', 'description' => 'Realisasi volume (opsional)'],
                    ],
                    'required' => ['project_name', 'item_name'],
                ],
            ],
            // ── Volume Progress ───────────────────────────────────
            [
                'name' => 'record_volume_progress',
                'description' => 'Catat progress volume fisik untuk task proyek. '
                    .'Gunakan untuk: "pengecoran lantai 2 sudah 45 m3", '
                    .'"progress galian hari ini 30 m3", '
                    .'"catat volume pemasangan bata 50 m2 hari ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                        'task_name' => ['type' => 'string', 'description' => 'Nama task/pekerjaan'],
                        'volume' => ['type' => 'number', 'description' => 'Volume yang dikerjakan (ditambahkan ke volume aktual)'],
                        'description' => ['type' => 'string', 'description' => 'Keterangan pekerjaan (opsional)'],
                    ],
                    'required' => ['project_name', 'task_name', 'volume'],
                ],
            ],
            [
                'name' => 'get_volume_progress',
                'description' => 'Lihat progress volume fisik semua task di proyek. '
                    .'Gunakan untuk: "progress volume proyek rumah A", '
                    .'"berapa persen pengecoran sudah selesai?", '
                    .'"volume fisik proyek hari ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'project_name' => ['type' => 'string', 'description' => 'Nama atau nomor proyek'],
                    ],
                    'required' => ['project_name'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createProject(array $args): array
    {
        $customer = null;
        if (! empty($args['customer_name'])) {
            $customer = Customer::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['customer_name']}%")
                ->first();
        }

        $number = 'PRJ-'.strtoupper(Str::random(6));
        $budget = $args['budget'] ?? 0;
        $type = $args['type'] ?? 'general';

        $project = Project::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer?->id,
            'number' => $number,
            'name' => $args['name'],
            'description' => $args['description'] ?? null,
            'type' => $type,
            'status' => 'planning',
            'start_date' => $args['start_date'] ?? null,
            'end_date' => $args['end_date'] ?? null,
            'budget' => $budget,
            'actual_cost' => 0,
            'progress' => 0,
        ]);

        // Buat tasks jika ada
        $taskCount = 0;
        if (! empty($args['tasks'])) {
            foreach ($args['tasks'] as $t) {
                ProjectTask::create([
                    'project_id' => $project->id,
                    'tenant_id' => $this->tenantId,
                    'name' => $t['name'],
                    'budget' => $t['budget'] ?? 0,
                    'weight' => $t['weight'] ?? 1,
                    'status' => 'todo',
                ]);
                $taskCount++;
            }
        }

        $budgetStr = $budget > 0 ? "\nBudget: **Rp ".number_format($budget, 0, ',', '.').'**' : '';
        $clientStr = $customer ? "\nClient: **{$customer->name}**" : '';
        $dateStr = ! empty($args['end_date']) ? "\nDeadline: **".Carbon::parse($args['end_date'])->format('d M Y').'**' : '';
        $taskStr = $taskCount > 0 ? "\n{$taskCount} task ditambahkan." : '';

        return [
            'status' => 'success',
            'message' => "Proyek **{$project->name}** berhasil dibuat.\n"
                ."Nomor: **{$number}**"
                .$budgetStr.$clientStr.$dateStr.$taskStr."\n"
                .'Status: **Planning** — gunakan `update_project_progress` untuk mulai proyek.',
            'data' => [
                'number' => $number,
                'name' => $project->name,
                'budget' => $budget,
                'tasks' => $taskCount,
            ],
        ];
    }

    public function getProjectStatus(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $project->load(['customer', 'tasks', 'expenses', 'timesheets']);

        $tasks = $project->tasks->map(fn ($t) => [
            'nama' => $t->name,
            'status' => $t->status,
            'bobot' => $t->weight,
            'budget' => $t->budget > 0 ? 'Rp '.number_format($t->budget, 0, ',', '.') : '-',
        ])->toArray();

        $totalHours = $project->timesheets->sum('hours');
        $variance = $project->budgetVariance();
        $usedPct = $project->budgetUsedPercent();

        return [
            'status' => 'success',
            'data' => [
                'nomor' => $project->number,
                'nama' => $project->name,
                'client' => $project->customer?->name ?? '-',
                'status' => $project->status,
                'progress' => $project->progress.'%',
                'budget' => 'Rp '.number_format($project->budget, 0, ',', '.'),
                'realisasi' => 'Rp '.number_format($project->actual_cost, 0, ',', '.'),
                'sisa_budget' => 'Rp '.number_format($variance, 0, ',', '.'),
                'budget_terpakai' => $usedPct.'%',
                'status_budget' => $variance >= 0 ? 'ON BUDGET' : 'OVER BUDGET',
                'mulai' => $project->start_date?->format('d M Y') ?? '-',
                'deadline' => $project->end_date?->format('d M Y') ?? '-',
                'total_jam_kerja' => $totalHours.' jam',
                'tasks' => $tasks,
            ],
        ];
    }

    public function updateProjectProgress(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $messages = [];

        // Update task status jika ada
        if (! empty($args['task_name']) && ! empty($args['task_status'])) {
            $task = $project->tasks()
                ->where('name', 'like', "%{$args['task_name']}%")
                ->first();

            if ($task) {
                $task->update(['status' => $args['task_status']]);
                $statusLabel = match ($args['task_status']) {
                    'done' => 'Selesai ✅',
                    'in_progress' => 'Sedang Dikerjakan',
                    'cancelled' => 'Dibatalkan',
                    default => $args['task_status'],
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
        if (! empty($args['status'])) {
            $newStatus = $args['status'];
            $allowed = Project::VALID_TRANSITIONS[$project->status] ?? [];

            if (! in_array($newStatus, $allowed)) {
                $allowedStr = empty($allowed) ? 'tidak ada (status final)' : implode(', ', $allowed);

                return [
                    'status' => 'error',
                    'message' => "Tidak bisa mengubah status proyek dari **{$project->status}** ke **{$newStatus}**.\n"
                        ."Transisi yang diizinkan: **{$allowedStr}**.",
                ];
            }

            $project->update(['status' => $newStatus]);
            $messages[] = "Status proyek → **{$newStatus}**";
        }

        if (! empty($args['notes'])) {
            $project->update(['notes' => $args['notes']]);
        }

        $project->refresh();

        return [
            'status' => 'success',
            'message' => "Proyek **{$project->name}** diperbarui.\n"
                .implode("\n", $messages)."\n"
                ."Progress saat ini: **{$project->progress}%** | Status: **{$project->status}**",
            'data' => [
                'number' => $project->number,
                'progress' => $project->progress,
                'status' => $project->status,
            ],
        ];
    }

    public function addProjectExpense(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $expense = ProjectExpense::create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => $args['category'] ?? 'material',
            'description' => $args['description'],
            'amount' => $args['amount'],
            'date' => $args['date'] ?? today()->toDateString(),
            'reference' => $args['reference'] ?? null,
            'notes' => $args['notes'] ?? null,
        ]);

        // Update actual_cost proyek
        $project->recalculateActualCost();
        $project->refresh();

        $variance = $project->budgetVariance();
        $usedPct = $project->budgetUsedPercent();
        $budgetWarn = $variance < 0
            ? "\n⚠️ **OVER BUDGET** sebesar Rp ".number_format(abs($variance), 0, ',', '.')
            : ($usedPct >= 80 ? "\n⚠️ Budget sudah terpakai **{$usedPct}%**" : '');

        return [
            'status' => 'success',
            'message' => 'Pengeluaran **Rp '.number_format($args['amount'], 0, ',', '.')."** untuk proyek **{$project->name}** berhasil dicatat.\n"
                ."Kategori: **{$expense->category}** — {$expense->description}\n"
                .'Total realisasi: **Rp '.number_format($project->actual_cost, 0, ',', '.').'** / Budget: Rp '.number_format($project->budget, 0, ',', '.')
                .$budgetWarn,
            'data' => [
                'project' => $project->name,
                'amount' => $args['amount'],
                'actual_cost' => $project->actual_cost,
                'budget' => $project->budget,
                'variance' => $variance,
            ],
        ];
    }

    public function logTimesheet(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $hours = (float) $args['hours'];
        $hourlyRate = (float) ($args['hourly_rate'] ?? 0);
        $laborCost = $hours * $hourlyRate;

        Timesheet::create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'date' => $args['date'] ?? today()->toDateString(),
            'hours' => $hours,
            'description' => $args['description'],
            'hourly_rate' => $hourlyRate,
        ]);

        // Jika ada tarif, tambahkan ke project expense sebagai labor cost
        if ($laborCost > 0) {
            ProjectExpense::create([
                'project_id' => $project->id,
                'tenant_id' => $this->tenantId,
                'user_id' => $this->userId,
                'category' => 'labor',
                'description' => "Timesheet: {$args['description']} ({$hours} jam)",
                'amount' => $laborCost,
                'date' => $args['date'] ?? today()->toDateString(),
            ]);
            $project->recalculateActualCost();
        }

        // Total jam proyek
        $totalHours = Timesheet::where('project_id', $project->id)->sum('hours');

        $costMsg = $laborCost > 0
            ? "\nBiaya tenaga kerja: **Rp ".number_format($laborCost, 0, ',', '.').'**'
            : '';

        return [
            'status' => 'success',
            'message' => "Timesheet **{$hours} jam** untuk proyek **{$project->name}** berhasil dicatat.\n"
                ."Pekerjaan: {$args['description']}"
                .$costMsg."\n"
                ."Total jam proyek: **{$totalHours} jam**",
            'data' => [
                'project' => $project->name,
                'hours' => $hours,
                'total_hours' => $totalHours,
                'labor_cost' => $laborCost,
            ],
        ];
    }

    public function getProjectSummary(array $args): array
    {
        $query = Project::where('tenant_id', $this->tenantId)
            ->with(['customer', 'tasks']);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['period'])) {
            $query = $this->applyPeriod($query, $args['period']);
        }

        $projects = $query->latest()->get();

        if ($projects->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada proyek yang ditemukan.'];
        }

        $totalBudget = $projects->sum('budget');
        $totalActualCost = $projects->sum('actual_cost');
        $byStatus = $projects->groupBy('status')->map->count();

        $list = $projects->map(fn ($p) => [
            'nomor' => $p->number,
            'nama' => $p->name,
            'client' => $p->customer?->name ?? '-',
            'status' => $p->status,
            'progress' => $p->progress.'%',
            'budget' => 'Rp '.number_format($p->budget, 0, ',', '.'),
            'realisasi' => 'Rp '.number_format($p->actual_cost, 0, ',', '.'),
            'deadline' => $p->end_date?->format('d M Y') ?? '-',
        ])->toArray();

        return [
            'status' => 'success',
            'data' => [
                'total_proyek' => $projects->count(),
                'per_status' => $byStatus->toArray(),
                'total_budget' => 'Rp '.number_format($totalBudget, 0, ',', '.'),
                'total_realisasi' => 'Rp '.number_format($totalActualCost, 0, ',', '.'),
                'projects' => $list,
            ],
        ];
    }

    public function addProjectTask(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $assignedTo = null;
        if (! empty($args['assigned_to'])) {
            $assignedUser = User::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['assigned_to']}%")
                ->first();
            $assignedTo = $assignedUser?->id;
        }

        $progressMethod = $args['progress_method'] ?? 'status';
        $targetVolume = (float) ($args['target_volume'] ?? 0);
        $volumeUnit = $args['volume_unit'] ?? null;

        // Auto-detect volume tracking if target_volume is provided
        if ($targetVolume > 0 && $progressMethod === 'status') {
            $progressMethod = 'volume';
        }

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenantId,
            'assigned_to' => $assignedTo,
            'name' => $args['task_name'],
            'budget' => $args['budget'] ?? 0,
            'weight' => $args['weight'] ?? 1,
            'due_date' => $args['due_date'] ?? null,
            'status' => 'todo',
            'progress_method' => $progressMethod,
            'target_volume' => $targetVolume,
            'volume_unit' => $volumeUnit,
        ]);

        $assignMsg = $assignedTo ? " — ditugaskan ke **{$args['assigned_to']}**" : '';
        $dueMsg = ! empty($args['due_date'])
            ? ', deadline: **'.Carbon::parse($args['due_date'])->format('d M Y').'**'
            : '';
        $volMsg = $task->isVolumeTracked()
            ? "\n📐 Tracking volume: **{$targetVolume} {$volumeUnit}** (progress otomatis dari volume fisik)"
            : '';

        return [
            'status' => 'success',
            'message' => "Task **{$task->name}** berhasil ditambahkan ke proyek **{$project->name}**{$assignMsg}{$dueMsg}.{$volMsg}",
            'data' => [
                'task' => $task->name,
                'project' => $project->name,
                'progress_method' => $progressMethod,
                'target_volume' => $targetVolume,
                'volume_unit' => $volumeUnit,
            ],
        ];
    }

    // ─── RAB Executors ───────────────────────────────────────────

    public function addRabItem(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $type = strtolower($args['type'] ?? 'item');
        $type = in_array($type, ['group', 'grup', 'header']) ? 'group' : 'item';

        // Find parent group if specified
        $parentId = null;
        if (! empty($args['group_name'])) {
            $parent = RabItem::where('project_id', $project->id)
                ->where('type', 'group')
                ->where('name', 'like', "%{$args['group_name']}%")
                ->first();
            $parentId = $parent?->id;
        }

        $maxSort = RabItem::where('project_id', $project->id)
            ->where('parent_id', $parentId)
            ->max('sort_order') ?? 0;

        $item = RabItem::create([
            'project_id' => $project->id,
            'tenant_id' => $this->tenantId,
            'parent_id' => $parentId,
            'code' => $args['code'] ?? null,
            'name' => $args['name'],
            'type' => $type,
            'category' => $args['category'] ?? null,
            'volume' => (float) ($args['volume'] ?? 0),
            'unit' => $args['unit'] ?? null,
            'unit_price' => (float) ($args['unit_price'] ?? 0),
            'coefficient' => (float) ($args['coefficient'] ?? 1),
            'sort_order' => $maxSort + 1,
            'notes' => $args['notes'] ?? null,
        ]);

        RabItem::recalculateProject($project->id);

        $subtotalMsg = $type === 'item' && $item->subtotal > 0
            ? ' — subtotal: **Rp '.number_format($item->subtotal, 0, ',', '.').'**'
            : '';
        $parentMsg = $parentId ? " (di dalam grup \"{$parent->name}\")" : '';

        return [
            'status' => 'success',
            'message' => ($type === 'group' ? 'Grup' : 'Item')." RAB **{$item->name}** berhasil ditambahkan ke proyek **{$project->name}**{$parentMsg}{$subtotalMsg}.",
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $type,
                'volume' => $item->volume,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'total_rab' => RabItem::where('project_id', $project->id)->whereNull('parent_id')->sum('subtotal'),
            ],
        ];
    }

    public function getRab(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $items = RabItem::where('project_id', $project->id)->orderBy('sort_order')->get();

        if ($items->isEmpty()) {
            return [
                'status' => 'empty',
                'message' => "Proyek **{$project->name}** belum memiliki RAB. Gunakan `add_rab_item` untuk menambahkan item.",
            ];
        }

        $totalRab = $items->whereNull('parent_id')->sum('subtotal');
        $totalActual = $items->where('type', 'item')->sum('actual_cost');

        $rows = [];
        foreach ($items as $item) {
            $row = [
                'kode' => $item->code,
                'uraian' => $item->name,
                'tipe' => $item->type,
            ];
            if ($item->type === 'item') {
                $row['volume'] = $item->volume.' '.$item->unit;
                $row['harga_satuan'] = 'Rp '.number_format($item->unit_price, 0, ',', '.');
                $row['koefisien'] = $item->coefficient != 1 ? $item->coefficient : null;
                $row['subtotal'] = 'Rp '.number_format($item->subtotal, 0, ',', '.');
                $row['realisasi'] = $item->actual_cost > 0 ? 'Rp '.number_format($item->actual_cost, 0, ',', '.') : null;
                $row['kategori'] = $item->category;
            } else {
                $row['subtotal'] = 'Rp '.number_format($item->subtotal, 0, ',', '.');
                $row['realisasi'] = $item->actual_cost > 0 ? 'Rp '.number_format($item->actual_cost, 0, ',', '.') : null;
            }
            $rows[] = $row;
        }

        $variance = $totalRab - $totalActual;

        return [
            'status' => 'success',
            'message' => "RAB proyek **{$project->name}**",
            'data' => [
                'project' => $project->name,
                'total_rab' => 'Rp '.number_format($totalRab, 0, ',', '.'),
                'total_realisasi' => 'Rp '.number_format($totalActual, 0, ',', '.'),
                'selisih' => ($variance >= 0 ? '' : '-').'Rp '.number_format(abs($variance), 0, ',', '.'),
                'status_budget' => $variance >= 0 ? 'UNDER BUDGET' : 'OVER BUDGET',
                'jumlah_item' => $items->where('type', 'item')->count(),
                'items' => $rows,
                'url' => "/projects/{$project->id}/rab",
            ],
        ];
    }

    public function recordRabActual(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $item = RabItem::where('project_id', $project->id)
            ->where('type', 'item')
            ->where('name', 'like', "%{$args['item_name']}%")
            ->first();

        if (! $item) {
            return ['status' => 'not_found', 'message' => "Item RAB \"{$args['item_name']}\" tidak ditemukan di proyek {$project->name}."];
        }

        $item->update([
            'actual_cost' => isset($args['actual_cost']) ? (float) $args['actual_cost'] : $item->actual_cost,
            'actual_volume' => isset($args['actual_volume']) ? (float) $args['actual_volume'] : $item->actual_volume,
        ]);

        RabItem::recalculateProject($project->id);

        $overBudget = $item->actual_cost > $item->subtotal;

        return [
            'status' => 'success',
            'message' => "Realisasi item **{$item->name}** berhasil dicatat."
                ."\n- RAB: Rp ".number_format($item->subtotal, 0, ',', '.')
                ."\n- Realisasi: Rp ".number_format($item->actual_cost, 0, ',', '.')
                ."\n- ".($overBudget ? '⚠️ **OVER BUDGET** ' : '✅ Under budget ')
                .'('.$item->realizationPercent().'%)'
                .($item->actual_volume > 0 ? "\n- Volume: {$item->actual_volume}/{$item->volume} {$item->unit} ({$item->volumeProgress()}%)" : ''),
            'data' => [
                'item' => $item->name,
                'rab' => $item->subtotal,
                'actual' => $item->actual_cost,
                'realization_pct' => $item->realizationPercent(),
                'over_budget' => $overBudget,
            ],
        ];
    }

    // ─── Volume Progress Executors ───────────────────────────────

    public function recordVolumeProgress(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $task = $project->tasks()
            ->where('name', 'like', "%{$args['task_name']}%")
            ->first();

        if (! $task) {
            return ['status' => 'not_found', 'message' => "Task \"{$args['task_name']}\" tidak ditemukan di proyek {$project->name}."];
        }

        if (! $task->isVolumeTracked()) {
            return [
                'status' => 'error',
                'message' => "Task **{$task->name}** tidak menggunakan tracking volume. "
                    ."Target volume: {$task->target_volume} {$task->volume_unit}. "
                    ."Ubah progress_method ke 'volume' dan set target_volume terlebih dahulu.",
            ];
        }

        $volume = (float) $args['volume'];
        $newActual = (float) $task->actual_volume + $volume;

        TaskVolumeLog::create([
            'project_task_id' => $task->id,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'volume' => $volume,
            'cumulative' => $newActual,
            'date' => today(),
            'description' => $args['description'] ?? null,
        ]);

        $task->update(['actual_volume' => $newActual]);
        $task->syncStatusFromVolume();
        $project->recalculateProgress();
        $project->refresh();

        $pct = $task->volumeProgress();
        $remaining = $task->remainingVolume();
        $fmt = fn ($v) => number_format($v, $v == (int) $v ? 0 : 1, ',', '.');

        return [
            'status' => 'success',
            'message' => "Volume dicatat untuk **{$task->name}**:"
                ."\n- Ditambahkan: **+{$fmt($volume)} {$task->volume_unit}**"
                ."\n- Total: **{$fmt($newActual)} / {$fmt($task->target_volume)} {$task->volume_unit}** ({$pct}%)"
                .($remaining > 0 ? "\n- Sisa: **{$fmt($remaining)} {$task->volume_unit}**" : "\n- ✅ **Target tercapai!**")
                ."\n- Progress proyek: **{$project->progress}%**",
            'data' => [
                'task' => $task->name,
                'actual' => $newActual,
                'target' => $task->target_volume,
                'unit' => $task->volume_unit,
                'pct' => $pct,
                'remaining' => $remaining,
                'project_progress' => $project->progress,
            ],
        ];
    }

    public function getVolumeProgress(array $args): array
    {
        $project = $this->findProject($args['project_name']);
        if (! $project) {
            return ['status' => 'not_found', 'message' => "Proyek \"{$args['project_name']}\" tidak ditemukan."];
        }

        $tasks = $project->tasks()->whereNotIn('status', ['cancelled'])->get();
        $volumeTasks = $tasks->filter(fn ($t) => $t->isVolumeTracked());

        if ($volumeTasks->isEmpty()) {
            return [
                'status' => 'empty',
                'message' => "Proyek **{$project->name}** belum memiliki task dengan tracking volume. "
                    ."Tambahkan task dengan progress_method='volume' dan target_volume.",
            ];
        }

        $fmt = fn ($v) => number_format($v, $v == (int) $v ? 0 : 1, ',', '.');

        $rows = $volumeTasks->map(fn ($t) => [
            'task' => $t->name,
            'target' => "{$fmt($t->target_volume)} {$t->volume_unit}",
            'actual' => "{$fmt($t->actual_volume)} {$t->volume_unit}",
            'progress' => "{$t->volumeProgress()}%",
            'remaining' => "{$fmt($t->remainingVolume())} {$t->volume_unit}",
            'status' => $t->status,
        ]);

        return [
            'status' => 'success',
            'message' => "Progress volume fisik proyek **{$project->name}** ({$project->progress}% overall)",
            'data' => [
                'project' => $project->name,
                'project_progress' => $project->progress,
                'volume_tasks' => $rows->toArray(),
                'total_tasks' => $tasks->count(),
                'volume_tracked' => $volumeTasks->count(),
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function findProject(string $nameOrNumber): ?Project
    {
        return Project::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('name', 'like', "%{$nameOrNumber}%")
                ->orWhere('number', $nameOrNumber)
                ->orWhere('number', 'like', "%{$nameOrNumber}%"))
            ->with(['tasks', 'expenses', 'timesheets'])
            ->first();
    }

    protected function applyPeriod($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => $query,
        };
    }
}
