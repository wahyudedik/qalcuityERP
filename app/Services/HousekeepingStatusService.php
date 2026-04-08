<?php

namespace App\Services;

use App\Models\Room;
use App\Models\HousekeepingTask;
use Illuminate\Support\Facades\Log;

/**
 * HousekeepingStatusService - Manage room status transitions for housekeeping
 * 
 * BUG-HOTEL-003 FIX: Ensure room status is properly updated through the cleaning workflow
 * 
 * Status Flow:
 * occupied → dirty (after check-out)
 * dirty → cleaning (when task starts)
 * cleaning → clean (when task completed)
 * clean → available (after inspection, ready for guest)
 * 
 * This prevents the bug where room stays "available" but hasn't been cleaned.
 */
class HousekeepingStatusService
{
    /**
     * BUG-HOTEL-003 FIX: Mark room as dirty after check-out
     * 
     * This is the critical step that was missing!
     * 
     * @param Room $room
     * @param string $reason Reason for marking dirty (checkout, guest_request, etc.)
     * @return array
     */
    public function markRoomDirty(Room $room, string $reason = 'checkout'): array
    {
        // Cannot mark dirty if already dirty or in maintenance
        if (in_array($room->status, ['dirty', 'maintenance', 'out_of_order'])) {
            return [
                'success' => false,
                'message' => "Room status saat ini adalah {$room->status}, tidak perlu ditandai dirty lagi.",
            ];
        }

        $oldStatus = $room->status;

        // Update room status to dirty
        $room->update([
            'status' => 'dirty',
            'last_inspected_at' => null, // Reset inspection
        ]);

        // Increment occupancy days
        $room->increment('occupancy_days');

        Log::info('Housekeeping: Room marked as dirty', [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'old_status' => $oldStatus,
            'reason' => $reason,
            'occupancy_days' => $room->occupancy_days,
        ]);

        // Auto-create cleaning task
        $task = $this->createCleaningTask($room, $reason);

        return [
            'success' => true,
            'message' => "Room {$room->number} ditandai dirty dan task cleaning dibuat.",
            'data' => [
                'room_id' => $room->id,
                'room_number' => $room->number,
                'old_status' => $oldStatus,
                'new_status' => 'dirty',
                'task_id' => $task->id ?? null,
            ],
        ];
    }

    /**
     * BUG-HOTEL-003 FIX: Start cleaning task and update room status
     * 
     * @param Room $room
     * @param HousekeepingTask $task
     * @return array
     */
    public function startCleaning(Room $room, HousekeepingTask $task): array
    {
        // Validate room is dirty
        if ($room->status !== 'dirty') {
            return [
                'success' => false,
                'message' => "Room harus dalam status 'dirty' untuk mulai cleaning. Status saat ini: {$room->status}",
            ];
        }

        $oldStatus = $room->status;

        // Update room status to cleaning
        $room->update(['status' => 'cleaning']);

        Log::info('Housekeeping: Cleaning started', [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'task_id' => $task->id,
            'old_status' => $oldStatus,
            'assigned_to' => $task->assigned_to,
        ]);

        return [
            'success' => true,
            'message' => "Cleaning dimulai untuk Room {$room->number}.",
            'data' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => 'cleaning',
            ],
        ];
    }

    /**
     * BUG-HOTEL-003 FIX: Complete cleaning and mark room as clean
     * 
     * @param Room $room
     * @param HousekeepingTask $task
     * @param array $checklist
     * @param string|null $notes
     * @return array
     */
    public function completeCleaning(Room $room, HousekeepingTask $task, array $checklist = [], ?string $notes = null): array
    {
        // Validate room is being cleaned
        if ($room->status !== 'cleaning') {
            return [
                'success' => false,
                'message' => "Room harus dalam status 'cleaning' untuk complete. Status saat ini: {$room->status}",
            ];
        }

        $oldStatus = $room->status;

        // Update room status to clean (not available yet!)
        $room->update([
            'status' => 'clean',
            'last_cleaned_at' => now(),
            'cleaned_by' => $task->assigned_to,
        ]);

        // Increment cleaning count
        $room->incrementCleaningCount();

        // Check if deep clean is required
        $requiresDeepClean = $room->checkDeepCleanRequired();

        Log::info('Housekeeping: Cleaning completed', [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'task_id' => $task->id,
            'old_status' => $oldStatus,
            'requires_deep_clean' => $requiresDeepClean,
        ]);

        return [
            'success' => true,
            'message' => "Room {$room->number} selesai dibersihkan. Butuh inspeksi sebelum available.",
            'data' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => 'clean',
                'requires_deep_clean' => $requiresDeepClean,
                'next_step' => 'inspection',
            ],
        ];
    }

    /**
     * BUG-HOTEL-003 FIX: Inspect room and mark as available
     * 
     * Room can only be available AFTER inspection!
     * 
     * @param Room $room
     * @param int $inspectorId
     * @param array $checklist
     * @param string|null $notes
     * @return array
     */
    public function inspectRoom(Room $room, int $inspectorId, array $checklist = [], ?string $notes = null): array
    {
        // Validate room is clean
        if ($room->status !== 'clean') {
            return [
                'success' => false,
                'message' => "Room harus dalam status 'clean' untuk inspeksi. Status saat ini: {$room->status}",
            ];
        }

        $oldStatus = $room->status;

        // Update room status to available (finally!)
        $room->update([
            'status' => 'available',
            'last_inspected_at' => now(),
            'inspected_by' => $inspectorId,
        ]);

        Log::info('Housekeeping: Room inspected and marked available', [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'old_status' => $oldStatus,
            'inspector_id' => $inspectorId,
        ]);

        return [
            'success' => true,
            'message' => "Room {$room->number} lulus inspeksi dan tersedia untuk guest.",
            'data' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => 'available',
                'inspector_id' => $inspectorId,
            ],
        ];
    }

    /**
     * BUG-HOTEL-003 FIX: Reject room after inspection (failed inspection)
     * 
     * @param Room $room
     * @param string $reason
     * @return array
     */
    public function rejectAfterInspection(Room $room, string $reason): array
    {
        // Validate room is clean
        if ($room->status !== 'clean') {
            return [
                'success' => false,
                'message' => "Room harus dalam status 'clean' untuk inspeksi. Status saat ini: {$room->status}",
            ];
        }

        $oldStatus = $room->status;

        // Send back to cleaning
        $room->update(['status' => 'dirty']);

        // Create new cleaning task with high priority
        $task = $this->createCleaningTask($room, 're_clean', 'high', $reason);

        Log::warning('Housekeeping: Room failed inspection, re-cleaning required', [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'old_status' => $oldStatus,
            'reason' => $reason,
            'task_id' => $task->id,
        ]);

        return [
            'success' => true,
            'message' => "Room {$room->number} tidak lulus inspeksi. Harus dibersihkan ulang.",
            'data' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => 'dirty',
                'task_id' => $task->id,
            ],
        ];
    }

    /**
     * Create cleaning task for room
     * 
     * @param Room $room
     * @param string $type
     * @param string $priority
     * @param string|null $notes
     * @return HousekeepingTask
     */
    protected function createCleaningTask(Room $room, string $type = 'checkout_clean', string $priority = 'normal', ?string $notes = null): HousekeepingTask
    {
        $task = HousekeepingTask::create([
            'tenant_id' => $room->tenant_id,
            'room_id' => $room->id,
            'type' => $type,
            'priority' => $priority,
            'status' => 'pending',
            'notes' => $notes,
            'scheduled_at' => now(),
        ]);

        Log::info('Housekeeping: Cleaning task created', [
            'task_id' => $task->id,
            'room_id' => $room->id,
            'room_number' => $room->number,
            'type' => $type,
            'priority' => $priority,
        ]);

        return $task;
    }

    /**
     * Get room status workflow info
     * 
     * @param Room $room
     * @return array
     */
    public function getStatusWorkflow(Room $room): array
    {
        $workflows = [
            'occupied' => ['next' => 'dirty', 'action' => 'Check-out guest'],
            'dirty' => ['next' => 'cleaning', 'action' => 'Start cleaning task'],
            'cleaning' => ['next' => 'clean', 'action' => 'Complete cleaning'],
            'clean' => ['next' => 'available', 'action' => 'Inspect room'],
            'available' => ['next' => 'occupied', 'action' => 'Check-in guest'],
            'maintenance' => ['next' => 'clean', 'action' => 'Complete maintenance'],
            'out_of_order' => ['next' => 'dirty', 'action' => 'Fix issue'],
        ];

        $current = $workflows[$room->status] ?? null;

        return [
            'room_id' => $room->id,
            'room_number' => $room->number,
            'current_status' => $room->status,
            'next_status' => $current['next'] ?? null,
            'required_action' => $current['action'] ?? null,
            'workflow_complete' => $room->status === 'available',
        ];
    }

    /**
     * Validate status transition is allowed
     * 
     * @param string $fromStatus
     * @param string $toStatus
     * @return bool
     */
    public function isValidTransition(string $fromStatus, string $toStatus): bool
    {
        $validTransitions = [
            'occupied' => ['dirty'],
            'dirty' => ['cleaning'],
            'cleaning' => ['clean'],
            'clean' => ['available', 'dirty'], // available after inspection, dirty if failed
            'available' => ['occupied', 'cleaning'], // occupied on check-in, cleaning for maintenance
            'maintenance' => ['clean', 'dirty'],
            'out_of_order' => ['dirty', 'maintenance'],
        ];

        return in_array($toStatus, $validTransitions[$fromStatus] ?? []);
    }
}
