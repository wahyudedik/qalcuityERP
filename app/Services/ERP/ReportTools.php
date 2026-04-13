<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payable;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Project;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Transaction;
use App\Models\WorkOrder;
use Carbon\Carbon;

class ReportTools
{
    public function __construct(protected int $tenantId, protected int $userId)
    {
    }

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_profit_loss',
                'description' => 'Laporan laba rugi detail per periode. Gunakan untuk: '
                    . '"laporan laba rugi bulan ini", "P&L Januari", '
                    . '"untung rugi minggu ini", "analisis laba bulan lalu", '
                    . '"berapa profit bersih bulan ini?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode: today, this_week, this_month, last_month, this_year (default: this_month)'],
                        'start_date' => ['type' => 'string', 'description' => 'Tanggal mulai custom YYYY-MM-DD (opsional, override period)'],
                        'end_date' => ['type' => 'string', 'description' => 'Tanggal akhir custom YYYY-MM-DD (opsional, override period)'],
                    ],
                ],
            ],
            [
                'name' => 'get_sales_trend',
                'description' => 'Analisis tren penjualan per hari/minggu/bulan. Gunakan untuk: '
                    . '"tren penjualan 7 hari terakhir", "grafik omzet bulan ini", '
                    . '"penjualan per hari minggu ini", "produk terlaris bulan ini", '
                    . '"perbandingan penjualan bulan ini vs bulan lalu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode: last_7_days, last_30_days, this_month, last_month, this_year (default: last_30_days)'],
                        'group_by' => ['type' => 'string', 'description' => 'Kelompokkan per: day, week, month (default: day)'],
                        'top_n' => ['type' => 'integer', 'description' => 'Tampilkan N produk terlaris (default: 5)'],
                    ],
                ],
            ],
            [
                'name' => 'get_expense_breakdown',
                'description' => 'Rincian pengeluaran per kategori. Gunakan untuk: '
                    . '"pengeluaran terbesar bulan ini", "breakdown biaya operasional", '
                    . '"kategori pengeluaran apa yang paling besar?", '
                    . '"analisis biaya bulan ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode: today, this_week, this_month, last_month, this_year (default: this_month)'],
                        'start_date' => ['type' => 'string', 'description' => 'Tanggal mulai custom YYYY-MM-DD (opsional)'],
                        'end_date' => ['type' => 'string', 'description' => 'Tanggal akhir custom YYYY-MM-DD (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'get_receivables_report',
                'description' => 'Laporan piutang dan hutang lengkap. Gunakan untuk: '
                    . '"laporan piutang bulan ini", "total hutang ke supplier", '
                    . '"aging piutang detail", "cashflow dari piutang", '
                    . '"berapa total yang belum dibayar customer?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => ['type' => 'string', 'description' => 'receivable (piutang), payable (hutang), atau all (default: all)'],
                        'period' => ['type' => 'string', 'description' => 'Periode: this_month, last_month, this_year (default: this_month)'],
                    ],
                ],
            ],
            [
                'name' => 'get_inventory_valuation',
                'description' => 'Valuasi inventori dan analisis pergerakan stok. Gunakan untuk: '
                    . '"nilai total inventori", "valuasi stok gudang", '
                    . '"produk mana yang nilainya paling besar?", '
                    . '"analisis perputaran stok", "dead stock ada berapa?".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'warehouse_name' => ['type' => 'string', 'description' => 'Filter per gudang (opsional)'],
                        'category' => ['type' => 'string', 'description' => 'Filter per kategori produk (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'get_hrm_report',
                'description' => 'Laporan SDM: kehadiran, produktivitas, dan ringkasan karyawan. Gunakan untuk: '
                    . '"laporan absensi bulan ini", "rekap kehadiran karyawan", '
                    . '"siapa yang paling sering absen?", "produktivitas tim bulan ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode: today, this_week, this_month, last_month (default: this_month)'],
                        'start_date' => ['type' => 'string', 'description' => 'Tanggal mulai custom YYYY-MM-DD (opsional)'],
                        'end_date' => ['type' => 'string', 'description' => 'Tanggal akhir custom YYYY-MM-DD (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'get_project_financial_report',
                'description' => 'Laporan keuangan proyek: realisasi vs budget, profitabilitas. Gunakan untuk: '
                    . '"laporan keuangan semua proyek", "proyek mana yang over budget?", '
                    . '"profitabilitas proyek bulan ini", "realisasi anggaran proyek".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status proyek: active, completed, all (default: all)'],
                        'period' => ['type' => 'string', 'description' => 'Filter periode: this_month, this_year (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'export_report_pdf',
                'description' => 'Generate link download laporan PDF. Gunakan ketika user ingin download/export laporan ke PDF. '
                    . 'Contoh: "download laporan penjualan bulan ini", "export PDF inventori", '
                    . '"cetak laporan keuangan minggu ini", "unduh laporan kehadiran bulan lalu", '
                    . '"export laporan piutang ke PDF", "download laporan laba rugi".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'report_type' => [
                            'type' => 'string',
                            'description' => 'Jenis laporan: sales (penjualan), finance (keuangan), inventory (inventori), hrm (kehadiran), receivables (piutang), profit_loss (laba rugi)',
                        ],
                        'start_date' => ['type' => 'string', 'description' => 'Tanggal mulai YYYY-MM-DD (opsional, default: awal bulan ini)'],
                        'end_date' => ['type' => 'string', 'description' => 'Tanggal akhir YYYY-MM-DD (opsional, default: hari ini)'],
                        'period' => ['type' => 'string', 'description' => 'Shortcut periode: today, this_week, this_month, last_month, this_year (opsional, override start/end_date)'],
                    ],
                    'required' => ['report_type'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function getProfitLoss(array $args): array
    {
        [$start, $end] = $this->resolveDateRange($args);

        $income = Transaction::where('tenant_id', $this->tenantId)
            ->where('type', 'income')
            ->whereBetween('date', [$start, $end])
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $expenseRows = Transaction::where('transactions.tenant_id', $this->tenantId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$start, $end])
            ->leftJoin('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Tidak Berkategori") as category, SUM(transactions.amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpense = $expenseRows->sum('total');

        // HPP dari penjualan (price_buy * qty terjual)
        $hpp = SalesOrderItem::whereHas(
            'salesOrder',
            fn($q) =>
            $q->where('tenant_id', $this->tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('date', [$start, $end])
        )->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(sales_order_items.quantity * products.price_buy) as total')
            ->value('total') ?? 0;

        $grossProfit = $income - $hpp;
        $netProfit = $income - $totalExpense - $hpp;
        $grossMargin = $income > 0 ? round(($grossProfit / $income) * 100, 1) : 0;
        $netMargin = $income > 0 ? round(($netProfit / $income) * 100, 1) : 0;

        return [
            'status' => 'success',
            'data' => [
                'periode' => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
                'pendapatan' => 'Rp ' . number_format($income, 0, ',', '.'),
                'hpp' => 'Rp ' . number_format($hpp, 0, ',', '.'),
                'laba_kotor' => 'Rp ' . number_format($grossProfit, 0, ',', '.'),
                'gross_margin' => $grossMargin . '%',
                'total_biaya' => 'Rp ' . number_format($totalExpense, 0, ',', '.'),
                'laba_bersih' => 'Rp ' . number_format($netProfit, 0, ',', '.'),
                'net_margin' => $netMargin . '%',
                'status' => $netProfit >= 0 ? '✅ LABA' : '🔴 RUGI',
                'rincian_biaya' => $expenseRows->map(fn($r) => [
                    'kategori' => $r->category,
                    'total' => 'Rp ' . number_format($r->total, 0, ',', '.'),
                    'persen' => $totalExpense > 0 ? round(($r->total / $totalExpense) * 100, 1) . '%' : '0%',
                ])->toArray(),
            ],
        ];
    }

    public function getSalesTrend(array $args): array
    {
        $period = $args['period'] ?? 'last_30_days';
        $groupBy = $args['group_by'] ?? 'day';
        $topN = (int) ($args['top_n'] ?? 5);

        [$start, $end] = $this->resolveDateRange(['period' => $period]);

        // Tren penjualan per periode
        $format = match ($groupBy) {
            'week' => '%Y-W%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $trend = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [$start, $end])
            ->selectRaw("DATE_FORMAT(date, '{$format}') as period, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn($r) => [
                'periode' => $r->period,
                'orders' => $r->orders,
                'omzet' => 'Rp ' . number_format($r->revenue, 0, ',', '.'),
                'omzet_raw' => (float) $r->revenue,
            ])->toArray();

        // Produk terlaris
        $topProducts = SalesOrderItem::whereHas(
            'salesOrder',
            fn($q) =>
            $q->where('tenant_id', $this->tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('date', [$start, $end])
        )->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(sales_order_items.quantity) as total_qty, SUM(sales_order_items.total) as total_revenue')
            ->groupBy('products.name')
            ->orderByDesc('total_revenue')
            ->limit($topN)
            ->get()
            ->map(fn($r) => [
                'produk' => $r->name,
                'qty' => $r->total_qty,
                'omzet' => 'Rp ' . number_format($r->total_revenue, 0, ',', '.'),
            ])->toArray();

        $totalRevenue = collect($trend)->sum('omzet_raw');
        $totalOrders = collect($trend)->sum('orders');
        $avgDaily = count($trend) > 0 ? $totalRevenue / count($trend) : 0;

        // Perbandingan periode sebelumnya
        $prevDays = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($prevDays);
        $prevEnd = $start->copy()->subDay();
        $prevRevenue = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('total');

        $growth = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : null;

        return [
            'status' => 'success',
            'data' => [
                'periode' => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
                'total_omzet' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                'total_orders' => $totalOrders,
                'rata_rata_harian' => 'Rp ' . number_format($avgDaily, 0, ',', '.'),
                'pertumbuhan' => $growth !== null ? ($growth >= 0 ? "+{$growth}%" : "{$growth}%") : 'N/A',
                'trend' => $trend,
                'produk_terlaris' => $topProducts,
            ],
        ];
    }

    public function getExpenseBreakdown(array $args): array
    {
        [$start, $end] = $this->resolveDateRange($args);

        $rows = Transaction::where('transactions.tenant_id', $this->tenantId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$start, $end])
            ->leftJoin('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Tidak Berkategori") as category, SUM(transactions.amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $total = $rows->sum('total');

        if ($rows->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada pengeluaran pada periode ini.'];
        }

        return [
            'status' => 'success',
            'data' => [
                'periode' => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
                'total_biaya' => 'Rp ' . number_format($total, 0, ',', '.'),
                'rincian' => $rows->map(fn($r) => [
                    'kategori' => $r->category,
                    'total' => 'Rp ' . number_format($r->total, 0, ',', '.'),
                    'jumlah_trx' => $r->count,
                    'persentase' => $total > 0 ? round(($r->total / $total) * 100, 1) . '%' : '0%',
                ])->toArray(),
            ],
        ];
    }

    public function getReceivablesReport(array $args): array
    {
        $type = $args['type'] ?? 'all';
        [$start, $end] = $this->resolveDateRange(['period' => $args['period'] ?? 'this_month']);

        $result = [];

        if (in_array($type, ['receivable', 'all'])) {
            $invoices = Invoice::where('tenant_id', $this->tenantId)
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->selectRaw('status, SUM(total_amount) as total, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $overdueCount = Invoice::where('tenant_id', $this->tenantId)
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', today())
                ->count();

            $result['piutang'] = [
                'total_tagihan' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'sudah_dibayar' => 'Rp ' . number_format($invoices->get('paid')?->total ?? 0, 0, ',', '.'),
                'belum_dibayar' => 'Rp ' . number_format($invoices->get('unpaid')?->total ?? 0, 0, ',', '.'),
                'sebagian_bayar' => 'Rp ' . number_format($invoices->get('partial')?->total ?? 0, 0, ',', '.'),
                'jatuh_tempo' => $overdueCount . ' invoice',
            ];
        }

        if (in_array($type, ['payable', 'all'])) {
            $payables = Payable::where('tenant_id', $this->tenantId)
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->selectRaw('status, SUM(total_amount) as total, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $overduePayable = Payable::where('tenant_id', $this->tenantId)
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', today())
                ->count();

            $result['hutang'] = [
                'total_kewajiban' => 'Rp ' . number_format($payables->sum('total'), 0, ',', '.'),
                'sudah_dibayar' => 'Rp ' . number_format($payables->get('paid')?->total ?? 0, 0, ',', '.'),
                'belum_dibayar' => 'Rp ' . number_format($payables->get('unpaid')?->total ?? 0, 0, ',', '.'),
                'jatuh_tempo' => $overduePayable . ' payable',
            ];
        }

        return [
            'status' => 'success',
            'data' => array_merge(
                ['periode' => $start->format('d M Y') . ' — ' . $end->format('d M Y')],
                $result
            ),
        ];
    }

    public function getInventoryValuation(array $args): array
    {
        // BUG-INV-002 FIX: Eager load with selective columns to reduce memory
        $query = ProductStock::with([
            'product' => function ($q) {
                $q->select('id', 'tenant_id', 'name', 'unit', 'price_buy', 'price_sell');
            },
            'warehouse' => function ($q) {
                $q->select('id', 'tenant_id', 'name');
            }
        ])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $this->tenantId)->where('is_active', true));

        if (!empty($args['warehouse_name'])) {
            $query->whereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$args['warehouse_name']}%"));
        }

        if (!empty($args['category'])) {
            $query->whereHas('product', fn($q) => $q->where('category', 'like', "%{$args['category']}%"));
        }

        $stocks = $query->get();

        $totalCostValue = $stocks->sum(fn($s) => $s->quantity * ($s->product->price_buy ?? 0));
        $totalSellValue = $stocks->sum(fn($s) => $s->quantity * ($s->product->price_sell ?? 0));
        $potentialProfit = $totalSellValue - $totalCostValue;

        // Dead stock: stok > 0 tapi tidak ada penjualan 30 hari terakhir
        $activeProductIds = SalesOrderItem::whereHas(
            'salesOrder',
            fn($q) =>
            $q->where('tenant_id', $this->tenantId)->where('date', '>=', now()->subDays(30))
        )->pluck('product_id')->unique();

        $deadStock = $stocks->filter(
            fn($s) =>
            $s->quantity > 0 && !$activeProductIds->contains($s->product_id)
        );

        // Top 5 by value
        $topByValue = $stocks->sortByDesc(fn($s) => $s->quantity * ($s->product->price_buy ?? 0))
            ->take(5)
            ->map(fn($s) => [
                'produk' => $s->product->name,
                'gudang' => $s->warehouse->name,
                'stok' => $s->quantity . ' ' . $s->product->unit,
                'nilai' => 'Rp ' . number_format($s->quantity * ($s->product->price_buy ?? 0), 0, ',', '.'),
            ])->values()->toArray();

        return [
            'status' => 'success',
            'data' => [
                'total_sku' => $stocks->count(),
                'nilai_modal' => 'Rp ' . number_format($totalCostValue, 0, ',', '.'),
                'nilai_jual' => 'Rp ' . number_format($totalSellValue, 0, ',', '.'),
                'potensi_profit' => 'Rp ' . number_format($potentialProfit, 0, ',', '.'),
                'dead_stock_count' => $deadStock->count(),
                'dead_stock_nilai' => 'Rp ' . number_format(
                    $deadStock->sum(fn($s) => $s->quantity * ($s->product->price_buy ?? 0)),
                    0,
                    ',',
                    '.'
                ),
                'top_5_nilai' => $topByValue,
            ],
        ];
    }

    public function getHrmReport(array $args): array
    {
        [$start, $end] = $this->resolveDateRange($args);

        $employees = Employee::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->with(['attendances' => fn($q) => $q->whereBetween('date', [$start, $end])])
            ->get();

        if ($employees->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada data karyawan.'];
        }

        $summary = [
            'total_karyawan' => $employees->count(),
            'periode' => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
        ];

        $totalDays = $start->diffInDays($end) + 1;

        $perEmployee = $employees->map(function ($emp) use ($totalDays) {
            $att = $emp->attendances;
            $present = $att->whereIn('status', ['present', 'late'])->count();
            $absent = $att->where('status', 'absent')->count();
            $late = $att->where('status', 'late')->count();
            $leave = $att->whereIn('status', ['leave', 'sick'])->count();
            $rate = $totalDays > 0 ? round(($present / $totalDays) * 100, 1) : 0;

            return [
                'nama' => $emp->name,
                'posisi' => $emp->position ?? '-',
                'hadir' => $present,
                'absen' => $absent,
                'terlambat' => $late,
                'izin_sakit' => $leave,
                'tingkat_kehadiran' => $rate . '%',
            ];
        })->sortBy('tingkat_kehadiran')->values()->toArray();

        // Aggregate
        $totalPresent = collect($perEmployee)->sum('hadir');
        $totalAbsent = collect($perEmployee)->sum('absen');
        $totalLate = collect($perEmployee)->sum('terlambat');

        return [
            'status' => 'success',
            'data' => array_merge($summary, [
                'total_hadir' => $totalPresent,
                'total_absen' => $totalAbsent,
                'total_terlambat' => $totalLate,
                'per_karyawan' => $perEmployee,
            ]),
        ];
    }

    public function getProjectFinancialReport(array $args): array
    {
        $query = Project::where('tenant_id', $this->tenantId)
            ->with(['customer', 'expenses']);

        if (!empty($args['status']) && $args['status'] !== 'all') {
            $query->where('status', $args['status']);
        }

        if (!empty($args['period'])) {
            [$start, $end] = $this->resolveDateRange(['period' => $args['period']]);
            $query->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()]);
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada proyek yang ditemukan.'];
        }

        $totalBudget = $projects->sum('budget');
        $totalActual = $projects->sum('actual_cost');
        $overBudget = $projects->filter(fn($p) => $p->actual_cost > $p->budget);
        $onBudget = $projects->filter(fn($p) => $p->actual_cost <= $p->budget);

        $list = $projects->map(fn($p) => [
            'nomor' => $p->number,
            'nama' => $p->name,
            'client' => $p->customer?->name ?? '-',
            'status' => $p->status,
            'progress' => $p->progress . '%',
            'budget' => 'Rp ' . number_format($p->budget, 0, ',', '.'),
            'realisasi' => 'Rp ' . number_format($p->actual_cost, 0, ',', '.'),
            'selisih' => 'Rp ' . number_format($p->budget - $p->actual_cost, 0, ',', '.'),
            'status_budget' => $p->actual_cost > $p->budget ? '🔴 OVER' : '✅ ON BUDGET',
            'persen_terpakai' => $p->budget > 0 ? round(($p->actual_cost / $p->budget) * 100, 1) . '%' : '0%',
        ])->toArray();

        return [
            'status' => 'success',
            'data' => [
                'total_proyek' => $projects->count(),
                'total_budget' => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                'total_realisasi' => 'Rp ' . number_format($totalActual, 0, ',', '.'),
                'total_selisih' => 'Rp ' . number_format($totalBudget - $totalActual, 0, ',', '.'),
                'over_budget' => $overBudget->count() . ' proyek',
                'on_budget' => $onBudget->count() . ' proyek',
                'proyek' => $list,
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function resolveDateRange(array $args): array
    {
        if (!empty($args['start_date']) && !empty($args['end_date'])) {
            return [Carbon::parse($args['start_date']), Carbon::parse($args['end_date'])];
        }

        $period = $args['period'] ?? 'this_month';

        // Normalize common period aliases from Gemini
        $period = match (true) {
            in_array(strtolower($period), ['tahun ini', 'this year', 'this_year', 'year']) => 'this_year',
            in_array(strtolower($period), ['bulan ini', 'this month', 'this_month', 'month']) => 'this_month',
            in_array(strtolower($period), ['minggu ini', 'this week', 'this_week', 'week']) => 'this_week',
            in_array(strtolower($period), ['hari ini', 'today', 'today']) => 'today',
            in_array(strtolower($period), ['bulan lalu', 'last month', 'last_month']) => 'last_month',
            preg_match('/^20\d{2}$/', $period) => 'year_' . $period, // e.g. "2026"
            default => $period,
        };

        // Handle specific year like "2026"
        if (str_starts_with($period, 'year_')) {
            $year = substr($period, 5);
            return [Carbon::createFromDate($year, 1, 1)->startOfYear(), Carbon::createFromDate($year, 12, 31)->endOfYear()];
        }

        return match ($period) {
            'today'        => [today(), today()],
            'this_week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'last_7_days'  => [now()->subDays(6), now()],
            'last_30_days' => [now()->subDays(29), now()],
            'this_month'   => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month'   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year'    => [now()->startOfYear(), now()->endOfYear()],
            default        => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function exportReportPdf(array $args): array
    {
        [$start, $end] = $this->resolveDateRange($args);
        $reportType = $args['report_type'] ?? 'sales';
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $routeMap = [
            'sales' => ['name' => 'reports.sales.pdf', 'params' => ['start_date' => $startStr, 'end_date' => $endStr]],
            'finance' => ['name' => 'reports.finance.pdf', 'params' => ['start_date' => $startStr, 'end_date' => $endStr]],
            'inventory' => ['name' => 'reports.inventory.pdf', 'params' => []],
            'hrm' => ['name' => 'reports.hrm.pdf', 'params' => ['start_date' => $startStr, 'end_date' => $endStr]],
            'receivables' => ['name' => 'reports.receivables.pdf', 'params' => ['start_date' => $startStr, 'end_date' => $endStr]],
            'profit_loss' => ['name' => 'reports.profit-loss.pdf', 'params' => ['start_date' => $startStr, 'end_date' => $endStr]],
        ];

        if (!isset($routeMap[$reportType])) {
            return ['status' => 'error', 'message' => "Jenis laporan '{$reportType}' tidak dikenali. Pilih: sales, finance, inventory, hrm, receivables, profit_loss."];
        }

        $route = $routeMap[$reportType];
        $url = route($route['name'], $route['params']);
        $labels = [
            'sales' => 'Laporan Penjualan',
            'finance' => 'Laporan Keuangan',
            'inventory' => 'Laporan Inventori',
            'hrm' => 'Laporan Kehadiran',
            'receivables' => 'Laporan Piutang',
            'profit_loss' => 'Laporan Laba Rugi',
        ];

        $periodLabel = $reportType === 'inventory'
            ? 'per ' . now()->format('d M Y')
            : $start->format('d M Y') . ' s/d ' . $end->format('d M Y');

        return [
            'status' => 'success',
            'report_type' => $reportType,
            'label' => $labels[$reportType],
            'period' => $periodLabel,
            'download_url' => $url,
            'message' => "📄 **{$labels[$reportType]}** ({$periodLabel}) siap diunduh.\n\n[⬇️ Download PDF]({$url})",
        ];
    }
}