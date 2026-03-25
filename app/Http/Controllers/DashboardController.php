<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\AnomalyAlert;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AiInsightService;
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

        // AI Insights — ambil dari cache atau generate on-demand (max 1x per jam)
        $insights = cache()->remember("ai_insights_{$tenantId}", now()->addHour(), function () use ($tenantId) {
            return app(AiInsightService::class)->analyze($tenantId);
        });

        // Anomali open terbaru untuk highlight di dashboard
        $openAnomalies = AnomalyAlert::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info')")
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.tenant', [
            'sales'          => $this->salesStats($tenantId),
            'inventory'      => $this->inventoryStats($tenantId),
            'finance'        => $this->financeStats($tenantId),
            'hrm'            => $this->hrmStats($tenantId),
            'insights'       => $insights,
            'openAnomalies'  => $openAnomalies,
        ]);
    }

    /**
     * AJAX: refresh insights + anomalies tanpa reload halaman.
     * Bust cache dan generate ulang.
     */
    public function refreshInsights(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Bust cache dan generate ulang
        cache()->forget("ai_insights_{$tenantId}");
        $insights = cache()->remember("ai_insights_{$tenantId}", now()->addHour(), function () use ($tenantId) {
            return app(AiInsightService::class)->analyze($tenantId);
        });

        // Ambil anomali open terbaru (max 5)
        $anomalies = AnomalyAlert::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info')")
            ->latest()
            ->limit(5)
            ->get(['id', 'type', 'severity', 'title', 'description', 'created_at'])
            ->map(fn($a) => [
                'id'          => $a->id,
                'severity'    => $a->severity,
                'title'       => $a->title,
                'description' => $a->description,
                'age'         => $a->created_at->diffForHumans(),
            ]);

        return response()->json([
            'insights'  => array_slice($insights, 0, 6),
            'anomalies' => $anomalies,
            'updated_at'=> now()->format('H:i'),
        ]);
    }

    /**
     * AJAX: acknowledge anomaly dari dashboard.
     */
    public function acknowledgeAnomaly(Request $request, AnomalyAlert $anomaly)
    {
        abort_if($anomaly->tenant_id !== $request->user()->tenant_id, 403);

        $anomaly->update([
            'status'          => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return response()->json(['ok' => true]);
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

        // New tenants this month & this week
        $newThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $newThisWeek = Tenant::where('created_at', '>=', now()->startOfWeek())->count();

        // AI usage this month across all tenants
        $aiThisMonth = AiUsageLog::where('month', now()->format('Y-m'))->sum('message_count');

        // MRR estimate — sum price_monthly of active paid tenants with a subscription plan
        $mrrEstimate = \App\Models\SubscriptionPlan::join('tenants', 'subscription_plans.id', '=', 'tenants.subscription_plan_id')
            ->where('tenants.is_active', true)
            ->where('tenants.plan', '!=', 'trial')
            ->sum('subscription_plans.price_monthly');

        // Tenants expiring in 7 / 14 / 30 days (trial or paid)
        $expiringIn7  = Tenant::where('is_active', true)->where(fn($q) => $q
            ->where(fn($q2) => $q2->where('plan', 'trial')->whereBetween('trial_ends_at', [now(), now()->addDays(7)]))
            ->orWhere(fn($q2) => $q2->where('plan', '!=', 'trial')->whereBetween('plan_expires_at', [now(), now()->addDays(7)]))
        )->get();

        $expiringIn30 = Tenant::where('is_active', true)->where(fn($q) => $q
            ->where(fn($q2) => $q2->where('plan', 'trial')->whereBetween('trial_ends_at', [now(), now()->addDays(30)]))
            ->orWhere(fn($q2) => $q2->where('plan', '!=', 'trial')->whereBetween('plan_expires_at', [now(), now()->addDays(30)]))
        )->with('admins')->orderByRaw("COALESCE(trial_ends_at, plan_expires_at) ASC")->get();

        // Tenant growth chart — last 6 months — 1 aggregate query (was 6 separate queries)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $monthlyGrowth = Tenant::where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('cnt', 'ym');

        $growthChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $growthChart[] = [
                'month' => $d->format('M Y'),
                'count' => (int) ($monthlyGrowth[$d->format('Y-m')] ?? 0),
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
            'totalUsers', 'newThisMonth', 'newThisWeek', 'aiThisMonth',
            'mrrEstimate', 'expiringIn7', 'expiringIn30',
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

        // Chart data: 7 hari terakhir — SINGLE aggregate query instead of 7 separate queries
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $dailySales = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('date', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(date) as sale_date, SUM(total) as day_total')
            ->groupByRaw('DATE(date)')
            ->pluck('day_total', 'sale_date');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->format('Y-m-d');
            $chartData[] = [
                'date'  => $d->format('d M'),
                'total' => (float) ($dailySales[$key] ?? 0),
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

        // Produk akan expired dalam 7 hari
        $expiringCount = \App\Models\ProductBatch::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays(7))
            ->whereHas('product', fn($q) => $q->where('has_expiry', true))
            ->count();

        return [
            'total_products'   => \App\Models\Product::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'total_warehouses' => \App\Models\Warehouse::where('tenant_id', $tenantId)->count(),
            'low_stock_count'  => $lowStock->count(),
            'low_stock_items'  => $lowStock,
            'expiring_soon'    => $expiringCount,
        ];
    }

    private function financeStats(int $tenantId): array
    {
        $income  = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $expense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');

        // Chart 6 bulan terakhir — 1 aggregate query (was 12 separate queries)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $monthlyTx = Transaction::where('tenant_id', $tenantId)
            ->whereDate('date', '>=', $sixMonthsAgo)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, type, SUM(amount) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m'), type")
            ->get()
            ->groupBy('ym');

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d  = now()->subMonths($i);
            $ym = $d->format('Y-m');
            $group = $monthlyTx[$ym] ?? collect();
            $chartData[] = [
                'month'   => $d->format('M Y'),
                'income'  => (float) $group->where('type', 'income')->sum('total'),
                'expense' => (float) $group->where('type', 'expense')->sum('total'),
            ];
        }

        // Top expense categories this month
        $topExpenses = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Overdue invoices
        $overdueInvoices = \App\Models\Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->count();

        return [
            'income'           => $income,
            'expense'          => $expense,
            'profit'           => $income - $expense,
            'pending_po'       => PurchaseOrder::where('tenant_id', $tenantId)->whereIn('status', ['draft', 'sent'])->count(),
            'overdue_invoices' => $overdueInvoices,
            'top_expenses'     => $topExpenses,
            'chart'            => $chartData,
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
