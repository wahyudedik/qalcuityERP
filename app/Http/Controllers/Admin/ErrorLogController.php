<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Services\ErrorAlertingService;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    /**
     * Display error logs dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'level' => $request->get('level'),
            'tenant_id' => $request->get('tenant_id'),
            'is_resolved' => $request->get('is_resolved'),
            'search' => $request->get('search'),
            'exception_class' => $request->get('exception_class'),
        ];

        // Build query
        $query = ErrorLog::with(['tenant', 'user', 'resolver'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($filters['level']) {
            $query->where('level', $filters['level']);
        }

        if ($filters['tenant_id']) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if ($filters['is_resolved'] !== null) {
            $query->where('is_resolved', $filters['is_resolved'] === 'true');
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('message', 'like', "%{$filters['search']}%")
                    ->orWhere('exception_class', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['exception_class']) {
            $query->where('exception_class', $filters['exception_class']);
        }

        // Paginate results
        $errorLogs = $query->paginate(50);

        // Get statistics
        $stats = $this->getStatistics();

        // Get unique exception classes for filter
        $exceptionClasses = ErrorLog::selectRaw('DISTINCT exception_class')
            ->whereNotNull('exception_class')
            ->pluck('exception_class');

        return view('admin.error-logs.index', compact('errorLogs', 'stats', 'exceptionClasses', 'filters'));
    }

    /**
     * Display specific error log details
     */
    public function show(ErrorLog $errorLog)
    {
        $errorLog->load(['tenant', 'user', 'resolver']);

        // Get similar errors
        $similarErrors = ErrorLog::where('exception_class', $errorLog->exception_class)
            ->where('id', '!=', $errorLog->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.error-logs.show', compact('errorLog', 'similarErrors'));
    }

    /**
     * Mark error as resolved
     */
    public function resolve(Request $request, ErrorLog $errorLog)
    {
        $data = $request->validate([
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $errorLog->resolve(
            userId: auth()->id(),
            notes: $data['resolution_notes'] ?? null
        );

        return redirect()->route('admin.error-logs.show', $errorLog)
            ->with('success', 'Error marked as resolved.');
    }

    /**
     * Bulk resolve errors
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'error_ids' => 'required|array',
            'error_ids.*' => 'exists:error_logs,id',
            'resolution_notes' => 'nullable|string',
        ]);

        $count = 0;
        foreach ($request->error_ids as $errorId) {
            $errorLog = ErrorLog::find($errorId);
            if ($errorLog && ! $errorLog->is_resolved) {
                $errorLog->resolve(auth()->id(), $request->resolution_notes);
                $count++;
            }
        }

        return redirect()->route('admin.error-logs.index')
            ->with('success', "{$count} errors marked as resolved.");
    }

    /**
     * Test alert system
     */
    public function testAlert()
    {
        $alertingService = app(ErrorAlertingService::class);

        try {
            $alertingService->testAlert();

            return back()->with('success', 'Test alert sent successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test alert: '.$e->getMessage());
        }
    }

    /**
     * Get error statistics
     */
    protected function getStatistics(): array
    {
        $now = now();

        return [
            'total' => ErrorLog::count(),
            'unresolved' => ErrorLog::where('is_resolved', false)->count(),
            'critical_24h' => ErrorLog::critical()
                ->where('created_at', '>=', $now->subHours(24))
                ->count(),
            'not_notified' => ErrorLog::where('notified', false)
                ->whereIn('level', ['emergency', 'alert', 'critical', 'error'])
                ->count(),
            'top_exceptions' => ErrorLog::selectRaw('exception_class, COUNT(*) as count')
                ->groupBy('exception_class')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_level' => [
                'emergency' => ErrorLog::where('level', 'emergency')->count(),
                'alert' => ErrorLog::where('level', 'alert')->count(),
                'critical' => ErrorLog::where('level', 'critical')->count(),
                'error' => ErrorLog::where('level', 'error')->count(),
                'warning' => ErrorLog::where('level', 'warning')->count(),
            ],
            'recent_trend' => [
                'today' => ErrorLog::whereDate('created_at', today())->count(),
                'yesterday' => ErrorLog::whereDate('created_at', today()->subDay())->count(),
            ],
        ];
    }
}
