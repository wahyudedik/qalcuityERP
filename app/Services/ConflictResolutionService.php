<?php

namespace App\Services;

use App\Models\EditConflict;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConflictResolutionService
{
    /**
     * Detect and create conflict record
     */
    public function detectConflict(string $modelType, int $modelId, array $originalData, array $newChanges): ?EditConflict
    {
        // Check if there's already a pending conflict
        $existingConflict = EditConflict::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('status', 'pending')
            ->first();

        if ($existingConflict) {
            // Update with second user's changes
            $existingConflict->update([
                'second_user_changes' => $newChanges,
                'conflicting_user_id' => Auth::id(),
            ]);

            return $existingConflict;
        }

        // Create new conflict record
        return EditConflict::create([
            'tenant_id' => Auth::user()->tenant_id ?? abort(401, 'Unauthenticated.'),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'original_user_id' => Auth::id(),
            'conflicting_user_id' => Auth::id(),
            'original_data' => $originalData,
            'first_user_changes' => $newChanges,
            'second_user_changes' => [],
            'detected_at' => now(),
        ]);
    }

    /**
     * Resolve conflict with strategy
     */
    public function resolveConflict(int $conflictId, string $strategy, ?array $mergedData = null): array
    {
        $conflict = EditConflict::find($conflictId);

        if (! $conflict) {
            return ['success' => false, 'error' => 'Conflict not found'];
        }

        try {
            switch ($strategy) {
                case 'first_wins':
                    $conflict->resolveWithFirstUser();
                    $resolvedData = $conflict->first_user_changes;
                    break;

                case 'last_wins':
                    $conflict->resolveWithSecondUser();
                    $resolvedData = $conflict->second_user_changes;
                    break;

                case 'merge':
                    if (! $mergedData) {
                        return ['success' => false, 'error' => 'Merged data required'];
                    }
                    $conflict->resolveWithMerge($mergedData);
                    $resolvedData = $mergedData;
                    break;

                default:
                    return ['success' => false, 'error' => 'Invalid strategy'];
            }

            // Apply resolved data to model
            $model = app($conflict->model_type)->find($conflict->model_id);
            if ($model) {
                $model->update($resolvedData);
            }

            return [
                'success' => true,
                'conflict' => $conflict,
                'strategy' => $strategy,
            ];

        } catch (\Throwable $e) {
            Log::error('Conflict resolution failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get pending conflicts for user
     */
    public function getPendingConflicts(int $limit = 20): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $tenantId = $user->tenant_id;
        if (! $tenantId) {
            return [];
        }

        return EditConflict::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->with(['originalUser', 'conflictingUser'])
            ->orderBy('detected_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get conflict statistics
     */
    public function getStatistics(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'pending_conflicts' => 0,
                'resolved_today' => 0,
                'total_conflicts' => 0,
            ];
        }

        $tenantId = $user->tenant_id;
        if (! $tenantId) {
            return [
                'pending_conflicts' => 0,
                'resolved_today' => 0,
                'total_conflicts' => 0,
            ];
        }

        return [
            'pending_conflicts' => EditConflict::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
            'resolved_today' => EditConflict::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
            'total_conflicts' => EditConflict::where('tenant_id', $tenantId)->count(),
        ];
    }

    /**
     * Discard conflict
     */
    public function discardConflict(int $conflictId): bool
    {
        $conflict = EditConflict::find($conflictId);

        if (! $conflict) {
            return false;
        }

        $conflict->update([
            'status' => 'discarded',
            'resolved_at' => now(),
        ]);

        return true;
    }
}
