<?php

namespace App\Services;

use App\Models\CosmeticBatchRecord;
use App\Models\ProductRecall;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recall Management Service
 * 
 * @note Linter may show false positive for auth()->id() - this is standard Laravel
 */
class RecallManagementService
{
    /**
     * TASK-2.41: Create product recall
     */
    public function createRecall(int $tenantId, array $data)
    {
        return DB::transaction(function () use ($tenantId, $data) {
            $recall = new ProductRecall();
            $recall->tenant_id = $tenantId;
            $recall->recall_number = $this->generateRecallNumber();
            $recall->product_id = $data['product_id'];
            $recall->batch_ids = $data['batch_ids'] ?? [];
            $recall->recall_type = $data['recall_type']; // voluntary, mandatory
            $recall->severity = $data['severity']; // critical, major, minor
            $recall->reason = $data['reason'];
            $recall->description = $data['description'] ?? null;
            $recall->affected_units = $data['affected_units'] ?? 0;
            $recall->action_required = $data['action_required'];
            $recall->contact_person = $data['contact_person'] ?? null;
            $recall->contact_email = $data['contact_email'] ?? null;
            $recall->contact_phone = $data['contact_phone'] ?? null;
            $recall->start_date = $data['start_date'] ?? now();
            $recall->end_date = $data['end_date'] ?? null;
            $recall->status = 'initiated';
            $recall->initiated_by = auth()->id();
            $recall->save();

            // Notify relevant parties
            $this->notifyRecallStakeholders($recall);

            Log::warning('Product recall initiated', [
                'recall_id' => $recall->id,
                'recall_number' => $recall->recall_number,
                'severity' => $recall->severity,
            ]);

            return $recall;
        });
    }

    /**
     * Update recall status
     */
    public function updateRecallStatus(ProductRecall $recall, string $status, string $notes = ''): ProductRecall
    {
        $recall->status = $status;
        $recall->resolution_notes = ($recall->resolution_notes ? $recall->resolution_notes . "\n\n" : '') . $notes;

        if ($status === 'completed') {
            $recall->completion_date = now()->format('Y-m-d');
        }

        $recall->save();

        Log::info('Product recall status updated', [
            'recall_id' => $recall->id,
            'status' => $status,
        ]);

        return $recall;
    }

    /**
     * Get active recalls
     */
    public function getActiveRecalls(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return ProductRecall::where('tenant_id', $tenantId)
            ->whereIn('status', ['initiated', 'in_progress'])
            ->with(['product', 'batches'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get recall statistics
     */
    public function getRecallStats(int $tenantId): array
    {
        $total = ProductRecall::where('tenant_id', $tenantId)->count();
        $active = ProductRecall::where('tenant_id', $tenantId)
            ->whereIn('status', ['initiated', 'in_progress'])
            ->count();
        $completed = ProductRecall::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->count();
        $critical = ProductRecall::where('tenant_id', $tenantId)
            ->where('severity', 'critical')
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed,
            'critical' => $critical,
            'resolution_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * TASK-2.41: Check expiring batches
     */
    public function checkExpiringBatches(int $tenantId, int $days = 90): array
    {
        $expiringSoon = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->with(['formula'])
            ->orderBy('expiry_date')
            ->get();

        $expired = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with(['formula'])
            ->orderByDesc('expiry_date')
            ->get();

        return [
            'expiring_soon' => $expiringSoon,
            'expired' => $expired,
            'expiring_count' => $expiringSoon->count(),
            'expired_count' => $expired->count(),
        ];
    }

    /**
     * Auto-expire batches
     */
    public function autoExpireBatches(int $tenantId): int
    {
        $count = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::warning("Auto-expired {$count} batches", [
                'tenant_id' => $tenantId,
            ]);
        }

        return $count;
    }

    /**
     * Generate recall number
     */
    protected function generateRecallNumber(): string
    {
        $year = now()->format('Y');
        $count = ProductRecall::whereYear('created_at', $year)->count() + 1;
        return 'RCL-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Notify stakeholders about recall
     */
    protected function notifyRecallStakeholders(ProductRecall $recall): void
    {
        // Send notifications to relevant users
        $notifiableUsers = User::where('tenant_id', $recall->tenant_id)
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhere('role', 'quality_control')
                    ->orWhere('role', 'manager');
            })
            ->get();

        // Note: ProductRecallNotification should be created if needed
        // For now, we log the notification attempt
        Log::info('Recall notification would be sent to ' . $notifiableUsers->count() . ' users', [
            'recall_id' => $recall->id,
            'recall_number' => $recall->recall_number,
        ]);

        // Uncomment when ProductRecallNotification is created:
        // try {
        //     Notification::send($notifiableUsers, new \App\Notifications\ProductRecallNotification($recall));
        // } catch (\Exception $e) {
        //     Log::error('Failed to send recall notifications', [
        //         'recall_id' => $recall->id,
        //         'error' => $e->getMessage(),
        //     ]);
        // }
    }
}
