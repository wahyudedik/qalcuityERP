<?php

namespace App\Http\Controllers\Construction;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\GanttChartService;
use Illuminate\Http\Request;

class GanttChartController extends Controller
{
    protected $ganttService;

    public function __construct(GanttChartService $ganttService)
    {
        $this->ganttService = $ganttService;
    }

    /**
     * Display Gantt chart for a project
     */
    public function index(Request $request, int $projectId)
    {
        $project = Project::where('id', $projectId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['tasks.assignedTo'])
            ->firstOrFail();

        $ganttData = $this->ganttService->generateGanttData($projectId, auth()->user()->tenant_id);
        $conflicts = $this->ganttService->detectConflicts($projectId, auth()->user()->tenant_id);

        return view('construction.gantt.index', compact('project', 'ganttData', 'conflicts'));
    }

    /**
     * Get Gantt data as JSON for API/visualization
     */
    public function getData(int $projectId)
    {
        $data = $this->ganttService->generateGanttData($projectId, auth()->user()->tenant_id);

        return response()->json($data);
    }

    /**
     * Detect scheduling conflicts
     */
    public function checkConflicts(int $projectId)
    {
        $conflicts = $this->ganttService->detectConflicts($projectId, auth()->user()->tenant_id);

        return response()->json($conflicts);
    }

    /**
     * Export Gantt data to JSON
     */
    public function export(int $projectId)
    {
        $json = $this->ganttService->exportToJson($projectId, auth()->user()->tenant_id);

        return response($json, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=gantt-project-{$projectId}.json");
    }
}
