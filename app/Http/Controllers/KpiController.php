<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\KpiTarget;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    // ─── Available metrics definition ────────────────────────────

    public static function availableMetrics(): array
    {
        return [
            'revenue' => ['label' => 'Pendapatan',          'unit' => 'currency'],
            'orders' => ['label' => 'Jumlah Order',        'unit' => 'number'],
            'profit' => ['label' => 'Laba Bersih',         'unit' => 'currency'],
            'new_customers' => ['label' => 'Pelanggan Baru',      'unit' => 'number'],
            'expense' => ['label' => 'Total Pengeluaran',   'unit' => 'currency'],
            'overdue_ar' => ['label' => 'AR Jatuh Tempo',      'unit' => 'currency'],
            'attendance_rate' => ['label' => 'Tingkat Kehadiran',   'unit' => 'percent'],
            'avg_order_value' => ['label' => 'Rata-rata Nilai Order', 'unit' => 'currency'],
        ];
    }

    // ─── Dashboard ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $period = $request->get('period', now()->format('Y-m'));

        $kpis = $this->buildKpiData($tenantId, $period);
        $targets = KpiTarget::where('tenant_id', $tenantId)
            ->where('period', $period)
            ->where('is_active', true)
            ->get();

        // Sync actual values into targets
        foreach ($targets as $target) {
            $actual = $kpis[$target->metric]['actual'] ?? 0;
            if ($target->actual != $actual) {
                $target->update(['actual' => $actual]);
                $target->actual = $actual;
            }
        }

        // Trend data: last 6 months
        $trend = $this->buildTrendData($tenantId);

        // Drill-down data for charts
        $drilldown = $this->buildDrilldown($tenantId, $period);

        $metrics = self::availableMetrics();

        return view('dashboard.kpi', compact('kpis', 'targets', 'trend', 'drilldown', 'period', 'metrics'));
    }

    // ─── AJAX: drill-down data ────────────────────────────────────

    public function drilldown(Request $request, string $metric)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $period = $request->get('period', now()->format('Y-m'));
        [$year, $month] = explode('-', $period);

        $data = match ($metric) {
            'revenue', 'orders', 'avg_order_value' => $this->drillRevenue($tenantId, (int) $year, (int) $month),
            'profit', 'expense' => $this->drillFinance($tenantId, (int) $year, (int) $month),
            'new_customers' => $this->drillCustomers($tenantId, (int) $year, (int) $month),
            'overdue_ar' => $this->drillOverdueAr($tenantId),
            'attendance_rate' => $this->drillAttendance($tenantId, (int) $year, (int) $month),
            default => [],
        };

        return response()->json($data);
    }

    // ─── Store / Update KPI target ────────────────────────────────

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $validated = $request->validate([
            'metric' => 'required|string|in:'.implode(',', array_keys(self::availableMetrics())),
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'target' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:20',
        ]);

        $metrics = self::availableMetrics();
        $kpis = $this->buildKpiData($tenantId, $validated['period']);
        $actual = $kpis[$validated['metric']]['actual'] ?? 0;

        KpiTarget::updateOrCreate(
            ['tenant_id' => $tenantId, 'metric' => $validated['metric'], 'period' => $validated['period']],
            [
                'label' => $metrics[$validated['metric']]['label'],
                'unit' => $metrics[$validated['metric']]['unit'],
                'target' => $validated['target'],
                'actual' => $actual,
                'color' => $validated['color'] ?? '#3b82f6',
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Target KPI disimpan.');
    }

    public function destroy(Request $request, KpiTarget $kpiTarget)
    {
        abort_if($kpiTarget->tenant_id !== $request->user()->tenant_id, 403);
        $kpiTarget->delete();

        return back()->with('success', 'Target KPI dihapus.');
    }

    // ─── Private: compute actuals ─────────────────────────────────

    private function buildKpiData(int $tenantId, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $y = (int) $year;
        $m = (int) $month;

        $revenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->sum('total');

        $orders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->count();

        $income = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
            ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');

        $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');

        $newCustomers = Customer::where('tenant_id', $tenantId)
            ->whereYear('created_at', $y)->whereMonth('created_at', $m)->count();

        $overdueAr = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->sum('remaining_amount');

        $totalEmployees = Employee::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $presentDays = Attendance::where('tenant_id', $tenantId)
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->where('status', 'present')->count();
        $workingDays = Attendance::where('tenant_id', $tenantId)
            ->whereYear('date', $y)->whereMonth('date', $m)->count();
        $attendanceRate = $workingDays > 0 ? round($presentDays / $workingDays * 100, 1) : 0;

        $avgOrderValue = $orders > 0 ? round($revenue / $orders, 2) : 0;

        return [
            'revenue' => ['actual' => $revenue,        'label' => 'Pendapatan',           'unit' => 'currency'],
            'orders' => ['actual' => $orders,         'label' => 'Jumlah Order',          'unit' => 'number'],
            'profit' => ['actual' => $income - $expense, 'label' => 'Laba Bersih',        'unit' => 'currency'],
            'new_customers' => ['actual' => $newCustomers,   'label' => 'Pelanggan Baru',        'unit' => 'number'],
            'expense' => ['actual' => $expense,        'label' => 'Total Pengeluaran',     'unit' => 'currency'],
            'overdue_ar' => ['actual' => $overdueAr,      'label' => 'AR Jatuh Tempo',        'unit' => 'currency'],
            'attendance_rate' => ['actual' => $attendanceRate, 'label' => 'Tingkat Kehadiran',     'unit' => 'percent'],
            'avg_order_value' => ['actual' => $avgOrderValue,  'label' => 'Rata-rata Nilai Order', 'unit' => 'currency'],
        ];
    }

    private function buildTrendData(int $tenantId): array
    {
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $y = (int) $d->year;
            $m = (int) $d->month;

            $revenue = SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('date', $y)->whereMonth('date', $m)->sum('total');

            $income = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
                ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');
            $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
                ->whereYear('date', $y)->whereMonth('date', $m)->sum('amount');

            $trend[] = [
                'month' => $d->format('M Y'),
                'revenue' => $revenue,
                'profit' => $income - $expense,
                'expense' => $expense,
            ];
        }

        return $trend;
    }

    private function buildDrilldown(int $tenantId, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $y = (int) $year;
        $m = (int) $month;

        // Top 5 customers by revenue this period
        $topCustomers = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('customer')
            ->get();

        // Daily revenue this period
        $daysInMonth = Carbon::create($y, $m)->daysInMonth;
        $dailyRevenue = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyRevenue[] = SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('date', $y)->whereMonth('date', $m)->whereDay('date', $d)
                ->sum('total');
        }

        return [
            'top_customers' => $topCustomers,
            'daily_revenue' => $dailyRevenue,
            'days_in_month' => $daysInMonth,
        ];
    }

    // ─── Drill-down JSON helpers ──────────────────────────────────

    private function drillRevenue(int $tenantId, int $y, int $m): array
    {
        $daysInMonth = Carbon::create($y, $m)->daysInMonth;
        $labels = $data = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = $d;
            $data[] = (float) SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('date', $y)->whereMonth('date', $m)->whereDay('date', $d)
                ->sum('total');
        }

        return ['labels' => $labels, 'data' => $data, 'type' => 'bar', 'label' => 'Pendapatan Harian'];
    }

    private function drillFinance(int $tenantId, int $y, int $m): array
    {
        $categories = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->leftJoin('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Lainnya") as cat, SUM(transactions.amount) as total')
            ->groupBy('cat')->orderByDesc('total')->limit(8)->get();

        return [
            'labels' => $categories->pluck('cat')->toArray(),
            'data' => $categories->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
            'type' => 'doughnut',
            'label' => 'Pengeluaran per Kategori',
        ];
    }

    private function drillCustomers(int $tenantId, int $y, int $m): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::create($y, $m)->subMonths($i);
            $data[] = Customer::where('tenant_id', $tenantId)
                ->whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count();
        }
        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $labels[] = Carbon::create($y, $m)->subMonths($i)->format('M Y');
        }

        return ['labels' => $labels, 'data' => $data, 'type' => 'line', 'label' => 'Pelanggan Baru'];
    }

    private function drillOverdueAr(int $tenantId): array
    {
        $buckets = ['current' => 0, '1-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        Invoice::where('tenant_id', $tenantId)->whereIn('status', ['unpaid', 'partial'])->get()
            ->each(function ($inv) use (&$buckets) {
                $buckets[$inv->agingBucket()] = ($buckets[$inv->agingBucket()] ?? 0) + (float) $inv->remaining_amount;
            });

        return [
            'labels' => array_keys($buckets),
            'data' => array_values($buckets),
            'type' => 'bar',
            'label' => 'AR Aging (Rp)',
        ];
    }

    private function drillAttendance(int $tenantId, int $y, int $m): array
    {
        $statuses = ['present', 'late', 'absent', 'leave', 'sick'];
        $data = [];
        foreach ($statuses as $s) {
            $data[] = Attendance::where('tenant_id', $tenantId)
                ->whereYear('date', $y)->whereMonth('date', $m)->where('status', $s)->count();
        }

        return [
            'labels' => ['Hadir', 'Terlambat', 'Absen', 'Cuti', 'Sakit'],
            'data' => $data,
            'type' => 'doughnut',
            'label' => 'Kehadiran',
        ];
    }
}
