<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Timesheet::where('tenant_id', $tenantId)
            ->with(['project', 'user'])
            ->latest('date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('user_id') && auth()->user()->hasRole(['admin', 'manager'])) {
            $query->where('user_id', $request->user_id);
        } elseif (! auth()->user()->hasRole(['admin', 'manager'])) {
            $query->where('user_id', auth()->id());
        }
        if ($request->filled('month')) {
            $query->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$request->month]);
        }

        $timesheets = $query->paginate(25)->withQueryString();

        $projects = Project::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'planning'])
            ->orderBy('name')
            ->get();

        $users = auth()->user()->hasRole(['admin', 'manager'])
            ? User::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $totalHours = $query->sum('hours');
        $totalCost = Timesheet::where('tenant_id', $tenantId)
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->selectRaw('SUM(hours * hourly_rate) as total')
            ->value('total') ?? 0;

        return view('timesheets.index', compact('timesheets', 'projects', 'users', 'totalHours', 'totalCost'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'date' => 'required|date|before_or_equal:today',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'required|string|max:500',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Pastikan project milik tenant ini
        $project = Project::where('tenant_id', $tenantId)->findOrFail($data['project_id']);

        Timesheet::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'project_id' => $project->id,
            'date' => $data['date'],
            'hours' => $data['hours'],
            'description' => $data['description'],
            'hourly_rate' => $data['hourly_rate'] ?? 0,
        ]);

        return back()->with('success', 'Timesheet berhasil dicatat.');
    }

    public function destroy(Timesheet $timesheet)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_if($timesheet->tenant_id !== $tenantId, 403);

        // Staff hanya bisa hapus milik sendiri
        if (! auth()->user()->hasRole(['admin', 'manager'])) {
            abort_if($timesheet->user_id !== auth()->id(), 403);
        }

        $timesheet->delete();

        return back()->with('success', 'Entri timesheet dihapus.');
    }
}
