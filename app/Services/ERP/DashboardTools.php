<?php

namespace App\Services\ERP;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Transaction;

class DashboardTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_dashboard_summary',
                'description' => 'Tampilkan ringkasan kondisi bisnis secara menyeluruh: penjualan, keuangan, stok, dan kehadiran karyawan. '
                    .'Gunakan untuk: "gimana kondisi bisnis hari ini?", "rekap minggu ini", '
                    .'"laporan harian", "summary bisnis", "kondisi toko hari ini", "rekap bulan ini".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'description' => 'Periode: today, this_week, this_month, last_month. Default: today',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getDashboardSummary(array $args): array
    {
        $period = $args['period'] ?? 'today';

        return [
            'status' => 'success',
            'period' => $period,
            'data' => [
                'sales' => $this->getSalesSummary($period),
                'finance' => $this->getFinanceSummary($period),
                'inventory' => $this->getInventorySummary(),
                'hrm' => $this->getHrmSummary($period),
            ],
        ];
    }

    // ─── Private helpers ─────────────────────────────────────────

    private function getSalesSummary(string $period): array
    {
        $query = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled']);

        $query = $this->applyPeriod($query, $period);

        $orders = $query->get();
        $revenue = $orders->sum('total');
        $count = $orders->count();

        $byStatus = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereIn('status', ['pending', 'confirmed', 'processing'])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total_orders' => $count,
            'total_revenue' => $revenue,
            'revenue_fmt' => 'Rp '.number_format($revenue, 0, ',', '.'),
            'avg_order' => $count > 0 ? 'Rp '.number_format($revenue / $count, 0, ',', '.') : 'Rp 0',
            'pending_orders' => $byStatus->sum(),
        ];
    }

    private function getFinanceSummary(string $period): array
    {
        $income = $this->applyPeriod(
            Transaction::where('tenant_id', $this->tenantId)->where('type', 'income'), $period
        )->sum('amount');

        $expense = $this->applyPeriod(
            Transaction::where('tenant_id', $this->tenantId)->where('type', 'expense'), $period
        )->sum('amount');

        $profit = $income - $expense;

        return [
            'income' => 'Rp '.number_format($income, 0, ',', '.'),
            'expense' => 'Rp '.number_format($expense, 0, ',', '.'),
            'profit' => 'Rp '.number_format($profit, 0, ',', '.'),
            'profit_status' => $profit >= 0 ? 'SURPLUS' : 'DEFISIT',
        ];
    }

    private function getInventorySummary(): array
    {
        $totalProducts = Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)->count();

        $lowStock = ProductStock::whereHas('product', fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->whereColumn('quantity', '<=', 'products.stock_min')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->count();

        $outOfStock = ProductStock::whereHas('product', fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->where('quantity', 0)
            ->count();

        return [
            'total_products' => $totalProducts,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'stock_status' => $lowStock === 0 ? 'AMAN' : "{$lowStock} produk perlu restock",
        ];
    }

    private function getHrmSummary(string $period): array
    {
        $totalEmployees = Employee::where('tenant_id', $this->tenantId)
            ->where('status', 'active')->count();

        $attendanceQuery = Attendance::where('tenant_id', $this->tenantId);
        $attendanceQuery = $this->applyPeriodByDate($attendanceQuery, $period);

        $attendance = $attendanceQuery
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total_employees' => $totalEmployees,
            'present' => $attendance->get('present', 0),
            'absent' => $attendance->get('absent', 0),
            'late' => $attendance->get('late', 0),
            'leave' => $attendance->get('leave', 0) + $attendance->get('sick', 0),
        ];
    }

    private function applyPeriod($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'last_month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
            default => $query->whereDate('created_at', today()),
        };
    }

    private function applyPeriodByDate($query, string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('date', today()),
            'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
            'last_month' => $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year),
            default => $query->whereDate('date', today()),
        };
    }
}
