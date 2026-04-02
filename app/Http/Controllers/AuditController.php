<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $logs = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('is_ai'), fn($q) => $q->where('is_ai_action', (bool) $request->is_ai))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->module, fn($q) => $q->where('model_type', 'like', '%' . $request->module))
            ->when($request->search, fn($q) => $q->where('description', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $actions = ActivityLog::where('tenant_id', $tenantId)
            ->distinct()->pluck('action')->sort()->values();

        $users = \App\Models\User::where('tenant_id', $tenantId)
            ->orderBy('name')->get(['id', 'name', 'role']);

        $modules = ActivityLog::where('tenant_id', $tenantId)
            ->whereNotNull('model_type')
            ->distinct()
            ->pluck('model_type')
            ->map(fn($m) => class_basename($m))
            ->unique()
            ->sort()
            ->values();

        $aiCount = ActivityLog::where('tenant_id', $tenantId)
            ->where('is_ai_action', true)
            ->whereDate('created_at', today())
            ->count();

        $retentionDays = config('audit.retention_days', 365);
        $totalLogs     = ActivityLog::where('tenant_id', $tenantId)->count();
        $oldestLog     = ActivityLog::where('tenant_id', $tenantId)->oldest()->first()?->created_at;

        return view('audit.index', compact(
            'logs',
            'actions',
            'users',
            'modules',
            'aiCount',
            'retentionDays',
            'totalLogs',
            'oldestLog'
        ));
    }

    /**
     * AJAX: Detail view for a single audit log entry.
     */
    public function show(ActivityLog $activityLog)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_if($activityLog->tenant_id !== $tenantId, 403);

        $activityLog->load(['user', 'rolledBackByUser']);

        // Build timeline: related logs for the same model
        $timeline = [];
        if ($activityLog->model_type && $activityLog->model_id) {
            $timeline = ActivityLog::where('tenant_id', $tenantId)
                ->where('model_type', $activityLog->model_type)
                ->where('model_id', $activityLog->model_id)
                ->with('user')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'log' => [
                'id'             => $activityLog->id,
                'action'         => $activityLog->action,
                'description'    => $activityLog->description,
                'model_type'     => $activityLog->model_type ? class_basename($activityLog->model_type) : null,
                'model_id'       => $activityLog->model_id,
                'user_name'      => $activityLog->user?->name ?? 'System',
                'user_role'      => $activityLog->user?->role,
                'ip_address'     => $activityLog->ip_address,
                'user_agent'     => $activityLog->user_agent,
                'is_ai_action'   => $activityLog->is_ai_action,
                'ai_tool_name'   => $activityLog->ai_tool_name,
                'old_values'     => $activityLog->old_values,
                'new_values'     => $activityLog->new_values,
                'is_rollbackable' => $activityLog->isRollbackable(),
                'rolled_back_at' => $activityLog->rolled_back_at?->format('d/m/Y H:i'),
                'rolled_back_by' => $activityLog->rolledBackByUser?->name,
                'created_at'     => $activityLog->created_at->format('d/m/Y H:i:s'),
                'ago'            => $activityLog->created_at->diffForHumans(),
            ],
            'timeline' => $timeline->map(fn($t) => [
                'id'        => $t->id,
                'action'    => $t->action,
                'user_name' => $t->user?->name ?? 'System',
                'created_at' => $t->created_at->format('d/m H:i'),
                'has_diff'  => !empty($t->old_values) || !empty($t->new_values),
                'is_current' => $t->id === $activityLog->id,
            ]),
        ]);
    }

    /**
     * POST: Rollback a specific audit log entry.
     */
    public function rollback(Request $request, ActivityLog $activityLog)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_if($activityLog->tenant_id !== $tenantId, 403);

        if (!config('audit.rollback_enabled', true)) {
            return response()->json(['ok' => false, 'message' => 'Rollback dinonaktifkan oleh administrator.'], 403);
        }

        if (!$activityLog->isRollbackable()) {
            return response()->json(['ok' => false, 'message' => 'Entry ini tidak dapat di-rollback.'], 422);
        }

        $success = $activityLog->rollback(auth()->id());

        if (!$success) {
            return response()->json(['ok' => false, 'message' => 'Rollback gagal. Model mungkin sudah dihapus.'], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Rollback berhasil. Perubahan telah dikembalikan.',
        ]);
    }

    /**
     * GET: Export audit logs as CSV.
     */
    public function export(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->module, fn($q) => $q->where('model_type', 'like', '%' . $request->module))
            ->latest();

        $filename = 'audit_trail_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'User', 'Role', 'Aksi', 'Modul', 'ID', 'Deskripsi', 'IP', 'AI?', 'Rolled Back?']);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'System',
                        $log->user?->role ?? '-',
                        $log->action,
                        $log->model_type ? class_basename($log->model_type) : '-',
                        $log->model_id ?? '-',
                        $log->description,
                        $log->ip_address ?? '-',
                        $log->is_ai_action ? 'Ya' : 'Tidak',
                        $log->rolled_back_at ? 'Ya (' . $log->rolled_back_at->format('d/m/Y') . ')' : 'Tidak',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
