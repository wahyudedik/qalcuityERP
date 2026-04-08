<?php

namespace App\Services;

use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    /**
     * Get housekeeping dashboard statistics
     */
    public function getDashboardStats(int $tenantId): array
    {
        return [
            'rooms' => [
                'total' => Room::where('tenant_id', $tenantId)->count(),
                'clean' => Room::where('tenant_id', $tenantId)->where('status', 'clean')->count(),
                'dirty' => Room::where('tenant_id', $tenantId)->where('status', 'dirty')->count(),
                'inspected' => Room::where('tenant_id', $tenantId)->where('status', 'inspected')->count(),
                'out_of_order' => Room::where('tenant_id', $tenantId)->where('status', 'out_of_order')->count(),
            ],
            'tasks' => [
                'pending' => HousekeepingTask::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
                'in_progress' => HousekeepingTask::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
                'completed_today' => HousekeepingTask::where('tenant_id', $tenantId)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', today())
                    ->count(),
                'overdue' => HousekeepingTask::where('tenant_id', $tenantId)
                    ->where('status', '!=', 'completed')
                    ->where('scheduled_at', '<', now())
                    ->count(),
            ],
            'maintenance' => [
                'pending' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'reported')->count(),
                'urgent' => MaintenanceRequest::where('tenant_id', $tenantId)->where('priority', 'urgent')->count(),
                'overdue' => MaintenanceRequest::where('tenant_id', $tenantId)
                    ->where('status', '!=', 'completed')
                    ->where(function ($q) {
                        $q->whereRaw("TIMESTAMPDIFF(HOUR, created_at, NOW()) > 
                            CASE priority
                                WHEN 'urgent' THEN 2
                                WHEN 'high' THEN 8
                                WHEN 'normal' THEN 24
                                ELSE 72
                            END");
                    })
                    ->count(),
            ],
        ];
    }

    /**
     * Create cleaning task for room
     */
    public function createCleaningTask(
        int $roomId,
        string $type,
        string $priority = 'normal',
        ?int $estimatedDuration = null,
        ?string $notes = null
    ): HousekeepingTask {
        $room = Room::findOrFail($roomId);

        $defaultDurations = [
            'checkout_clean' => 45,
            'stay_clean' => 30,
            'deep_clean' => 120,
            'inspection' => 20,
            'turndown' => 15,
        ];

        return HousekeepingTask::create([
            'tenant_id' => $room->tenant_id,
            'room_id' => $roomId,
            'type' => $type,
            'status' => 'pending',
            'priority' => $priority,
            'estimated_duration' => $estimatedDuration ?? ($defaultDurations[$type] ?? 30),
            'scheduled_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Assign task to housekeeping staff
     */
    public function assignTask(int $taskId, int $userId): HousekeepingTask
    {
        $task = HousekeepingTask::findOrFail($taskId);

        $task->update([
            'assigned_to' => $userId,
            'status' => 'pending',
        ]);

        ActivityLog::record(
            'task_assigned',
            "Housekeeping task #{$task->id} assigned to user {$userId}",
            $task->room,
            ['task_id' => $taskId, 'assigned_to' => $userId]
        );

        return $task;
    }

    /**
     * Start working on task
     */
    public function startTask(int $taskId, ?int $userId = null): HousekeepingTask
    {
        $task = HousekeepingTask::findOrFail($taskId);

        $task->start($userId);
        $task->room?->incrementCleaningCount();

        // BUG-HOTEL-003 FIX: Update room status to cleaning
        if ($task->room && $task->room->status === 'dirty') {
            $statusService = new HousekeepingStatusService();
            $statusService->startCleaning($task->room, $task);
        }

        ActivityLog::record(
            'task_started',
            "Housekeeping task #{$task->id} started",
            $task->room,
            ['task_id' => $taskId]
        );

        return $task;
    }

    /**
     * Complete task with checklist
     */
    public function completeTask(int $taskId, array $checklist = [], ?string $notes = null): HousekeepingTask
    {
        $task = HousekeepingTask::findOrFail($taskId);

        $task->complete($checklist, $notes);

        // BUG-HOTEL-003 FIX: Use proper status transition service
        if (in_array($task->type, ['checkout_clean', 'stay_clean', 'deep_clean'])) {
            $statusService = new HousekeepingStatusService();
            $statusService->completeCleaning(
                $task->room,
                $task,
                $checklist,
                $notes
            );
        }

        ActivityLog::record(
            'task_completed',
            "Housekeeping task #{$task->id} completed",
            $task->room,
            ['task_id' => $taskId, 'duration' => $task->actual_duration]
        );

        return $task;
    }

    /**
     * Create maintenance request
     */
    public function createMaintenanceRequest(
        int $roomId,
        string $title,
        string $category,
        string $description,
        string $priority,
        ?int $reportedBy = null
    ): MaintenanceRequest {
        $room = Room::findOrFail($roomId);

        $request = MaintenanceRequest::create([
            'tenant_id' => $room->tenant_id,
            'room_id' => $roomId,
            'reported_by' => $reportedBy ?? auth()->id(),
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'status' => 'reported',
            'priority' => $priority,
        ]);

        // If urgent/high priority, mark room as out of order
        if (in_array($priority, ['urgent', 'high'])) {
            $room->update(['status' => 'out_of_order']);
        }

        ActivityLog::record(
            'maintenance_reported',
            "Maintenance request '{$title}' reported for room {$room->number}",
            $room,
            ['request_id' => $request->id, 'priority' => $priority]
        );

        return $request;
    }

    /**
     * Assign maintenance request to technician
     */
    public function assignMaintenanceRequest(int $requestId, int $userId): MaintenanceRequest
    {
        $request = MaintenanceRequest::findOrFail($requestId);
        $request->assign($userId);

        return $request;
    }

    /**
     * Complete maintenance request
     */
    public function completeMaintenanceRequest(int $requestId, string $resolutionNotes, float $cost = 0): MaintenanceRequest
    {
        $request = MaintenanceRequest::findOrFail($requestId);
        $request->complete($resolutionNotes, $cost);

        return $request;
    }

    /**
     * Get rooms by status
     */
    public function getRoomsByStatus(int $tenantId, string $status): array
    {
        return Room::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->with(['roomType', 'housekeepingTasks.pending'])
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'number' => $room->number,
                    'floor' => $room->floor,
                    'type' => $room->roomType?->name,
                    'pending_tasks' => $room->housekeepingTasks->count(),
                    'last_cleaned' => $room->last_cleaned_at?->diffForHumans(),
                    'requires_deep_clean' => $room->requires_deep_clean,
                ];
            });
    }

    /**
     * Generate daily housekeeping report
     */
    public function generateDailyReport(int $tenantId, \Carbon\Carbon $date): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'rooms_cleaned' => HousekeepingTask::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count(),
            'average_cleaning_time' => HousekeepingTask::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->avg('actual_duration'),
            'maintenance_completed' => MaintenanceRequest::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count(),
            'total_maintenance_cost' => MaintenanceRequest::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->sum('cost'),
        ];
    }
}
