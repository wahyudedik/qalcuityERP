<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectExpense;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Project::where('tenant_id', $this->tid())->with(['customer', 'tasks']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('number', 'like', "%$s%"));
        }

        $projects  = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $customers = Customer::where('tenant_id', $this->tid())->orderBy('name')->get();
        $users     = User::where('tenant_id', $this->tid())->where('is_active', true)->get();

        $stats = [
            'total'     => Project::where('tenant_id', $this->tid())->count(),
            'active'    => Project::where('tenant_id', $this->tid())->where('status', 'active')->count(),
            'completed' => Project::where('tenant_id', $this->tid())->where('status', 'completed')->count(),
            'overdue'   => Project::where('tenant_id', $this->tid())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->where('end_date', '<', today())->count(),
        ];

        return view('projects.index', compact('projects', 'customers', 'users', 'stats'));
    }

    public function show(Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);
        $project->load(['tasks.assignedTo', 'expenses', 'timesheets.user', 'customer']);
        $users = User::where('tenant_id', $this->tid())->where('is_active', true)->get();
        return view('projects.show', compact('project', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'type'        => 'nullable|string|max:100',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'tenant_id' => $this->tid(),
            'user_id'   => auth()->id(),
            'number'    => 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'status'    => 'planning',
            'progress'  => 0,
            'actual_cost' => 0,
        ] + $data);

        return redirect()->route('projects.show', $project)->with('success', "Proyek {$project->name} berhasil dibuat.");
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'status'      => 'required|in:planning,active,on_hold,completed,cancelled',
            'customer_id' => 'nullable|exists:customers,id',
            'type'        => 'nullable|string|max:100',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'budget'      => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $project->update($data);
        return back()->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    // ── Tasks ──────────────────────────────────────────────────────────────

    public function storeTask(Request $request, Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
            'weight'      => 'nullable|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        ProjectTask::create([
            'project_id' => $project->id,
            'tenant_id'  => $this->tid(),
            'status'     => 'todo',
            'weight'     => $data['weight'] ?? 1,
        ] + $data);

        $project->recalculateProgress();
        return back()->with('success', 'Task berhasil ditambahkan.');
    }

    public function updateTaskStatus(Request $request, ProjectTask $task)
    {
        abort_unless($task->tenant_id === $this->tid(), 403);

        $request->validate(['status' => 'required|in:todo,in_progress,review,done,cancelled']);
        $task->update(['status' => $request->status]);
        $task->project->recalculateProgress();

        return response()->json(['ok' => true, 'progress' => $task->project->fresh()->progress]);
    }

    public function destroyTask(ProjectTask $task)
    {
        abort_unless($task->tenant_id === $this->tid(), 403);
        $project = $task->project;
        $task->delete();
        $project->recalculateProgress();
        return back()->with('success', 'Task dihapus.');
    }

    // ── Expenses ───────────────────────────────────────────────────────────

    public function storeExpense(Request $request, Project $project)
    {
        abort_unless($project->tenant_id === $this->tid(), 403);

        $data = $request->validate([
            'category'    => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
        ]);

        ProjectExpense::create([
            'project_id' => $project->id,
            'tenant_id'  => $this->tid(),
            'user_id'    => auth()->id(),
        ] + $data);

        $project->recalculateActualCost();
        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }
}
