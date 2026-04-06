<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTask;
use Carbon\Carbon;

/**
 * Gantt Chart Service untuk Konstruksi
 */
class GanttChartService
{
    /**
     * Generate Gantt chart data for a project
     */
    public function generateGanttData(int $projectId, int $tenantId): array
    {
        $project = Project::where('id', $projectId)
            ->where('tenant_id', $tenantId)
            ->with([
                'tasks' => function ($query) {
                    $query->orderBy('due_date');
                }
            ])
            ->firstOrFail();

        $tasks = $project->tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->name,
                'start' => $task->created_at->format('Y-m-d'),
                'end' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'progress' => $task->effectiveProgress(),
                'status' => $task->status,
                'assigned_to' => $task->assignedTo?->name ?? 'Unassigned',
                'weight' => $task->weight,
                'is_milestone' => $task->weight >= 20, // Tasks with high weight are milestones
                'dependencies' => [], // Can be extended later
            ];
        });

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'number' => $project->number,
                'start_date' => $project->start_date?->format('Y-m-d'),
                'end_date' => $project->end_date?->format('Y-m-d'),
                'progress' => $project->progress,
                'status' => $project->status,
            ],
            'tasks' => $tasks,
            'timeline' => $this->calculateTimeline($project),
            'critical_path' => $this->identifyCriticalPath($project),
        ];
    }

    /**
     * Calculate project timeline summary
     */
    private function calculateTimeline(Project $project): array
    {
        $tasks = $project->tasks;

        if ($tasks->isEmpty()) {
            return [
                'total_days' => 0,
                'elapsed_days' => 0,
                'remaining_days' => 0,
                'completion_percentage' => 0,
            ];
        }

        $startDate = $project->start_date ?? $tasks->min(fn($t) => $t->created_at);
        $endDate = $project->end_date ?? $tasks->max(fn($t) => $t->due_date);

        if (!$startDate || !$endDate) {
            return [
                'total_days' => 0,
                'elapsed_days' => 0,
                'remaining_days' => 0,
                'completion_percentage' => 0,
            ];
        }

        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        $elapsedDays = Carbon::parse($startDate)->diffInDays(now());
        $remainingDays = max(0, Carbon::parse($endDate)->diffInDays(now()));

        return [
            'total_days' => $totalDays,
            'elapsed_days' => min($elapsedDays, $totalDays),
            'remaining_days' => $remainingDays,
            'completion_percentage' => $totalDays > 0
                ? round((min($elapsedDays, $totalDays) / $totalDays) * 100, 1)
                : 0,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_overdue' => now()->gt(Carbon::parse($endDate)) && $project->status !== 'completed',
        ];
    }

    /**
     * Identify critical path tasks (high weight, long duration)
     */
    private function identifyCriticalPath(Project $project): array
    {
        $tasks = $project->tasks()
            ->whereNotIn('status', ['cancelled', 'done'])
            ->orderByDesc('weight')
            ->orderByDesc('due_date')
            ->limit(5)
            ->get();

        return $tasks->map(fn($task) => [
            'id' => $task->id,
            'name' => $task->name,
            'weight' => $task->weight,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'progress' => $task->effectiveProgress(),
        ])->toArray();
    }

    /**
     * Get task dependencies and conflicts
     */
    public function detectConflicts(int $projectId, int $tenantId): array
    {
        $project = Project::where('id', $projectId)
            ->where('tenant_id', $tenantId)
            ->with('tasks')
            ->firstOrFail();

        $conflicts = [];
        $tasks = $project->tasks->sortBy('due_date');

        foreach ($tasks as $index => $task) {
            if ($index === 0)
                continue;

            $prevTask = $tasks[$index - 1];

            // Check if current task starts before previous task ends
            if ($task->due_date && $prevTask->due_date) {
                if ($task->created_at->lt($prevTask->due_date)) {
                    $conflicts[] = [
                        'type' => 'overlap',
                        'task1' => $prevTask->name,
                        'task2' => $task->name,
                        'message' => "Task '{$task->name}' may overlap with '{$prevTask->name}'",
                    ];
                }
            }
        }

        return [
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts,
            'total_tasks' => $tasks->count(),
        ];
    }

    /**
     * Export Gantt data to JSON for frontend visualization
     */
    public function exportToJson(int $projectId, int $tenantId): string
    {
        $data = $this->generateGanttData($projectId, $tenantId);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
