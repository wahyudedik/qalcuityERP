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

        return view('super-admin.monitoring.index', compact(
            'tab', 'errors', 'errorStats',
            'aiUsage', 'aiMonthly', 'aiStats',
            'activities', 'anomalies', 'anomalyStats',
            'health', 'tenants'
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
