<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\Manufacturing\ProductionSchedulingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Production Gantt Chart Controller
 *
 * TASK-2.15: Visual production scheduling with Gantt chart
 */
class ProductionGanttController extends Controller
{
    protected $schedulingService;

    public function __construct(ProductionSchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

    private function tid(): int
    {
        return Auth::user()->tenant_id;
    }

    /**
     * Display Gantt chart view
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $schedule = $this->schedulingService->getProductionSchedule(
            $this->tid(),
            $startDate,
            $endDate
        );

        $analytics = $this->schedulingService->getSchedulingAnalytics($this->tid(), 30);

        return view('production.gantt', compact('schedule', 'analytics', 'startDate', 'endDate'));
    }

    /**
     * Get Gantt chart data (API)
     */
    public function getData(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $schedule = $this->schedulingService->getProductionSchedule(
            $this->tid(),
            $startDate,
            $endDate
        );

        return response()->json($schedule);
    }

    /**
     * Schedule/Update work order
     */
    public function schedule(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after_or_equal:planned_start_date',
            'priority' => 'nullable|integer|min:1|max:4',
            'production_line' => 'nullable|string|max:255',
        ]);

        $result = $this->schedulingService->scheduleWorkOrder(
            $workOrder->id,
            $this->tid(),
            Carbon::parse($data['planned_start_date']),
            Carbon::parse($data['planned_end_date']),
            $data['priority'] ?? 3,
            $data['production_line'] ?? null
        );

        if ($result['success']) {
            return back()->with('success', 'Work Order scheduled successfully');
        }

        return back()->with('error', $result['message'])
            ->with('conflicts', $result['conflicts'] ?? []);
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectConflicts(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exclude_id' => 'nullable|integer',
        ]);

        $conflicts = $this->schedulingService->detectSchedulingConflicts(
            $this->tid(),
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date']),
            $data['exclude_id'] ?? null
        );

        return response()->json([
            'conflicts' => $conflicts,
            'has_conflicts' => ! empty($conflicts),
            'count' => count($conflicts),
        ]);
    }

    /**
     * Optimize production schedule
     */
    public function optimize()
    {
        $result = $this->schedulingService->optimizeSchedule($this->tid());

        return response()->json($result);
    }

    /**
     * Get capacity utilization
     */
    public function capacityUtilization(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->date)
            : now();

        $utilization = $this->schedulingService->getCapacityUtilization(
            $this->tid(),
            $date
        );

        return response()->json($utilization);
    }

    /**
     * Reschedule overdue work orders
     */
    public function rescheduleOverdue()
    {
        $result = $this->schedulingService->rescheduleOverdue($this->tid());

        return back()->with('success', $result['message']);
    }
}
