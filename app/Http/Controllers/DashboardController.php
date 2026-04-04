<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\AnomalyAlert;
use App\Models\Customer;
use App\Models\CustomDashboardWidget;
use App\Models\EcommerceOrder;
use App\Models\Employee;
use App\Models\PopupAd;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDashboardConfig;
use App\Services\AiInsightService;
use App\Services\DashboardWidgetService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            try {
                return view('dashboard.super_admin', $this->superAdminStats());
            } catch (\Throwable $e) {
                \Log::error("Super admin dashboard failed: " . $e->getMessage());
                return view('dashboard.super_admin', []);
            }
        }

        $tenantId = $user->tenant_id;

        // Redirect ke onboarding jika belum selesai (admin saja)
        if ($user->isAdmin() && $user->tenant && !$user->tenant->onboarding_completed) {
            return redirect()->route('onboarding.show');
        }

        // ── Widget config per user ──────────────────────────────────
        $config = UserDashboardConfig::where('user_id', $user->id)->first();
        $userWidgets = $config?->widgets ?? DashboardWidgetService::defaultsForRole($user->role);

        $registry = DashboardWidgetService::registryForTenant($tenantId, $user->role);
        $availableKeys = array_keys(DashboardWidgetService::availableForRoleAndTenant($tenantId, $user->role));
        $requiredGroups = DashboardWidgetService::requiredDataGroups($userWidgets);

        // ── Load only required data groups ──────────────────────────
        $dataGroups = [];
        if (in_array('sales', $requiredGroups))
            $dataGroups['sales'] = $this->salesStats($tenantId);
        if (in_array('inventory', $requiredGroups))
            $dataGroups['inventory'] = $this->inventoryStats($tenantId);
        if (in_array('finance', $requiredGroups))
            $dataGroups['finance'] = $this->financeStats($tenantId);
        if (in_array('hrm', $requiredGroups))
            $dataGroups['hrm'] = $this->hrmStats($tenantId);
        if (in_array('pos', $requiredGroups))
            $dataGroups['pos'] = $this->posStats($tenantId);

        if (in_array('insights', $requiredGroups)) {
            $dataGroups['insights'] = [
                'insights' => cache()->remember("ai_insights_{$tenantId}", now()->addHour(), function () use ($tenantId) {
                    try {
                        return app(AiInsightService::class)->analyze($tenantId);
                    } catch (\Throwable $e) {
                        \Log::warning("Dashboard insights failed: " . $e->getMessage());
                        return [];
                    }
                }),
            ];
        }

        if (in_array('anomalies', $requiredGroups)) {
            try {
                $dataGroups['anomalies'] = [
                    'openAnomalies' => AnomalyAlert::where('tenant_id', $tenantId)
                        ->where('status', 'open')
                        ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info')")
                        ->latest()
                        ->limit(5)
                        ->get(),
                ];
            } catch (\Throwable $e) {
                \Log::warning("Dashboard anomalies failed: " . $e->getMessage());
                $dataGroups['anomalies'] = ['openAnomalies' => collect()];
            }
        }

        if (in_array('ecommerce', $requiredGroups)) {
            try {
                $dataGroups['ecommerce'] = $this->ecommerceStats($tenantId);
            } catch (\Throwable $e) {
                \Log::warning("Dashboard ecommerce stats failed: " . $e->getMessage());
                $dataGroups['ecommerce'] = [];
            }
        }

        if (in_array('gamification', $requiredGroups)) {
            try {
                $dataGroups['gamification'] = \App\Services\GamificationService::getUserStats($user);
            } catch (\Throwable $e) {
                \Log::warning("Dashboard gamification stats failed: " . $e->getMessage());
                $dataGroups['gamification'] = [];
            }
        }

        if (in_array('custom', $requiredGroups)) {
            // Evaluate each visible custom widget individually
            $customData = [];
            foreach ($userWidgets as $w) {
                if (!($w['visible'] ?? false))
                    continue;
                $key = $w['key'];
                if (!str_starts_with($key, 'custom_'))
                    continue;
                $id = (int) substr($key, 7);
                $cw = CustomDashboardWidget::find($id);
                if ($cw && $cw->tenant_id === $tenantId) {
                    $value = $cw->evaluate($tenantId);
                    $customData[$key] = [
                        'value' => $value,
                        'display' => $cw->formatValue($value),
                        'title' => $cw->title,
                        'subtitle' => $cw->subtitle,
                        'custom_id' => $id,
                    ];
                }
            }
            $dataGroups['custom'] = $customData;
        }

        // ── Map data to each widget ─────────────────────────────────
        $widgetData = [];
        foreach ($userWidgets as $w) {
            $key = $w['key'];
            $meta = $registry[$key] ?? null;
            if (!$meta)
                continue;

            $group = $meta['data_group'];
            if ($group === 'all') {
                $widgetData[$key] = $dataGroups;
            } elseif ($group === 'custom') {
                $widgetData[$key] = $dataGroups['custom'][$key] ?? [];
            } else {
                $widgetData[$key] = $dataGroups[$group] ?? [];
            }
        }

        return view('dashboard.tenant', [
            'userWidgets' => $userWidgets,
            'widgetData' => $widgetData,
            'registry' => $registry,
            'availableKeys' => $availableKeys,
            'customWidgets' => CustomDashboardWidget::where('tenant_id', $tenantId)->get(),
            'popupAd' => PopupAd::where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
                ->where(fn($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
                ->get()
                ->first(fn($ad) => $ad->shouldShowTo($user)),
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
                'id' => $a->id,
                'severity' => $a->severity,
                'title' => $a->title,
                'description' => $a->description,
                'age' => $a->created_at->diffForHumans(),
            ]);

        return response()->json([
            'insights' => array_slice($insights, 0, 6),
            'anomalies' => $anomalies,
            'updated_at' => now()->format('H:i'),
        ]);
    }

    /**
     * AJAX: acknowledge anomaly dari dashboard.
     */
    public function acknowledgeAnomaly(Request $request, AnomalyAlert $anomaly)
    {
        abort_if($anomaly->tenant_id !== $request->user()->tenant_id, 403);

        $anomaly->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ─── Super Admin Stats ────────────────────────────────────────

    private function superAdminStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->get();
        $trialTenants = Tenant::where('plan', 'trial')->count();
        $expiredTenants = Tenant::where('is_active', true)
            ->where(
                fn($q) => $q
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
        $expiringIn7 = Tenant::where('is_active', true)->where(
            fn($q) => $q
                ->where(fn($q2) => $q2->where('plan', 'trial')->whereBetween('trial_ends_at', [now(), now()->addDays(7)]))
                ->orWhere(fn($q2) => $q2->where('plan', '!=', 'trial')->whereBetween('plan_expires_at', [now(), now()->addDays(7)]))
        )->get();

        $expiringIn30 = Tenant::where('is_active', true)->where(
            fn($q) => $q
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
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'expiredTenants',
            'totalUsers',
            'newThisMonth',
            'newThisWeek',
            'aiThisMonth',
            'mrrEstimate',
            'expiringIn7',
            'expiringIn30',
            'growthChart',
            'planDist',
            'recentTenants',
            'topAiTenants'
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
                'date' => $d->format('d M'),
                'total' => (float) ($dailySales[$key] ?? 0),
            ];
        }

        return [
            'this_month_revenue' => $thisRevenue,
            'this_month_orders' => $thisMonth->count(),
            'growth_percent' => round($growth, 1),
            'pending_orders' => SalesOrder::where('tenant_id', $tenantId)->whereIn('status', ['pending', 'confirmed'])->count(),
            'chart' => $chartData,
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
            'total_products' => \App\Models\Product::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'total_warehouses' => \App\Models\Warehouse::where('tenant_id', $tenantId)->count(),
            'low_stock_count' => $lowStock->count(),
            'low_stock_items' => $lowStock,
            'expiring_soon' => $expiringCount,
        ];
    }

    private function financeStats(int $tenantId): array
    {
        $income = Transaction::where('tenant_id', $tenantId)->where('type', 'income')
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
            $d = now()->subMonths($i);
            $ym = $d->format('Y-m');
            $group = $monthlyTx[$ym] ?? collect();
            $chartData[] = [
                'month' => $d->format('M Y'),
                'income' => (float) $group->where('type', 'income')->sum('total'),
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
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense,
            'pending_po' => PurchaseOrder::where('tenant_id', $tenantId)->whereIn('status', ['draft', 'sent'])->count(),
            'overdue_invoices' => $overdueInvoices,
            'top_expenses' => $topExpenses,
            'chart' => $chartData,
        ];
    }

    private function hrmStats(int $tenantId): array
    {
        return [
            'total_employees' => Employee::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'present_today' => \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', today())->where('status', 'present')->count(),
            'absent_today' => \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', today())->where('status', 'absent')->count(),
            'total_customers' => Customer::where('tenant_id', $tenantId)->where('is_active', true)->count(),
        ];
    }

    private function ecommerceStats(int $tenantId): array
    {
        $thisMonth = EcommerceOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year);

        $lastMonth = EcommerceOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('ordered_at', now()->subMonth()->month)
            ->whereYear('ordered_at', now()->subMonth()->year);

        $thisCount = $thisMonth->count();
        $lastCount = $lastMonth->count();
        $thisRevenue = $thisMonth->sum('total');
        $pending = EcommerceOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $growth = $lastCount > 0
            ? round((($thisCount - $lastCount) / $lastCount) * 100, 1)
            : ($thisCount > 0 ? 100 : 0);

        return [
            'this_month_orders' => $thisCount,
            'this_month_revenue' => $thisRevenue,
            'pending_orders' => $pending,
            'growth_percent' => $growth,
        ];
    }

    private function posStats(int $tenantId): array
    {
        $stats = SalesOrder::where('tenant_id', $tenantId)
            ->where('source', 'pos')
            ->whereDate('date', today())
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('COALESCE(SUM(total), 0) as revenue, COUNT(*) as cnt')
            ->first();

        $revenue = (float) $stats->revenue;
        $count = (int) $stats->cnt;

        return [
            'revenue' => $revenue,
            'count' => $count,
            'avg_ticket' => $count > 0 ? round($revenue / $count) : 0,
        ];
    }

    // ─── Widget CRUD ─────────────────────────────────────────────

    public function saveWidgets(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.key' => 'required|string',
            'widgets.*.order' => 'required|integer|min:0',
            'widgets.*.visible' => 'required|boolean',
        ]);

        $user = $request->user();
        $available = array_keys(DashboardWidgetService::availableForRole($user->role));

        // Only keep widgets the user's role can see
        $widgets = collect($request->widgets)
            ->filter(fn($w) => in_array($w['key'], $available))
            ->sortBy('order')
            ->values()
            ->toArray();

        UserDashboardConfig::updateOrCreate(
            ['user_id' => $user->id],
            ['widgets' => $widgets],
        );

        return response()->json(['ok' => true]);
    }

    public function resetWidgets(Request $request)
    {
        $user = $request->user();

        UserDashboardConfig::where('user_id', $user->id)->delete();

        return response()->json([
            'ok' => true,
            'widgets' => DashboardWidgetService::defaultsForRole($user->role),
        ]);
    }

    // ─── Custom Widget Builder ────────────────────────────────────

    /**
     * GET: List all custom widgets for the current tenant.
     */
    public function customWidgetsList(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $widgets = CustomDashboardWidget::where('tenant_id', $tenantId)
            ->with('creator')
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'key' => $w->registryKey(),
                'title' => $w->title,
                'subtitle' => $w->subtitle,
                'metric_type' => $w->metric_type,
                'model_class' => $w->model_class,
                'cols' => $w->cols,
                'icon_bg' => $w->icon_bg,
                'icon_color' => $w->icon_color,
                'created_by' => $w->creator?->name,
            ]);

        return response()->json(['ok' => true, 'widgets' => $widgets]);
    }

    /**
     * GET: Return a single custom widget as JSON (used by builder edit form).
     */
    public function customWidgetShow(Request $request, CustomDashboardWidget $customWidget)
    {
        abort_if($customWidget->tenant_id !== $request->user()->tenant_id, 403);

        return response()->json([
            'id' => $customWidget->id,
            'title' => $customWidget->title,
            'subtitle' => $customWidget->subtitle,
            'metric_type' => $customWidget->metric_type,
            'model_class' => $customWidget->model_class,
            'metric_column' => $customWidget->metric_type !== 'static' ? $customWidget->metric_column : null,
            'static_value' => $customWidget->metric_type === 'static' ? $customWidget->metric_column : null,
            'date_scope' => $customWidget->date_scope,
            'value_format' => $customWidget->value_format,
            'cols' => $customWidget->cols,
            'icon_bg' => $customWidget->icon_bg,
            'icon_color' => $customWidget->icon_color,
        ]);
    }

    /**
     * POST: Create a new custom widget.
     */
    public function customWidgetStore(Request $request)
    {
        $user = $request->user();
        abort_if(!in_array($user->role, ['admin', 'manager', 'super_admin']), 403);

        $data = $request->validate([
            'title' => 'required|string|max:60',
            'subtitle' => 'nullable|string|max:100',
            'icon_bg' => 'nullable|string|max:50',
            'icon_color' => 'nullable|string|max:50',
            'cols' => 'nullable|integer|in:1,2,4',
            'metric_type' => 'required|in:count,sum,avg,static',
            'model_class' => 'nullable|string|max:50',
            'metric_column' => 'nullable|string|max:50',
            'static_value' => 'nullable|numeric',
            'where_conditions' => 'nullable|array',
            'date_scope' => 'nullable|in:today,this_month,this_year,all_time',
            'date_column' => 'nullable|string|max:50',
            'value_prefix' => 'nullable|string|max:20',
            'value_suffix' => 'nullable|string|max:20',
            'value_format' => 'nullable|in:number,currency,percent',
            'visible_to_roles' => 'nullable|array',
        ]);

        // For static widgets, store the static value in metric_column
        if (($data['metric_type'] ?? '') === 'static' && isset($data['static_value'])) {
            $data['metric_column'] = (string) $data['static_value'];
        }
        unset($data['static_value']);

        $widget = CustomDashboardWidget::create(array_merge($data, [
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
        ]));

        return response()->json([
            'ok' => true,
            'key' => $widget->registryKey(),
            'id' => $widget->id,
        ]);
    }

    /**
     * PUT: Update an existing custom widget.
     */
    public function customWidgetUpdate(Request $request, CustomDashboardWidget $customWidget)
    {
        $user = $request->user();
        abort_if($customWidget->tenant_id !== $user->tenant_id, 403);
        abort_if(!in_array($user->role, ['admin', 'manager', 'super_admin']), 403);

        $data = $request->validate([
            'title' => 'required|string|max:60',
            'subtitle' => 'nullable|string|max:100',
            'icon_bg' => 'nullable|string|max:50',
            'icon_color' => 'nullable|string|max:50',
            'cols' => 'nullable|integer|in:1,2,4',
            'metric_type' => 'required|in:count,sum,avg,static',
            'model_class' => 'nullable|string|max:50',
            'metric_column' => 'nullable|string|max:50',
            'static_value' => 'nullable|numeric',
            'where_conditions' => 'nullable|array',
            'date_scope' => 'nullable|in:today,this_month,this_year,all_time',
            'date_column' => 'nullable|string|max:50',
            'value_prefix' => 'nullable|string|max:20',
            'value_suffix' => 'nullable|string|max:20',
            'value_format' => 'nullable|in:number,currency,percent',
            'visible_to_roles' => 'nullable|array',
        ]);

        // For static widgets, store the static value in metric_column
        if (($data['metric_type'] ?? '') === 'static' && isset($data['static_value'])) {
            $data['metric_column'] = (string) $data['static_value'];
        }
        unset($data['static_value']);

        $customWidget->update($data);

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE: Remove a custom widget.
     */
    public function customWidgetDelete(Request $request, CustomDashboardWidget $customWidget)
    {
        $user = $request->user();
        abort_if($customWidget->tenant_id !== $user->tenant_id, 403);
        abort_if(!in_array($user->role, ['admin', 'manager', 'super_admin']), 403);

        $customWidget->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * POST: Preview a custom widget value without saving.
     */
    public function customWidgetPreview(Request $request)
    {
        $user = $request->user();
        abort_if(!in_array($user->role, ['admin', 'manager', 'super_admin']), 403);

        $data = $request->validate([
            'metric_type' => 'required|in:count,sum,avg,static',
            'model_class' => 'nullable|string|max:50',
            'metric_column' => 'nullable|string|max:20',
            'static_value' => 'nullable|numeric',
            'where_conditions' => 'nullable|array',
            'date_scope' => 'nullable|in:today,this_month,this_year,all_time',
            'date_column' => 'nullable|string|max:50',
            'value_format' => 'nullable|in:number,currency,percent',
            'value_prefix' => 'nullable|string|max:20',
            'value_suffix' => 'nullable|string|max:20',
        ]);

        // For static type, static_value is stored in metric_column
        if (($data['metric_type'] ?? '') === 'static' && isset($data['static_value'])) {
            $data['metric_column'] = (string) $data['static_value'];
        }
        unset($data['static_value']);

        $mock = new CustomDashboardWidget($data);
        $value = $mock->evaluate($user->tenant_id);

        return response()->json([
            'ok' => true,
            'value' => $value,
            'display' => $mock->formatValue($value),
        ]);
    }
}
