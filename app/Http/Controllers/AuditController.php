<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @property User $user
 */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

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
        $totalLogs = ActivityLog::where('tenant_id', $tenantId)->count();
        $oldestLog = ActivityLog::where('tenant_id', $tenantId)->oldest()->first()?->created_at;

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
        $tenantId = Auth::user()->tenant_id;
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
                'id' => $activityLog->id,
                'action' => $activityLog->action,
                'description' => $activityLog->description,
                'model_type' => $activityLog->model_type ? class_basename($activityLog->model_type) : null,
                'model_id' => $activityLog->model_id,
                'user_name' => $activityLog->user?->name ?? 'System',
                'user_role' => $activityLog->user?->role,
                'ip_address' => $activityLog->ip_address,
                'user_agent' => $activityLog->user_agent,
                'is_ai_action' => $activityLog->is_ai_action,
                'ai_tool_name' => $activityLog->ai_tool_name,
                'old_values' => $activityLog->old_values,
                'new_values' => $activityLog->new_values,
                'is_rollbackable' => $activityLog->isRollbackable(),
                'rolled_back_at' => $activityLog->rolled_back_at?->format('d/m/Y H:i'),
                'rolled_back_by' => $activityLog->rolledBackByUser?->name,
                'created_at' => $activityLog->created_at->format('d/m/Y H:i:s'),
                'ago' => $activityLog->created_at->diffForHumans(),
            ],
            'timeline' => $timeline->map(fn($t) => [
                'id' => $t->id,
                'action' => $t->action,
                'user_name' => $t->user?->name ?? 'System',
                'created_at' => $t->created_at->format('d/m H:i'),
                'has_diff' => !empty($t->old_values) || !empty($t->new_values),
                'is_current' => $t->id === $activityLog->id,
            ]),
        ]);
    }

    /**
     * POST: Rollback a specific audit log entry.
     */
    public function rollback(Request $request, ActivityLog $activityLog)
    {
        $user = Auth::user();
        abort_if($activityLog->tenant_id !== $user->tenant_id, 403);

        // Only admins and managers may roll back
        if (!in_array($user->role, ['admin', 'manager', 'super_admin'])) {
            return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki izin untuk melakukan rollback.'], 403);
        }

        if (!config('audit.rollback_enabled', true)) {
            return response()->json(['ok' => false, 'message' => 'Rollback dinonaktifkan oleh administrator.'], 403);
        }

        if (!$activityLog->isRollbackable()) {
            return response()->json(['ok' => false, 'message' => 'Entry ini tidak dapat di-rollback.'], 422);
        }

        // Pass force=1 to skip conflict warning
        $force = (bool) $request->input('force', false);
        $result = $activityLog->rollback($user->id);

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'message' => $result['message']], 422);
        }

        // If there were conflicts and caller didn't force, return a warning first
        if (!empty($result['conflicts']) && !$force) {
            return response()->json([
                'ok' => false,
                'conflict' => true,
                'message' => 'Perhatian: field berikut telah diubah setelah log ini dicatat. Rollback tetap berhasil, tetapi perubahan terbaru telah ditimpa.',
                'conflicts' => $result['conflicts'],
                // Re-confirm token: client should call again with force=1
            ], 409);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Rollback berhasil. Perubahan telah dikembalikan.',
            'conflicts' => $result['conflicts'],
        ]);
    }

    /**
     * GET: Export audit logs as CSV.
     */
    public function export(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with('user')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->module, fn($q) => $q->where('model_type', 'like', '%' . $request->module))
            ->latest();

        // Support both CSV and Excel export
        $format = $request->get('format', 'csv');

        if ($format === 'xlsx') {
            return $this->exportExcel($query);
        }

        return $this->exportCsv($query);
    }

    /**
     * Export audit logs as CSV.
     */
    protected function exportCsv($query)
    {
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

    /**
     * Export audit logs as Excel (XLSX).
     * TASK-022: Enhanced export with formatting.
     */
    protected function exportExcel($query)
    {
        $filename = 'audit_trail_' . now()->format('Y-m-d_His') . '.xlsx';

        // Use Maatwebsite Excel package (already installed)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Waktu', 'User', 'Role', 'Aksi', 'Modul', 'ID', 'Deskripsi', 'IP', 'AI?', 'Rolled Back?'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF3B82F6');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Add data
        $row = 2;
        $query->chunk(500, function ($logs) use ($sheet, &$row) {
            foreach ($logs as $log) {
                $sheet->setCellValue('A' . $row, $log->created_at->format('Y-m-d H:i:s'));
                $sheet->setCellValue('B' . $row, $log->user?->name ?? 'System');
                $sheet->setCellValue('C' . $row, $log->user?->role ?? '-');
                $sheet->setCellValue('D' . $row, $log->action);
                $sheet->setCellValue('E' . $row, $log->model_type ? class_basename($log->model_type) : '-');
                $sheet->setCellValue('F' . $row, $log->model_id ?? '-');
                $sheet->setCellValue('G' . $row, $log->description);
                $sheet->setCellValue('H' . $row, $log->ip_address ?? '-');
                $sheet->setCellValue('I' . $row, $log->is_ai_action ? 'Ya' : 'Tidak');
                $sheet->setCellValue('J' . $row, $log->rolled_back_at ? 'Ya' : 'Tidak');

                // Highlight AI actions
                if ($log->is_ai_action) {
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF3E8FF');
                }

                // Highlight rolled back
                if ($log->rolled_back_at) {
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFF3CD');
                }

                $row++;
            }
        });

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Write to output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * GET: Export compliance report as a SOX-style CSV.
     *
     * Covers the full audit trail for a given date range, grouped by user and module.
     * Suitable for hand-off to an external auditor or compliance officer.
     */
    public function complianceReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Only admins / managers may generate compliance reports
        abort_if(!in_array($user->role, ['admin', 'manager', 'super_admin']), 403);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $query = ActivityLog::where('tenant_id', $tenantId)
            ->with(['user', 'rolledBackByUser'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at');

        // Stats for report header
        $totalCount = (clone $query)->count();
        $aiCount = (clone $query)->where('is_ai_action', true)->count();
        $rbCount = (clone $query)->whereNotNull('rolled_back_at')->count();
        $uniqueUsers = (clone $query)->distinct()->pluck('user_id')->count();

        $filename = 'compliance_report_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('His') . '.csv';

        return response()->streamDownload(function () use ($query, $dateFrom, $dateTo, $totalCount, $aiCount, $rbCount, $uniqueUsers) {
            $handle = fopen('php://output', 'w');

            // ── Report header ──────────────────────────────────────────────
            fputcsv($handle, ['QALCUITY ERP - AUDIT COMPLIANCE REPORT']);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Period', $dateFrom . ' to ' . $dateTo]);
            fputcsv($handle, ['Total Events', $totalCount]);
            fputcsv($handle, ['Unique Users', $uniqueUsers]);
            fputcsv($handle, ['AI-Generated Actions', $aiCount]);
            fputcsv($handle, ['Rolled-Back Events', $rbCount]);
            fputcsv($handle, ['Standard', 'SOX / COSO Internal Control Framework']);
            fputcsv($handle, []);

            // ── Column headers ─────────────────────────────────────────────
            fputcsv($handle, [
                'Event #',
                'Timestamp (UTC)',
                'Date',
                'Time',
                'User ID',
                'User Name',
                'User Role',
                'IP Address',
                'Action Type',
                'Module',
                'Record ID',
                'Description',
                'Fields Changed',
                'Old Values (JSON)',
                'New Values (JSON)',
                'AI Generated?',
                'AI Tool',
                'Rolled Back?',
                'Rolled Back At',
                'Rolled Back By',
                'Integrity Hash',
            ]);

            $seq = 0;
            $query->chunk(500, function ($logs) use ($handle, &$seq) {
                foreach ($logs as $log) {
                    $seq++;

                    $oldJson = $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '';
                    $newJson = $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '';

                    // List changed field names
                    $fieldsChanged = '';
                    if ($log->old_values && $log->new_values) {
                        $allKeys = array_unique(array_merge(array_keys($log->old_values), array_keys($log->new_values)));
                        $changed = array_filter(
                            $allKeys,
                            fn($k) => (string) ($log->old_values[$k] ?? '') !== (string) ($log->new_values[$k] ?? '')
                        );
                        $fieldsChanged = implode(', ', $changed);
                    }

                    // Integrity hash: deterministic fingerprint for tamper detection
                    $integrityHash = hash(
                        'sha256',
                        $log->id . '|' .
                        $log->tenant_id . '|' .
                        $log->user_id . '|' .
                        $log->action . '|' .
                        $log->created_at->toIso8601String() . '|' .
                        $oldJson . '|' .
                        $newJson
                    );

                    fputcsv($handle, [
                        $seq,
                        $log->created_at->toIso8601String(),
                        $log->created_at->format('Y-m-d'),
                        $log->created_at->format('H:i:s'),
                        $log->user_id ?? 'system',
                        $log->user?->name ?? 'System',
                        $log->user?->role ?? '-',
                        $log->ip_address ?? '-',
                        $log->action,
                        $log->model_type ? class_basename($log->model_type) : '-',
                        $log->model_id ?? '-',
                        $log->description,
                        $fieldsChanged,
                        $oldJson,
                        $newJson,
                        $log->is_ai_action ? 'YES' : 'NO',
                        $log->ai_tool_name ?? '-',
                        $log->rolled_back_at ? 'YES' : 'NO',
                        $log->rolled_back_at?->format('Y-m-d H:i:s') ?? '-',
                        $log->rolledBackByUser?->name ?? '-',
                        $integrityHash,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
