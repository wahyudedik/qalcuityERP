<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with(['user']);

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $users = \App\Models\User::select('id', 'name')->get();

        $statistics = [
            'total_logs' => AuditTrail::count(),
            'today' => AuditTrail::whereDate('created_at', today())->count(),
            'this_week' => AuditTrail::where('created_at', '>=', now()->startOfWeek())->count(),
            'critical_actions' => AuditTrail::where('severity', 'critical')->count(),
        ];

        return view('healthcare.audit-trail.index', compact('logs', 'users', 'statistics'));
    }

    public function show(AuditTrail $log)
    {
        $log->load(['user']);
        return view('healthcare.audit-trail.show', compact('log'));
    }

    public function filter(Request $request)
    {
        $query = AuditTrail::with(['user']);

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->latest()->limit(100)->get();

        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function export(Request $request)
    {
        $query = AuditTrail::with(['user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Audit trail exported successfully',
        ]);
    }
}
