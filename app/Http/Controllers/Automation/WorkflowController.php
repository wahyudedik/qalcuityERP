<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowExecutionLog;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = $this->workflowEngine->getStatistics(auth()->user()->tenant_id);

        $recentLogs = WorkflowExecutionLog::where('tenant_id', auth()->user()->tenant_id)
            ->with('workflow')
            ->latest('started_at')
            ->take(20)
            ->get();

        return view('automation.dashboard', compact('stats', 'recentLogs'));
    }

    /**
     * List workflows
     */
    public function index()
    {
        $workflows = Workflow::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('actions')
            ->withCount([
                'logs as logs_today_count' => function ($query) {
                    $query->whereDate('started_at', today());
                },
            ])
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return view('automation.workflows.index', compact('workflows'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('automation.workflows.create');
    }

    /**
     * Store workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:event,schedule,condition',
            'trigger_config' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:100',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['created_by'] = auth()->id();

        $workflow = Workflow::create($validated);

        return redirect()->route('automation.workflows.show', $workflow)
            ->with('success', 'Workflow berhasil dibuat');
    }

    /**
     * Show workflow details
     */
    public function show(Workflow $workflow)
    {
        $this->authorize('view', $workflow);

        $actions = $workflow->actions()->orderBy('order')->get();
        $logs = $workflow->logs()->latest('started_at')->take(50)->get();

        return view('automation.workflows.show', compact('workflow', 'actions', 'logs'));
    }

    /**
     * Update workflow
     */
    public function update(Request $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_config' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:100',
        ]);

        $workflow->update($validated);

        return redirect()->route('automation.workflows.show', $workflow)
            ->with('success', 'Workflow berhasil diupdate');
    }

    /**
     * Delete workflow
     */
    public function destroy(Workflow $workflow)
    {
        $this->authorize('delete', $workflow);

        $workflow->delete();

        return redirect()->route('automation.workflows.index')
            ->with('success', 'Workflow berhasil dihapus');
    }

    /**
     * Toggle workflow active status
     */
    public function toggle(Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $workflow->update(['is_active' => ! $workflow->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $workflow->is_active,
        ]);
    }

    /**
     * Test workflow execution
     */
    public function test(Workflow $workflow)
    {
        $this->authorize('view', $workflow);

        $result = $this->workflowEngine->testWorkflow($workflow, [
            'product_id' => 1,
            'stock_quantity' => 5,
            'minimum_stock' => 10,
            'employee_id' => 1,
            'sales_amount' => 50000000,
        ]);

        return response()->json($result);
    }

    /**
     * View execution logs
     */
    public function logs(Workflow $workflow, Request $request)
    {
        $this->authorize('view', $workflow);

        $query = $workflow->logs()->latest('started_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return view('automation.workflows.logs', compact('workflow', 'logs'));
    }

    /**
     * Add action to workflow
     */
    public function addAction(Request $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $validated = $request->validate([
            'action_type' => 'required|string',
            'action_config' => 'required|array',
            'order' => 'required|integer|min:0',
            'condition' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['workflow_id'] = $workflow->id;

        WorkflowAction::create($validated);

        return response()->json(['success' => true, 'message' => 'Action added']);
    }

    /**
     * Update action
     */
    public function updateAction(Request $request, WorkflowAction $action)
    {
        $this->authorize('update', $action->workflow);

        $validated = $request->validate([
            'action_config' => 'required|array',
            'condition' => 'nullable|array',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $action->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Delete action
     */
    public function deleteAction(WorkflowAction $action)
    {
        $this->authorize('update', $action->workflow);

        $action->delete();

        return response()->json(['success' => true]);
    }
}
