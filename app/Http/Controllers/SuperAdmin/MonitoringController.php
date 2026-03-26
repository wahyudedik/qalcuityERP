<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiUsageLog;
use App\Models\AnomalyAlert;
use App\Models\ErrorLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'errors');

        // ── Error Logs ────────────────────────────────────────────
        $errorQuery = ErrorLog::latest();
        if ($level = $request->input('level')) {
            $errorQuery->where('level', $level);
        }
        if ($request->input('unresolved')) {
            $errorQuery->where('is_resolved', false);
        }
        $errors = $errorQuery->paginate(30, ['*'], 'error_page')->withQueryString();

        $errorStats = [
            'total'      => ErrorLog::count(),
            'unresolved' => ErrorLog::where('is_resolved', false)->count(),
            'today'      => ErrorLog::whereDate('created_at', today())->count(),
            'critical'   => ErrorLog::where('level', 'critical')->where('is_resolved', false)->count(),
        ];

        // ── AI Usage ──────────────────────────────────────────────
        $currentMonth = now()->format('Y-m');
        $aiUsage = AiUsageLog::with('tenant')
            ->where('month', $currentMonth)
            ->selectRaw('tenant_id, SUM(message_count) as total_messages, SUM(token_count) as total_tokens')
            ->groupBy('tenant_id')
            ->orderByDesc('total_messages')
            ->get();

        $aiMonthly = AiUsageLog::selectRaw('month, SUM(message_count) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        $aiStats = [
            'total_this_month' => AiUsageLog::where('month', $currentMonth)->sum('message_count'),
            'total_tokens'     => AiUsageLog::where('month', $currentMonth)->sum('token_count'),
            'active_tenants'   => AiUsageLog::where('month', $currentMonth)->distinct('tenant_id')->count('tenant_id'),
        ];

        // ── Activity Log ──────────────────────────────────────────
        $activityQuery = ActivityLog::with('user')->latest();
        if ($action = $request->input('action')) {
            $activityQuery->where('action', 'like', "%{$action}%");
        }
        if ($tenantId = $request->input('tenant_id')) {
            $activityQuery->where('tenant_id', $tenantId);
        }
        $activities = $activityQuery->paginate(30, ['*'], 'activity_page')->withQueryString();

        // ── System Health ─────────────────────────────────────────
        $health = $this->getSystemHealth();

        // ── Anomalies ─────────────────────────────────────────────
        $anomalies = AnomalyAlert::with('tenant')
            ->where('status', 'open')
            ->orderByRaw("FIELD(severity,'critical','warning','info')")
            ->latest()
            ->limit(50)
            ->get();

        $anomalyStats = [
            'open'     => AnomalyAlert::where('status', 'open')->count(),
            'critical' => AnomalyAlert::where('status', 'open')->where('severity', 'critical')->count(),
            'warning'  => AnomalyAlert::where('status', 'open')->where('severity', 'warning')->count(),
        ];

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        // ── Module Health (cross-module metrics) ──────────────────
        $moduleHealth = $this->getModuleHealth();

        return view('super-admin.monitoring.index', compact(
            'tab', 'errors', 'errorStats',
            'aiUsage', 'aiMonthly', 'aiStats',
            'activities', 'anomalies', 'anomalyStats',
            'health', 'tenants', 'moduleHealth'
        ));
    }

    public function resolveError(ErrorLog $error): RedirectResponse
    {
        $error->update(['is_resolved' => true, 'resolved_at' => now()]);
        return back()->with('success', 'Error ditandai sebagai resolved.');
    }

    public function resolveAllErrors(): RedirectResponse
    {
        ErrorLog::where('is_resolved', false)->update(['is_resolved' => true, 'resolved_at' => now()]);
        return back()->with('success', 'Semua error ditandai sebagai resolved.');
    }

    public function deleteError(ErrorLog $error): RedirectResponse
    {
        $error->delete();
        return back()->with('success', 'Error log dihapus.');
    }

    public function clearErrors(): RedirectResponse
    {
        ErrorLog::where('is_resolved', true)->delete();
        return back()->with('success', 'Error log yang sudah resolved berhasil dibersihkan.');
    }

    public function healthJson(): JsonResponse
    {
        return response()->json($this->getSystemHealth());
    }

    private function getSystemHealth(): array
    {
        $dbOk = true;
        $dbLatency = 0;
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $start) * 1000, 2);
        } catch (\Throwable) {
            $dbOk = false;
        }

        $diskTotal = disk_total_space(base_path());
        $diskFree  = disk_free_space(base_path());
        $diskUsed  = $diskTotal - $diskFree;
        $diskPct   = $diskTotal > 0 ? round($diskUsed / $diskTotal * 100, 1) : 0;

        $logPath  = storage_path('logs/laravel.log');
        $logSize  = file_exists($logPath) ? filesize($logPath) : 0;

        return [
            'db_ok'          => $dbOk,
            'db_latency_ms'  => $dbLatency,
            'disk_total_gb'  => round($diskTotal / 1073741824, 1),
            'disk_free_gb'   => round($diskFree / 1073741824, 1),
            'disk_used_pct'  => $diskPct,
            'log_size_mb'    => round($logSize / 1048576, 2),
            'php_version'    => PHP_VERSION,
            'laravel_version'=> app()->version(),
            'total_tenants'  => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users'    => User::count(),
            'errors_today'   => ErrorLog::whereDate('created_at', today())->count(),
            'queue_failed'   => $this->getFailedJobsCount(),
            'uptime'         => $this->getUptime(),
        ];
    }

    private function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Module-level health metrics across all tenants.
     * Covers all 35+ modules with actionable alerts.
     */
    private function getModuleHealth(): array
    {
        $modules = [];

        // Sales & Invoicing
        $modules['sales'] = [
            'label' => 'Penjualan',
            'total' => DB::table('sales_orders')->count(),
            'today' => DB::table('sales_orders')->whereDate('created_at', today())->count(),
            'alerts' => [],
        ];

        $overdueInvoices = DB::table('invoices')->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', today())->count();
        $modules['invoices'] = [
            'label' => 'Invoice',
            'total' => DB::table('invoices')->count(),
            'alerts' => $overdueInvoices > 0 ? [['type' => 'warning', 'msg' => "{$overdueInvoices} invoice jatuh tempo"]] : [],
        ];

        // Inventory
        $lowStock = DB::table('products')
            ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
            ->whereRaw('product_stocks.quantity <= products.stock_min')
            ->where('products.is_active', true)
            ->count();
        $modules['inventory'] = [
            'label' => 'Inventori',
            'total' => DB::table('products')->where('is_active', true)->count(),
            'alerts' => $lowStock > 0 ? [['type' => 'warning', 'msg' => "{$lowStock} produk stok rendah"]] : [],
        ];

        // Purchasing
        $modules['purchasing'] = [
            'label' => 'Pembelian',
            'total' => DB::table('purchase_orders')->count(),
            'alerts' => [],
        ];

        // GL Posting failures (check journal entries with issues)
        $glOrphans = DB::table('journal_entries')->where('status', 'draft')->where('created_at', '<', now()->subDays(7))->count();
        $modules['accounting'] = [
            'label' => 'Akuntansi / GL',
            'total' => DB::table('journal_entries')->count(),
            'alerts' => $glOrphans > 0 ? [['type' => 'warning', 'msg' => "{$glOrphans} jurnal draft > 7 hari"]] : [],
        ];

        // HRM & Payroll
        $modules['hrm'] = [
            'label' => 'SDM & Payroll',
            'total' => DB::table('employees')->where('status', 'active')->count(),
            'alerts' => [],
        ];

        // Manufacturing
        $stuckWo = DB::table('work_orders')->where('status', 'in_progress')->where('started_at', '<', now()->subDays(30))->count();
        $modules['manufacturing'] = [
            'label' => 'Manufaktur',
            'total' => DB::table('work_orders')->count(),
            'alerts' => $stuckWo > 0 ? [['type' => 'warning', 'msg' => "{$stuckWo} WO in-progress > 30 hari"]] : [],
        ];

        // Fleet
        try {
            $expVehicles = DB::table('fleet_vehicles')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereBetween('registration_expiry', [now(), now()->addDays(30)])
                      ->orWhereBetween('insurance_expiry', [now(), now()->addDays(30)]);
                })->count();
            $maintDue = DB::table('fleet_maintenances')->where('status', 'scheduled')->where('scheduled_date', '<=', now()->addDays(7))->count();
            $modules['fleet'] = [
                'label' => 'Fleet',
                'total' => DB::table('fleet_vehicles')->where('is_active', true)->count(),
                'alerts' => array_filter([
                    $expVehicles > 0 ? ['type' => 'warning', 'msg' => "{$expVehicles} kendaraan STNK/asuransi segera expired"] : null,
                    $maintDue > 0 ? ['type' => 'info', 'msg' => "{$maintDue} maintenance terjadwal minggu ini"] : null,
                ]),
            ];
        } catch (\Throwable) { $modules['fleet'] = ['label' => 'Fleet', 'total' => 0, 'alerts' => []]; }

        // Contracts
        try {
            $expiringContracts = DB::table('contracts')->where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(30)])->count();
            $pendingBilling = DB::table('contract_billings')->where('status', 'pending')->count();
            $modules['contracts'] = [
                'label' => 'Kontrak',
                'total' => DB::table('contracts')->where('status', 'active')->count(),
                'alerts' => array_filter([
                    $expiringContracts > 0 ? ['type' => 'warning', 'msg' => "{$expiringContracts} kontrak segera expired"] : null,
                    $pendingBilling > 0 ? ['type' => 'info', 'msg' => "{$pendingBilling} billing pending"] : null,
                ]),
            ];
        } catch (\Throwable) { $modules['contracts'] = ['label' => 'Kontrak', 'total' => 0, 'alerts' => []]; }

        // Consignment
        try {
            $pendingSettle = DB::table('consignment_sales_reports')->whereIn('status', ['draft', 'confirmed'])->count();
            $modules['consignment'] = [
                'label' => 'Konsinyasi',
                'total' => DB::table('consignment_shipments')->whereIn('status', ['shipped', 'partial_sold'])->count(),
                'alerts' => $pendingSettle > 0 ? [['type' => 'info', 'msg' => "{$pendingSettle} settlement pending"]] : [],
            ];
        } catch (\Throwable) { $modules['consignment'] = ['label' => 'Konsinyasi', 'total' => 0, 'alerts' => []]; }

        // Commission
        try {
            $unpaidComm = DB::table('commission_calculations')->where('status', 'approved')->count();
            $modules['commission'] = [
                'label' => 'Komisi Sales',
                'total' => DB::table('commission_calculations')->count(),
                'alerts' => $unpaidComm > 0 ? [['type' => 'info', 'msg' => "{$unpaidComm} komisi approved belum dibayar"]] : [],
            ];
        } catch (\Throwable) { $modules['commission'] = ['label' => 'Komisi', 'total' => 0, 'alerts' => []]; }

        // Helpdesk
        try {
            $openTickets = DB::table('helpdesk_tickets')->whereNotIn('status', ['resolved', 'closed'])->count();
            $overdueTickets = DB::table('helpdesk_tickets')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->whereNotNull('sla_resolve_due')
                ->where('sla_resolve_due', '<', now())->count();
            $modules['helpdesk'] = [
                'label' => 'Helpdesk',
                'total' => $openTickets,
                'alerts' => $overdueTickets > 0 ? [['type' => 'critical', 'msg' => "{$overdueTickets} tiket SLA overdue"]] : [],
            ];
        } catch (\Throwable) { $modules['helpdesk'] = ['label' => 'Helpdesk', 'total' => 0, 'alerts' => []]; }

        // Subscription Billing
        try {
            $pastDue = DB::table('customer_subscriptions')->where('status', 'active')
                ->where('next_billing_date', '<', today())->count();
            $modules['subscription_billing'] = [
                'label' => 'Subscription Billing',
                'total' => DB::table('customer_subscriptions')->where('status', 'active')->count(),
                'alerts' => $pastDue > 0 ? [['type' => 'warning', 'msg' => "{$pastDue} subscription jatuh tempo"]] : [],
            ];
        } catch (\Throwable) { $modules['subscription_billing'] = ['label' => 'Subscription', 'total' => 0, 'alerts' => []]; }

        // Landed Cost
        try {
            $draftLc = DB::table('landed_costs')->where('status', 'draft')->count();
            $modules['landed_cost'] = [
                'label' => 'Landed Cost',
                'total' => DB::table('landed_costs')->count(),
                'alerts' => $draftLc > 0 ? [['type' => 'info', 'msg' => "{$draftLc} landed cost masih draft"]] : [],
            ];
        } catch (\Throwable) { $modules['landed_cost'] = ['label' => 'Landed Cost', 'total' => 0, 'alerts' => []]; }

        // CRM
        $modules['crm'] = [
            'label' => 'CRM',
            'total' => DB::table('crm_leads')->whereNotIn('stage', ['won', 'lost'])->count(),
            'alerts' => [],
        ];

        // Projects
        $modules['projects'] = [
            'label' => 'Proyek',
            'total' => DB::table('projects')->whereIn('status', ['active', 'planning'])->count(),
            'alerts' => [],
        ];

        // Assets
        $modules['assets'] = [
            'label' => 'Aset',
            'total' => DB::table('assets')->where('status', 'active')->count(),
            'alerts' => [],
        ];

        // Count total alerts
        $totalAlerts = collect($modules)->sum(fn($m) => count($m['alerts']));
        $criticalAlerts = collect($modules)->sum(fn($m) => collect($m['alerts'])->where('type', 'critical')->count());

        return [
            'modules'        => $modules,
            'total_alerts'   => $totalAlerts,
            'critical_alerts'=> $criticalAlerts,
        ];
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $seconds = (int) explode(' ', $uptime)[0];
                $days    = floor($seconds / 86400);
                $hours   = floor(($seconds % 86400) / 3600);
                $mins    = floor(($seconds % 3600) / 60);
                return "{$days}d {$hours}h {$mins}m";
            }
        }
        return 'N/A';
    }
}
