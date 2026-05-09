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
            'total' => ErrorLog::count(),
            'unresolved' => ErrorLog::where('is_resolved', false)->count(),
            'today' => ErrorLog::whereDate('created_at', today())->count(),
            'critical' => ErrorLog::where('level', 'critical')->where('is_resolved', false)->count(),
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
            'total_tokens' => AiUsageLog::where('month', $currentMonth)->sum('token_count'),
            'active_tenants' => AiUsageLog::where('month', $currentMonth)->distinct('tenant_id')->count('tenant_id'),
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
            'open' => AnomalyAlert::where('status', 'open')->count(),
            'critical' => AnomalyAlert::where('status', 'open')->where('severity', 'critical')->count(),
            'warning' => AnomalyAlert::where('status', 'open')->where('severity', 'warning')->count(),
        ];

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        // ── Module Health (cross-module metrics) ──────────────────
        try {
            $moduleHealth = $this->getModuleHealth();
        } catch (\Throwable) {
            $moduleHealth = ['modules' => [], 'total_alerts' => 0, 'critical_alerts' => 0];
        }

        return view('super-admin.monitoring.index', compact(
            'tab',
            'errors',
            'errorStats',
            'aiUsage',
            'aiMonthly',
            'aiStats',
            'activities',
            'anomalies',
            'anomalyStats',
            'health',
            'tenants',
            'moduleHealth'
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
        $diskFree = disk_free_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskPct = $diskTotal > 0 ? round($diskUsed / $diskTotal * 100, 1) : 0;

        $logPath = storage_path('logs/laravel.log');
        $logSize = file_exists($logPath) ? filesize($logPath) : 0;

        return [
            'db_ok' => $dbOk,
            'db_latency_ms' => $dbLatency,
            'disk_total_gb' => round($diskTotal / 1073741824, 1),
            'disk_free_gb' => round($diskFree / 1073741824, 1),
            'disk_used_pct' => $diskPct,
            'log_size_mb' => round($logSize / 1048576, 2),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_users' => User::count(),
            'errors_today' => ErrorLog::whereDate('created_at', today())->count(),
            'queue_failed' => $this->getFailedJobsCount(),
            'uptime' => $this->getUptime(),
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
     *
     * All counts are fetched in TWO consolidated UNION ALL queries (core + optional tables)
     * instead of 30+ sequential round-trips, eliminating the 30-second timeout.
     */
    private function getModuleHealth(): array
    {
        // ── Step 1: Core tables – all guaranteed to exist ─────────────────────
        $c = [];
        try {
            $rows = DB::select("
                SELECT 'sales_total'               AS k, COUNT(*)  AS v FROM sales_orders
                UNION ALL
                SELECT 'sales_today',                  COUNT(*)        FROM sales_orders       WHERE DATE(created_at) = CURDATE()
                UNION ALL
                SELECT 'invoices_total',               COUNT(*)        FROM invoices
                UNION ALL
                SELECT 'invoices_overdue',             COUNT(*)        FROM invoices            WHERE status IN ('unpaid','partial') AND due_date < CURDATE()
                UNION ALL
                SELECT 'products_active',              COUNT(*)        FROM products            WHERE is_active = 1
                UNION ALL
                SELECT 'products_low_stock',           COUNT(*)        FROM products p
                    JOIN product_stocks ps ON p.id = ps.product_id
                    WHERE ps.quantity <= p.stock_min AND p.is_active = 1
                UNION ALL
                SELECT 'purchase_orders_total',        COUNT(*)        FROM purchase_orders
                UNION ALL
                SELECT 'journal_entries_total',        COUNT(*)        FROM journal_entries
                UNION ALL
                SELECT 'journal_entries_old_draft',    COUNT(*)        FROM journal_entries     WHERE status = 'draft' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                UNION ALL
                SELECT 'employees_active',             COUNT(*)        FROM employees           WHERE status = 'active'
                UNION ALL
                SELECT 'work_orders_total',            COUNT(*)        FROM work_orders
                UNION ALL
                SELECT 'work_orders_stuck',            COUNT(*)        FROM work_orders         WHERE status = 'in_progress' AND started_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
                UNION ALL
                SELECT 'crm_leads_open',               COUNT(*)        FROM crm_leads           WHERE stage NOT IN ('won','lost')
                UNION ALL
                SELECT 'projects_active',              COUNT(*)        FROM projects            WHERE status IN ('active','planning')
                UNION ALL
                SELECT 'assets_active',                COUNT(*)        FROM assets              WHERE status = 'active'
            ");
            foreach ($rows as $row) {
                $c[$row->k] = (int) $row->v;
            }
        } catch (\Throwable) { /* leave $c empty; all values will default to 0 */
        }

        // ── Step 2: Optional / newer-module tables – each wrapped independently
        $o = [];
        $optSql = [
            'fleet_active' => 'SELECT COUNT(*) AS v FROM fleet_vehicles WHERE is_active = 1',
            'fleet_exp' => 'SELECT COUNT(*) AS v FROM fleet_vehicles WHERE is_active = 1 AND (registration_expiry BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 30 DAY) OR insurance_expiry BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 30 DAY))',
            'fleet_maint_due' => "SELECT COUNT(*) AS v FROM fleet_maintenances WHERE status = 'scheduled' AND scheduled_date <= DATE_ADD(NOW(),INTERVAL 7 DAY)",
            'contracts_active' => "SELECT COUNT(*) AS v FROM contracts WHERE status = 'active'",
            'contracts_exp' => "SELECT COUNT(*) AS v FROM contracts WHERE status = 'active' AND end_date BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 30 DAY)",
            'contract_billing' => "SELECT COUNT(*) AS v FROM contract_billings WHERE status = 'pending'",
            'consign_ship' => "SELECT COUNT(*) AS v FROM consignment_shipments WHERE status IN ('shipped','partial_sold')",
            'consign_settle' => "SELECT COUNT(*) AS v FROM consignment_sales_reports WHERE status IN ('draft','confirmed')",
            'commission_total' => 'SELECT COUNT(*) AS v FROM commission_calculations',
            'commission_unpaid' => "SELECT COUNT(*) AS v FROM commission_calculations WHERE status = 'approved'",
            'helpdesk_open' => "SELECT COUNT(*) AS v FROM helpdesk_tickets WHERE status NOT IN ('resolved','closed')",
            'helpdesk_overdue' => "SELECT COUNT(*) AS v FROM helpdesk_tickets WHERE status NOT IN ('resolved','closed') AND sla_resolve_due IS NOT NULL AND sla_resolve_due < NOW()",
            'subscr_active' => "SELECT COUNT(*) AS v FROM customer_subscriptions WHERE status = 'active'",
            'subscr_due' => "SELECT COUNT(*) AS v FROM customer_subscriptions WHERE status = 'active' AND next_billing_date < CURDATE()",
            'landed_total' => 'SELECT COUNT(*) AS v FROM landed_costs',
            'landed_draft' => "SELECT COUNT(*) AS v FROM landed_costs WHERE status = 'draft'",
        ];

        // Try one consolidated query; fall back to per-table on failure
        try {
            $unionParts = [];
            foreach ($optSql as $key => $subSql) {
                $unionParts[] = "SELECT '{$key}' AS k, ({$subSql}) AS v";
            }
            foreach (DB::select(implode(' UNION ALL ', $unionParts)) as $row) {
                $o[$row->k] = (int) $row->v;
            }
        } catch (\Throwable) {
            foreach ($optSql as $key => $subSql) {
                try {
                    $o[$key] = (int) (DB::selectOne($subSql)?->v ?? 0);
                } catch (\Throwable) {
                    $o[$key] = 0;
                }
            }
        }

        $g = fn (string $key): int => $c[$key] ?? 0;
        $f = fn (string $key): int => $o[$key] ?? 0;

        // ── Step 3: Assemble module array ─────────────────────────────────────
        $modules = [
            'sales' => [
                'label' => 'Penjualan',
                'total' => $g('sales_total'),
                'today' => $g('sales_today'),
                'alerts' => [],
            ],
            'invoices' => [
                'label' => 'Invoice',
                'total' => $g('invoices_total'),
                'alerts' => $g('invoices_overdue') > 0
                    ? [['type' => 'warning', 'msg' => $g('invoices_overdue').' invoice jatuh tempo']]
                    : [],
            ],
            'inventory' => [
                'label' => 'Inventori',
                'total' => $g('products_active'),
                'alerts' => $g('products_low_stock') > 0
                    ? [['type' => 'warning', 'msg' => $g('products_low_stock').' produk stok rendah']]
                    : [],
            ],
            'purchasing' => [
                'label' => 'Pembelian',
                'total' => $g('purchase_orders_total'),
                'alerts' => [],
            ],
            'accounting' => [
                'label' => 'Akuntansi / GL',
                'total' => $g('journal_entries_total'),
                'alerts' => $g('journal_entries_old_draft') > 0
                    ? [['type' => 'warning', 'msg' => $g('journal_entries_old_draft').' jurnal draft > 7 hari']]
                    : [],
            ],
            'hrm' => [
                'label' => 'SDM & Payroll',
                'total' => $g('employees_active'),
                'alerts' => [],
            ],
            'manufacturing' => [
                'label' => 'Manufaktur',
                'total' => $g('work_orders_total'),
                'alerts' => $g('work_orders_stuck') > 0
                    ? [['type' => 'warning', 'msg' => $g('work_orders_stuck').' WO in-progress > 30 hari']]
                    : [],
            ],
            'fleet' => [
                'label' => 'Fleet',
                'total' => $f('fleet_active'),
                'alerts' => array_values(array_filter([
                    $f('fleet_exp') > 0 ? ['type' => 'warning', 'msg' => $f('fleet_exp').' kendaraan STNK/asuransi segera expired'] : null,
                    $f('fleet_maint_due') > 0 ? ['type' => 'info', 'msg' => $f('fleet_maint_due').' maintenance terjadwal minggu ini'] : null,
                ])),
            ],
            'contracts' => [
                'label' => 'Kontrak',
                'total' => $f('contracts_active'),
                'alerts' => array_values(array_filter([
                    $f('contracts_exp') > 0 ? ['type' => 'warning', 'msg' => $f('contracts_exp').' kontrak segera expired'] : null,
                    $f('contract_billing') > 0 ? ['type' => 'info', 'msg' => $f('contract_billing').' billing pending'] : null,
                ])),
            ],
            'consignment' => [
                'label' => 'Konsinyasi',
                'total' => $f('consign_ship'),
                'alerts' => $f('consign_settle') > 0
                    ? [['type' => 'info', 'msg' => $f('consign_settle').' settlement pending']]
                    : [],
            ],
            'commission' => [
                'label' => 'Komisi Sales',
                'total' => $f('commission_total'),
                'alerts' => $f('commission_unpaid') > 0
                    ? [['type' => 'info', 'msg' => $f('commission_unpaid').' komisi approved belum dibayar']]
                    : [],
            ],
            'helpdesk' => [
                'label' => 'Helpdesk',
                'total' => $f('helpdesk_open'),
                'alerts' => $f('helpdesk_overdue') > 0
                    ? [['type' => 'critical', 'msg' => $f('helpdesk_overdue').' tiket SLA overdue']]
                    : [],
            ],
            'subscription_billing' => [
                'label' => 'Subscription Billing',
                'total' => $f('subscr_active'),
                'alerts' => $f('subscr_due') > 0
                    ? [['type' => 'warning', 'msg' => $f('subscr_due').' subscription jatuh tempo']]
                    : [],
            ],
            'landed_cost' => [
                'label' => 'Landed Cost',
                'total' => $f('landed_total'),
                'alerts' => $f('landed_draft') > 0
                    ? [['type' => 'info', 'msg' => $f('landed_draft').' landed cost masih draft']]
                    : [],
            ],
            'crm' => [
                'label' => 'CRM',
                'total' => $g('crm_leads_open'),
                'alerts' => [],
            ],
            'projects' => [
                'label' => 'Proyek',
                'total' => $g('projects_active'),
                'alerts' => [],
            ],
            'assets' => [
                'label' => 'Aset',
                'total' => $g('assets_active'),
                'alerts' => [],
            ],
        ];

        $totalAlerts = collect($modules)->sum(fn ($m) => count($m['alerts']));
        $criticalAlerts = collect($modules)->sum(fn ($m) => collect($m['alerts'])->where('type', 'critical')->count());

        return [
            'modules' => $modules,
            'total_alerts' => $totalAlerts,
            'critical_alerts' => $criticalAlerts,
        ];
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $seconds = (int) explode(' ', $uptime)[0];
                $days = floor($seconds / 86400);
                $hours = floor(($seconds % 86400) / 3600);
                $mins = floor(($seconds % 3600) / 60);

                return "{$days}d {$hours}h {$mins}m";
            }
        }

        return 'N/A';
    }
}
