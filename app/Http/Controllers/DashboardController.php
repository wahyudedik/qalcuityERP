<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard.super_admin', $this->superAdminStats());
        }

        $tenantId = $user->tenant_id;

        // Redirect ke onboarding jika belum selesai (admin saja)
        if ($user->isAdmin() && $user->tenant && !$user->tenant->onboarding_completed) {
            return redirect()->route('onboarding.show');
        }

        return view('dashboard.tenant', [
            'sales'     => $this->salesStats($tenantId),
            'inventory' => $this->inventoryStats($tenantId),
            'finance'   => $this->financeStats($tenantId),
            'hrm'       => $this->hrmStats($tenantId),
        ]);
    }

    // ─── Super Admin Stats ────────────────────────────────────────

    private function superAdminStats(): array
    {
        $totalTenants  = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->get();
        $trialTenants  = Tenant::where('plan', 'trial')->count();
        $expiredTenants= Tenant::where('is_active', true)
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('plan', 'trial')->where('trial_ends_at', '<', now()))
                ->orWhere(fn($q2) => $q2->where('plan', '!=', 'trial')->whereNotNull('plan_expires_at')->where('plan_expires_at', '<', now()))
            )->count();

        $totalUsers = User::where('role', '!=', 'super_admin')->count();

        // New tenants this month
        $newThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        // AI usage this month across all tenants
        $aiThisMonth = AiUsageLog::where('month', now()->format('Y-m'))->sum('message_count');

        // Tenant growth chart — last 6 months
        $growthChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $growthChart[] = [
                'month' => $d->format('M Y'),
                'count' => Tenant::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->count(),
            ];
        }

        // Plan distribution
        $planDist = Tenant::selectRaw('plan, count(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        // Recent tenants
        $recentTenants = Tenant::with('subscriptionPlan')
            ->latest()->take(8)->get();

        // AI usage per tenant this month (top 5)
        $topAiTenants = AiUsageLog::where('month', now()->format('Y-m'))
            ->selectRaw('tenant_id, SUM(message_count) as total')
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->load('tenant');

        return compact(
            'totalTenants', 'activeTenants', 'trialTenants', 'expiredTenants',
            'totalUsers', 'newThisMonth', 'aiThisMonth',
            'growthChart', 'planDist', 'recentTenants', 'topAiTenants'
        );
    }

    private function salesStats(int $tenantId): array
    {
        $thisMonth = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);

        $lastMonth = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year);

        $thisRevenue = $thisMonth->sum('total');
        $lastRevenue = $lastMonth->sum('total');
        $growth = $lastRevenue > 0 ? (($thisRevenue - $lastRevenue) / $lastRevenue) * 100 : 0;

        // Chart data: 7 hari terakhir
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date'  => $date->format('d M'),
                'total' => SalesOrder::where('tenant_id', $tenantId)
                    ->whereDate('date', $date)
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('total'),
            ];
        }

        return [
            'this_month_revenue' => $thisRevenue,
            'this_month_orders'  => $thisMonth->count(),
            'growth_percent'     => round($growth, 1),
            'pending_orders'     => SalesOrder::where('tenant_id', $tenantId)->whereIn('status', ['pending', 'confirmed'])->count(),
            'chart'              => $chartData,
        ];
    }

    private function inventoryStats(int $tenantId): array
    {
        $lowStock = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->whereColumn('quantity', '<=', 'products.stock_min')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->limit(5)
            ->get();

        return [
            'total_products'  => \App\Models\Product::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'total_warehouses'=> \App\Models\Warehouse::where('tenant_id', $tenantId)->count(),
            'low_stock_count' => $lowStock->count(),
            'low_stock_items' => $lowStock,
        ];
    }

    private function financeStats(int $tenantId): array
    {
        $income  = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');

        // Chart 6 bulan terakhir
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartData[] = [
                'month'   => $d->format('M Y'),
                'income'  => Transaction::where('tenant_id', $tenantId)->where('type', 'income')
                    ->whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('amount'),
                'expense' => Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
                    ->whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('amount'),
            ];
        }

        return [
            'income'         => $income,
            'expense'        => $expense,
            'profit'         => $income - $expense,
            'pending_po'     => PurchaseOrder::where('tenant_id', $tenantId)->whereIn('status', ['draft', 'sent'])->count(),
            'chart'          => $chartData,
        ];
    }

    private function hrmStats(int $tenantId): array
    {
        return [
            'total_employees' => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'present_today'   => \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', today())->where('status', 'present')->count(),
            'absent_today'    => \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', today())->where('status', 'absent')->count(),
            'total_customers' => Customer::where('tenant_id', $tenantId)->where('is_active', true)->count(),
        ];
    }
}
