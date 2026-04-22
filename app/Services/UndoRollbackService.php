<?php

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Support\Facades\DB;

class UndoRollbackService
{
    /**
     * Log an action for potential undo
     */
    public function logAction(string $actionType, string $modelType, $modelId, ?array $beforeState = null, ?array $afterState = null, array $metadata = []): ActionLog
    {
        return ActionLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'metadata' => $metadata,
            'can_undo' => true,
            'expires_at' => now()->addHours(24), // Expire after 24 hours
        ]);
    }

    /**
     * Undo last action by user
     */
    public function undoLastAction(?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();
        $tenantId = auth()->user()->tenant_id;

        $lastAction = ActionLog::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('undone', false)
            ->where('can_undo', true)
            ->latest()
            ->first();

        if (!$lastAction) {
            return ['success' => false, 'error' => 'No actions to undo'];
        }

        if ($lastAction->isExpired()) {
            return ['success' => false, 'error' => 'Action has expired'];
        }

        $success = $lastAction->undo();

        return [
            'success' => $success,
            'action' => $lastAction,
            'message' => $success ? 'Action undone successfully' : 'Failed to undo action'
        ];
    }

    /**
     * Undo specific action
     */
    public function undoAction(int $actionLogId): array
    {
        $tenantId = auth()->user()->tenant_id;

        $action = ActionLog::where('tenant_id', $tenantId)
            ->where('id', $actionLogId)
            ->first();

        if (!$action) {
            return ['success' => false, 'error' => 'Action not found'];
        }

        if ($action->undone) {
            return ['success' => false, 'error' => 'Action already undone'];
        }

        if ($action->isExpired()) {
            return ['success' => false, 'error' => 'Action has expired'];
        }

        $success = $action->undo();

        return [
            'success' => $success,
            'message' => $success ? 'Action undone successfully' : 'Failed to undo action'
        ];
    }

    /**
     * Get undoable actions for user
     */
    public function getUndoableActions(int $limit = 20): array
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

        return ActionLog::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('undone', false)
            ->where('can_undo', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Bulk undo actions
     */
    public function bulkUndo(array $actionIds): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($actionIds as $actionId) {
            $result = $this->undoAction($actionId);
            $results[] = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success' => true,
            'total' => count($actionIds),
            'succeeded' => $successCount,
            'failed' => $failCount,
            'results' => $results
        ];
    }

    /**
     * Cleanup expired action logs
     */
    public function cleanupExpiredLogs(): int
    {
        return ActionLog::where('undone', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
