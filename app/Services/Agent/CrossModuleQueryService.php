<?php

namespace App\Services\Agent;

use App\Models\Attendance;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Project;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CrossModuleQueryService — Task 13
 *
 * Mengeksekusi query paralel ke beberapa modul ERP sekaligus dan
 * mengkorelasikan hasilnya. Jika modul tidak aktif, mengembalikan
 * hasil parsial dari modul yang aktif beserta daftar modul tidak tersedia.
 *
 * Kombinasi yang didukung:
 *  1. Akuntansi + Inventory
 *  2. Akuntansi + HRM
 *  3. Penjualan + CRM + Inventory
 *  4. HRM + Payroll + Absensi
 *  5. Project + Keuangan
 *
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
 */
class CrossModuleQueryService
{
    /** Batas waktu per query modul dalam detik */
    private const MODULE_TIMEOUT_SECONDS = 4.5;

    public function __construct(protected int $tenantId, protected array $activeModules = []) {}

    // =========================================================================
    // 1. Akuntansi + Inventory
    // =========================================================================

    /**
     * Query lintas modul: Akuntansi + Inventory.
     *
     * Mengkorelasikan data keuangan (pendapatan, pengeluaran) dengan kondisi
     * stok (stok kritis, nilai persediaan) untuk insight bisnis terintegrasi.
     *
     * @param  array  $args  ['period' => 'this_month']
     * @return array
     */
    public function queryAkuntansiInventory(array $args = []): array
    {
        $period            = $args['period'] ?? 'this_month';
        $unavailableModules = [];
        $data              = [];

        $startTime = microtime(true);

        // ── Modul Akuntansi ───────────────────────────────────────────────────
        if ($this->isModuleActive('accounting')) {
            $akuntansi = $this->runSafe('accounting', $startTime, function () use ($period) {
                $income  = $this->queryTransactions('income', $period);
                $expense = $this->queryTransactions('expense', $period);
                return [
                    'pendapatan'    => $income,
                    'pengeluaran'   => $expense,
                    'profit'        => $income - $expense,
                    'profit_status' => ($income - $expense) >= 0 ? 'SURPLUS' : 'DEFISIT',
                ];
            });

            if ($akuntansi !== null) {
                $data['akuntansi'] = $akuntansi;
            } else {
                $unavailableModules[] = 'accounting';
            }
        } else {
            $unavailableModules[] = 'accounting';
        }

        // ── Modul Inventory ───────────────────────────────────────────────────
        if ($this->isModuleActive('inventory')) {
            $inventory = $this->runSafe('inventory', $startTime, function () {
                $criticalCount = $this->queryCriticalStock();
                $totalValue    = $this->queryInventoryValue();
                return [
                    'stok_kritis'     => $criticalCount,
                    'nilai_persediaan' => $totalValue,
                ];
            });

            if ($inventory !== null) {
                $data['inventory'] = $inventory;
            } else {
                $unavailableModules[] = 'inventory';
            }
        } else {
            $unavailableModules[] = 'inventory';
        }

        // ── Korelasi ──────────────────────────────────────────────────────────
        $correlation = [];
        if (isset($data['akuntansi'], $data['inventory'])) {
            $profit = $data['akuntansi']['profit'];
            $nilai  = $data['inventory']['nilai_persediaan'];
            if ($nilai > 0) {
                $correlation['rasio_profit_ke_persediaan'] = round($profit / $nilai * 100, 2) . '%';
            }
            if ($data['inventory']['stok_kritis'] > 0) {
                $correlation['peringatan'] = "Ada {$data['inventory']['stok_kritis']} produk dengan stok kritis yang dapat mempengaruhi pendapatan.";
            }
        }

        return $this->buildResponse($data, $unavailableModules, $correlation);
    }

    // =========================================================================
    // 2. Akuntansi + HRM
    // =========================================================================

    /**
     * Query lintas modul: Akuntansi + HRM.
     *
     * Mengkorelasikan data keuangan dengan data SDM untuk analisis
     * biaya tenaga kerja dan produktivitas karyawan.
     *
     * @param  array  $args  ['period' => 'this_month']
     * @return array
     */
    public function queryAkuntansiHrm(array $args = []): array
    {
        $period            = $args['period'] ?? 'this_month';
        $unavailableModules = [];
        $data              = [];

        $startTime = microtime(true);

        // ── Modul Akuntansi ───────────────────────────────────────────────────
        if ($this->isModuleActive('accounting')) {
            $akuntansi = $this->runSafe('accounting', $startTime, function () use ($period) {
                $income  = $this->queryTransactions('income', $period);
                $expense = $this->queryTransactions('expense', $period);
                return [
                    'pendapatan'  => $income,
                    'pengeluaran' => $expense,
                    'profit'      => $income - $expense,
                ];
            });

            if ($akuntansi !== null) {
                $data['akuntansi'] = $akuntansi;
            } else {
                $unavailableModules[] = 'accounting';
            }
        } else {
            $unavailableModules[] = 'accounting';
        }

        // ── Modul HRM ─────────────────────────────────────────────────────────
        if ($this->isModuleActive('hrm')) {
            $hrm = $this->runSafe('hrm', $startTime, function () {
                $activeCount = (int) Employee::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->count();

                $departments = Employee::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->select('department', DB::raw('count(*) as total'))
                    ->groupBy('department')
                    ->pluck('total', 'department')
                    ->toArray();

                return [
                    'karyawan_aktif' => $activeCount,
                    'per_departemen' => $departments,
                ];
            });

            if ($hrm !== null) {
                $data['hrm'] = $hrm;
            } else {
                $unavailableModules[] = 'hrm';
            }
        } else {
            $unavailableModules[] = 'hrm';
        }

        // ── Korelasi ──────────────────────────────────────────────────────────
        $correlation = [];
        if (isset($data['akuntansi'], $data['hrm']) && $data['hrm']['karyawan_aktif'] > 0) {
            $revenuePerEmployee = $data['akuntansi']['pendapatan'] / $data['hrm']['karyawan_aktif'];
            $correlation['pendapatan_per_karyawan'] = 'Rp ' . number_format($revenuePerEmployee, 0, ',', '.');
        }

        return $this->buildResponse($data, $unavailableModules, $correlation);
    }

    // =========================================================================
    // 3. Penjualan + CRM + Inventory
    // =========================================================================

    /**
     * Query lintas modul: Penjualan + CRM + Inventory.
     *
     * Mengkorelasikan pipeline penjualan, data CRM, dan ketersediaan stok
     * untuk analisis peluang bisnis dan kesiapan fulfillment.
     *
     * @param  array  $args  ['period' => 'this_month']
     * @return array
     */
    public function queryPenjualanCrmInventory(array $args = []): array
    {
        $period            = $args['period'] ?? 'this_month';
        $unavailableModules = [];
        $data              = [];

        $startTime = microtime(true);

        // ── Modul Penjualan ───────────────────────────────────────────────────
        if ($this->isModuleActive('sales')) {
            $penjualan = $this->runSafe('sales', $startTime, function () use ($period) {
                $orders = SalesOrder::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('status', '!=', 'cancelled')
                    ->when($period === 'this_month', fn($q) => $q
                        ->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year))
                    ->selectRaw('status, count(*) as total, sum(total) as nilai')
                    ->groupBy('status')
                    ->get();

                $totalRevenue = $orders->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered', 'completed'])->sum('nilai');

                return [
                    'total_order'   => $orders->sum('total'),
                    'total_revenue' => $totalRevenue,
                    'per_status'    => $orders->pluck('total', 'status')->toArray(),
                ];
            });

            if ($penjualan !== null) {
                $data['penjualan'] = $penjualan;
            } else {
                $unavailableModules[] = 'sales';
            }
        } else {
            $unavailableModules[] = 'sales';
        }

        // ── Modul CRM ─────────────────────────────────────────────────────────
        if ($this->isModuleActive('crm')) {
            $crm = $this->runSafe('crm', $startTime, function () {
                $leads = CrmLead::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->whereNotIn('stage', ['won', 'lost'])
                    ->selectRaw('stage, count(*) as total, sum(estimated_value) as nilai')
                    ->groupBy('stage')
                    ->get();

                $totalPipeline = $leads->sum('nilai');
                $weightedValue = CrmLead::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->whereNotIn('stage', ['won', 'lost'])
                    ->selectRaw('sum(estimated_value * probability / 100) as weighted')
                    ->value('weighted') ?? 0;

                return [
                    'total_leads'      => $leads->sum('total'),
                    'nilai_pipeline'   => $totalPipeline,
                    'nilai_tertimbang' => $weightedValue,
                    'per_stage'        => $leads->pluck('total', 'stage')->toArray(),
                ];
            });

            if ($crm !== null) {
                $data['crm'] = $crm;
            } else {
                $unavailableModules[] = 'crm';
            }
        } else {
            $unavailableModules[] = 'crm';
        }

        // ── Modul Inventory ───────────────────────────────────────────────────
        if ($this->isModuleActive('inventory')) {
            $inventory = $this->runSafe('inventory', $startTime, function () {
                return [
                    'stok_kritis'      => $this->queryCriticalStock(),
                    'nilai_persediaan' => $this->queryInventoryValue(),
                ];
            });

            if ($inventory !== null) {
                $data['inventory'] = $inventory;
            } else {
                $unavailableModules[] = 'inventory';
            }
        } else {
            $unavailableModules[] = 'inventory';
        }

        // ── Korelasi ──────────────────────────────────────────────────────────
        $correlation = [];
        if (isset($data['crm'], $data['penjualan'])) {
            $pipeline = $data['crm']['nilai_pipeline'];
            $revenue  = $data['penjualan']['total_revenue'];
            if ($pipeline > 0) {
                $correlation['potensi_konversi'] = 'Rp ' . number_format($pipeline, 0, ',', '.');
            }
        }
        if (isset($data['inventory']) && $data['inventory']['stok_kritis'] > 0) {
            $correlation['peringatan_stok'] = "Ada {$data['inventory']['stok_kritis']} produk dengan stok kritis yang dapat menghambat fulfillment order.";
        }

        return $this->buildResponse($data, $unavailableModules, $correlation);
    }

    // =========================================================================
    // 4. HRM + Payroll + Absensi
    // =========================================================================

    /**
     * Query lintas modul: HRM + Payroll + Absensi.
     *
     * Mengkorelasikan data karyawan, penggajian, dan kehadiran untuk
     * analisis produktivitas dan biaya SDM secara menyeluruh.
     *
     * @param  array  $args  ['period' => 'this_month']
     * @return array
     */
    public function queryHrmPayrollAbsensi(array $args = []): array
    {
        $period            = $args['period'] ?? now()->format('Y-m');
        $unavailableModules = [];
        $data              = [];

        $startTime = microtime(true);

        // ── Modul HRM ─────────────────────────────────────────────────────────
        if ($this->isModuleActive('hrm')) {
            $hrm = $this->runSafe('hrm', $startTime, function () {
                $activeCount = (int) Employee::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->count();

                // Karyawan yang akan resign dalam 30 hari (berdasarkan resign_date)
                $contractExpiring = (int) Employee::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('status', 'active')
                    ->whereNotNull('resign_date')
                    ->whereBetween('resign_date', [now(), now()->addDays(30)])
                    ->count();

                return [
                    'karyawan_aktif'         => $activeCount,
                    'kontrak_akan_berakhir'  => $contractExpiring,
                ];
            });

            if ($hrm !== null) {
                $data['hrm'] = $hrm;
            } else {
                $unavailableModules[] = 'hrm';
            }
        } else {
            $unavailableModules[] = 'hrm';
        }

        // ── Modul Payroll ─────────────────────────────────────────────────────
        if ($this->isModuleActive('payroll')) {
            $payroll = $this->runSafe('payroll', $startTime, function () use ($period) {
                $run = PayrollRun::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->where('period', $period)
                    ->first(['status', 'total_gross', 'total_deductions', 'total_net']);

                if (!$run) {
                    return [
                        'status'           => 'belum_diproses',
                        'total_gaji_kotor' => null,
                        'total_potongan'   => null,
                        'total_gaji_bersih'=> null,
                    ];
                }

                return [
                    'status'           => $run->status,
                    'total_gaji_kotor' => $run->total_gross,
                    'total_potongan'   => $run->total_deductions,
                    'total_gaji_bersih'=> $run->total_net,
                ];
            });

            if ($payroll !== null) {
                $data['payroll'] = $payroll;
            } else {
                $unavailableModules[] = 'payroll';
            }
        } else {
            $unavailableModules[] = 'payroll';
        }

        // ── Modul Absensi ─────────────────────────────────────────────────────
        if ($this->isModuleActive('hrm')) {
            // Absensi adalah bagian dari modul HRM
            $absensi = $this->runSafe('attendance', $startTime, function () use ($period) {
                [$year, $month] = array_pad(explode('-', $period), 2, now()->month);

                $summary = Attendance::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $totalHadir  = $summary['present'] ?? 0;
                $totalAbsen  = $summary['absent'] ?? 0;
                $totalIzin   = ($summary['leave'] ?? 0) + ($summary['late'] ?? 0);
                $totalRecord = array_sum($summary);

                return [
                    'total_hadir'  => $totalHadir,
                    'total_absen'  => $totalAbsen,
                    'total_izin'   => $totalIzin,
                    'total_record' => $totalRecord,
                    'per_status'   => $summary,
                ];
            });

            if ($absensi !== null) {
                $data['absensi'] = $absensi;
            } else {
                $unavailableModules[] = 'attendance';
            }
        } else {
            $unavailableModules[] = 'attendance';
        }

        // ── Korelasi ──────────────────────────────────────────────────────────
        $correlation = [];
        if (isset($data['hrm'], $data['payroll']) && $data['hrm']['karyawan_aktif'] > 0 && isset($data['payroll']['total_gaji_bersih']) && $data['payroll']['total_gaji_bersih'] !== null) {
            $gajiPerKaryawan = $data['payroll']['total_gaji_bersih'] / $data['hrm']['karyawan_aktif'];
            $correlation['rata_gaji_per_karyawan'] = 'Rp ' . number_format($gajiPerKaryawan, 0, ',', '.');
        }
        if (isset($data['hrm']) && $data['hrm']['kontrak_akan_berakhir'] > 0) {
            $correlation['peringatan_kontrak'] = "{$data['hrm']['kontrak_akan_berakhir']} karyawan memiliki kontrak yang akan berakhir dalam 30 hari.";
        }
        if (isset($data['absensi']) && $data['absensi']['total_record'] > 0) {
            $tingkatKehadiran = round($data['absensi']['total_hadir'] / $data['absensi']['total_record'] * 100, 1);
            $correlation['tingkat_kehadiran'] = $tingkatKehadiran . '%';
        }

        return $this->buildResponse($data, $unavailableModules, $correlation);
    }

    // =========================================================================
    // 5. Project + Keuangan
    // =========================================================================

    /**
     * Query lintas modul: Project + Keuangan.
     *
     * Mengkorelasikan data proyek (progress, budget) dengan data keuangan
     * untuk analisis profitabilitas proyek dan kesehatan arus kas.
     *
     * @param  array  $args  ['period' => 'this_month', 'status' => null]
     * @return array
     */
    public function queryProjectKeuangan(array $args = []): array
    {
        $period            = $args['period'] ?? 'this_month';
        $projectStatus     = $args['status'] ?? null;
        $unavailableModules = [];
        $data              = [];

        $startTime = microtime(true);

        // ── Modul Project ─────────────────────────────────────────────────────
        if ($this->isModuleActive('project')) {
            $project = $this->runSafe('project', $startTime, function () use ($projectStatus) {
                $query = Project::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId);

                if ($projectStatus) {
                    $query->where('status', $projectStatus);
                }

                $projects = $query->get(['id', 'name', 'status', 'budget', 'actual_cost', 'progress']);

                $totalBudget     = $projects->sum('budget');
                $totalActualCost = $projects->sum('actual_cost');
                $byStatus        = $projects->groupBy('status')->map->count();

                return [
                    'total_proyek'    => $projects->count(),
                    'total_budget'    => (float) $totalBudget,
                    'total_realisasi' => (float) $totalActualCost,
                    'variance'        => (float) ($totalBudget - $totalActualCost),
                    'per_status'      => $byStatus->toArray(),
                    'rata_progress'   => $projects->isNotEmpty()
                        ? round($projects->avg('progress'), 1)
                        : 0,
                ];
            });

            if ($project !== null) {
                $data['project'] = $project;
            } else {
                $unavailableModules[] = 'project';
            }
        } else {
            $unavailableModules[] = 'project';
        }

        // ── Modul Keuangan ────────────────────────────────────────────────────
        if ($this->isModuleActive('accounting')) {
            $keuangan = $this->runSafe('accounting', $startTime, function () use ($period) {
                $income  = $this->queryTransactions('income', $period);
                $expense = $this->queryTransactions('expense', $period);

                // Piutang jatuh tempo
                $overdueAr = (float) Invoice::withoutGlobalScopes()
                    ->where('tenant_id', $this->tenantId)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->where('due_date', '<', now()->startOfDay())
                    ->sum('remaining_amount');

                return [
                    'pendapatan'  => $income,
                    'pengeluaran' => $expense,
                    'profit'      => $income - $expense,
                    'piutang_jatuh_tempo' => $overdueAr,
                ];
            });

            if ($keuangan !== null) {
                $data['keuangan'] = $keuangan;
            } else {
                $unavailableModules[] = 'accounting';
            }
        } else {
            $unavailableModules[] = 'accounting';
        }

        // ── Korelasi ──────────────────────────────────────────────────────────
        $correlation = [];
        if (isset($data['project'])) {
            $variance = $data['project']['variance'];
            if ($variance < 0) {
                $correlation['peringatan_budget'] = 'Total realisasi biaya proyek melebihi budget sebesar Rp ' . number_format(abs($variance), 0, ',', '.') . '.';
            } elseif ($variance > 0) {
                $correlation['sisa_budget'] = 'Sisa budget proyek: Rp ' . number_format($variance, 0, ',', '.') . '.';
            }
        }
        if (isset($data['keuangan']) && $data['keuangan']['piutang_jatuh_tempo'] > 0) {
            $correlation['piutang_jatuh_tempo'] = 'Piutang jatuh tempo: Rp ' . number_format($data['keuangan']['piutang_jatuh_tempo'], 0, ',', '.') . '.';
        }

        return $this->buildResponse($data, $unavailableModules, $correlation);
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * Cek apakah modul aktif untuk tenant ini.
     */
    private function isModuleActive(string $module): bool
    {
        if (empty($this->activeModules)) {
            return true; // Jika tidak ada filter, anggap semua aktif
        }
        return in_array(strtolower($module), array_map('strtolower', $this->activeModules), true);
    }

    /**
     * Jalankan callable dengan timeout guard.
     * Jika timeout atau error, kembalikan null (modul tidak tersedia).
     */
    private function runSafe(string $moduleName, float $startTime, callable $fn): ?array
    {
        $elapsed = microtime(true) - $startTime;

        if ($elapsed >= self::MODULE_TIMEOUT_SECONDS) {
            Log::warning("CrossModuleQueryService: skip modul '{$moduleName}' karena mendekati timeout", [
                'tenant_id'  => $this->tenantId,
                'elapsed_ms' => round($elapsed * 1000),
            ]);
            return null;
        }

        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning("CrossModuleQueryService: query modul '{$moduleName}' gagal", [
                'tenant_id' => $this->tenantId,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Bangun response standar dengan data, modul tidak tersedia, dan korelasi.
     * Tidak pernah mengembalikan error total — selalu partial result.
     */
    private function buildResponse(array $data, array $unavailableModules, array $correlation = []): array
    {
        $response = [
            'status'             => 'success',
            'data'               => $data,
            'correlation'        => $correlation,
            'unavailable_modules'=> array_values(array_unique($unavailableModules)),
        ];

        if (!empty($unavailableModules)) {
            $response['partial'] = true;
            $response['message'] = 'Hasil parsial. Modul tidak tersedia: ' . implode(', ', array_unique($unavailableModules)) . '.';
        }

        return $response;
    }

    /**
     * Query total transaksi berdasarkan tipe dan periode.
     */
    private function queryTransactions(string $type, string $period): float
    {
        $query = Transaction::withoutGlobalScopes()
            ->where('tenant_id', $this->tenantId)
            ->where('type', $type);

        return (float) match (strtolower($period)) {
            'today'      => $query->whereDate('date', today())->sum('amount'),
            'this_week'  => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount'),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'last_month' => $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('amount'),
            'this_year'  => $query->whereYear('date', now()->year)->sum('amount'),
            default      => strlen($period) === 7
                ? $query->whereYear('date', substr($period, 0, 4))->whereMonth('date', substr($period, 5, 2))->sum('amount')
                : $query->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
        };
    }

    /**
     * Query jumlah produk dengan stok kritis (di bawah stock_min).
     */
    private function queryCriticalStock(): int
    {
        return (int) DB::table('products')
            ->leftJoin('product_stocks', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.tenant_id', $this->tenantId)
            ->where('products.is_active', true)
            ->whereNotNull('products.stock_min')
            ->where('products.stock_min', '>', 0)
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.stock_min')
            ->havingRaw('COALESCE(SUM(product_stocks.quantity), 0) < products.stock_min')
            ->get(['products.id'])
            ->count();
    }

    /**
     * Query total nilai persediaan (stok × harga beli).
     */
    private function queryInventoryValue(): float
    {
        return (float) DB::table('products')
            ->join('product_stocks', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.tenant_id', $this->tenantId)
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->selectRaw('SUM(product_stocks.quantity * products.price_buy) as total_value')
            ->value('total_value') ?? 0.0;
    }
}
