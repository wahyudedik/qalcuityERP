<?php

namespace App\Services;

use App\Models\NightAuditBatch;
use App\Models\NightAuditLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\FbOrder;
use App\Models\MinibarTransaction;
use Illuminate\Support\Facades\DB;

class NightAuditService
{
    /**
     * Start night audit batch process
     */
    public function startAudit(int $tenantId, \Carbon\Carbon $auditDate): NightAuditBatch
    {
        return DB::transaction(function () use ($tenantId, $auditDate) {
            // Check if batch already exists for this date
            $existingBatch = NightAuditBatch::where('tenant_id', $tenantId)
                ->where('audit_date', $auditDate)
                ->first();

            if ($existingBatch) {
                throw new \Exception("Audit batch already exists for date: {$auditDate->format('Y-m-d')}");
            }

            // Create new batch
            $batch = NightAuditBatch::create([
                'tenant_id' => $tenantId,
                'batch_number' => NightAuditBatch::generateBatchNumber($auditDate),
                'audit_date' => $auditDate,
                'started_at' => now(),
                'auditor_id' => auth()->id(),
                'status' => 'in_progress',
            ]);

            NightAuditLog::logSuccess(
                'start_audit',
                "Started night audit batch: {$batch->batch_number}",
                auth()->id(),
                $batch->id
            );

            return $batch;
        });
    }

    /**
     * Post room charges for all occupied rooms
     * BUG-HOTEL-002 FIX: Wrapped in transaction with idempotency check
     */
    public function postRoomCharges(NightAuditBatch $batch): array
    {
        return DB::transaction(function () use ($batch) {
            // BUG-HOTEL-002 FIX: Idempotency check - prevent double posting
            if ($batch->room_charges_posted) {
                throw new \Exception("Room charges already posted for this batch. Cannot post twice.");
            }

            $postedCount = 0;
            $totalRevenue = 0;

            // Get all checked-in reservations for audit date
            $reservations = Reservation::where('tenant_id', $batch->tenant_id)
                ->where('check_in_date', '<=', $batch->audit_date)
                ->where('check_out_date', '>', $batch->audit_date)
                ->where('status', 'checked_in')
                ->with(['roomType', 'rooms'])
                ->get();

            foreach ($reservations as $reservation) {
                foreach ($reservation->rooms as $room) {
                    $rateAmount = $reservation->rate_per_night ?? $room->roomType->base_rate ?? 0;

                    if ($rateAmount > 0) {
                        // Post room charge
                        $posting = \App\Models\RevenuePosting::create([
                            'tenant_id' => $batch->tenant_id,
                            'audit_batch_id' => $batch->id,
                            'posting_reference' => \App\Models\RevenuePosting::generatePostingReference(),
                            'posting_date' => $batch->audit_date,
                            'reservation_id' => $reservation->id,
                            'room_number' => $room->number,
                            'guest_id' => $reservation->guest_id,
                            'revenue_type' => 'room_charge',
                            'description' => "Room charge - Room {$room->number} - {$batch->audit_date->format('Y-m-d')}",
                            'amount' => $rateAmount,
                            'tax_amount' => $rateAmount * 0.10, // 10% tax
                            'total_amount' => 0, // Will be calculated
                            'status' => 'pending',
                            'auto_generated' => true,
                            'created_by' => auth()->id(),
                        ]);

                        $posting->calculateTotal();
                        $posting->post();

                        $postedCount++;
                        $totalRevenue += $posting->total_amount;

                        NightAuditLog::logSuccess(
                            'post_room_charge',
                            "Posted room charge for Room {$room->number}",
                            auth()->id(),
                            $batch->id,
                            ['posting_id' => $posting->id, 'amount' => $rateAmount]
                        );
                    }
                }
            }

            // BUG-HOTEL-002 FIX: Mark as posted ONLY after all charges succeed
            $batch->update([
                'occupied_rooms' => $reservations->count(),
                'total_room_revenue' => $totalRevenue,
                'room_charges_posted' => true,
                'room_charges_posted_at' => now(),
            ]);

            return [
                'posted_count' => $postedCount,
                'total_revenue' => $totalRevenue,
            ];
        });
    }

    /**
     * Post F&B revenue from previous day
     * BUG-HOTEL-002 FIX: Wrapped in transaction with idempotency check
     */
    public function postFBRevenue(NightAuditBatch $batch): array
    {
        return DB::transaction(function () use ($batch) {
            // BUG-HOTEL-002 FIX: Idempotency check
            if ($batch->fb_revenue_posted) {
                throw new \Exception("F&B revenue already posted for this batch. Cannot post twice.");
            }

            $postedCount = 0;
            $totalRevenue = 0;

            // Get completed F&B orders from audit date
            $orders = FbOrder::where('tenant_id', $batch->tenant_id)
                ->whereDate('created_at', $batch->audit_date)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->get();

            foreach ($orders as $order) {
                $posting = \App\Models\RevenuePosting::create([
                    'tenant_id' => $batch->tenant_id,
                    'audit_batch_id' => $batch->id,
                    'posting_reference' => \App\Models\RevenuePosting::generatePostingReference(),
                    'posting_date' => $batch->audit_date,
                    'reservation_id' => $order->reservation_id,
                    'room_number' => $order->room_number,
                    'guest_id' => $order->guest_id,
                    'revenue_type' => $this->mapOrderTypeToRevenueType($order->order_type),
                    'description' => "F&B Order #{$order->order_number}",
                    'amount' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'total_amount' => $order->total_amount,
                    'status' => 'posted',
                    'auto_generated' => true,
                    'created_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                $postedCount++;
                $totalRevenue += $order->total_amount;
            }

            // BUG-HOTEL-002 FIX: Mark as posted ONLY after all postings succeed
            $batch->update([
                'total_fb_revenue' => $totalRevenue,
                'fb_revenue_posted' => true,
                'fb_revenue_posted_at' => now(),
            ]);

            return [
                'posted_count' => $postedCount,
                'total_revenue' => $totalRevenue,
            ];
        });
    }

    /**
     * Post minibar charges
     * BUG-HOTEL-002 FIX: Wrapped in transaction, mark billed AFTER posting succeeds
     */
    public function postMinibarCharges(NightAuditBatch $batch): array
    {
        return DB::transaction(function () use ($batch) {
            // BUG-HOTEL-002 FIX: Idempotency check
            if ($batch->minibar_charges_posted) {
                throw new \Exception("Minibar charges already posted for this batch. Cannot post twice.");
            }

            $postedCount = 0;
            $totalRevenue = 0;
            $updatedTransactionIds = []; // Track for rollback

            try {
                // Get pending minibar transactions
                $transactions = MinibarTransaction::where('tenant_id', $batch->tenant_id)
                    ->whereDate('consumption_date', $batch->audit_date)
                    ->where('billing_status', 'pending')
                    ->get();

                foreach ($transactions as $transaction) {
                    $posting = \App\Models\RevenuePosting::create([
                        'tenant_id' => $batch->tenant_id,
                        'audit_batch_id' => $batch->id,
                        'posting_reference' => \App\Models\RevenuePosting::generatePostingReference(),
                        'posting_date' => $batch->audit_date,
                        'reservation_id' => $transaction->reservation_id,
                        'room_number' => $transaction->room_number,
                        'revenue_type' => 'minibar',
                        'description' => "Minibar - {$transaction->menuItem->name} x{$transaction->quantity_consumed}",
                        'amount' => $transaction->total_charge,
                        'tax_amount' => 0,
                        'total_amount' => $transaction->total_charge,
                        'status' => 'posted',
                        'auto_generated' => true,
                        'created_by' => auth()->id(),
                        'posted_at' => now(),
                    ]);

                    // BUG-HOTEL-002 FIX: Mark transaction as billed AFTER posting succeeds
                    $transaction->update(['billing_status' => 'billed']);
                    $updatedTransactionIds[] = $transaction->id;

                    $postedCount++;
                    $totalRevenue += $transaction->total_charge;
                }

                // BUG-HOTEL-002 FIX: Mark as posted ONLY after all postings succeed
                $batch->update([
                    'total_other_revenue' => $totalRevenue,
                    'minibar_charges_posted' => true,
                    'minibar_charges_posted_at' => now(),
                ]);

                return [
                    'posted_count' => $postedCount,
                    'total_revenue' => $totalRevenue,
                ];
            } catch (\Exception $e) {
                // BUG-HOTEL-002 FIX: Rollback minibar transaction status if posting fails
                if (!empty($updatedTransactionIds)) {
                    MinibarTransaction::whereIn('id', $updatedTransactionIds)
                        ->update(['billing_status' => 'pending']);

                    \Log::warning('Minibar posting failed, rolled back billing status', [
                        'batch_id' => $batch->id,
                        'rolled_back_count' => count($updatedTransactionIds),
                        'error' => $e->getMessage(),
                    ]);
                }

                throw $e;
            }
        });
    }

    /**
     * Calculate occupancy statistics
     */
    public function calculateOccupancyStats(NightAuditBatch $batch): void
    {
        $tenantId = $batch->tenant_id;
        $auditDate = $batch->audit_date;

        // Get total rooms
        $totalRooms = Room::where('tenant_id', $tenantId)->count();

        // Get occupied rooms (checked-in reservations)
        $occupiedRooms = Reservation::where('tenant_id', $tenantId)
            ->where('check_in_date', '<=', $auditDate)
            ->where('check_out_date', '>', $auditDate)
            ->where('status', 'checked_in')
            ->count();

        // Get out of order rooms
        $outOfOrderRooms = Room::where('tenant_id', $tenantId)
            ->where('status', 'out_of_order')
            ->count();

        // Calculate check-ins and check-outs for the day
        $checkIns = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_in_date', $auditDate)
            ->count();

        $checkOuts = Reservation::where('tenant_id', $tenantId)
            ->whereDate('check_out_date', $auditDate)
            ->count();

        // Create or update occupancy stats
        $stats = \App\Models\DailyOccupancyStat::getOrCreateForDate($tenantId, $auditDate);
        $stats->update([
            'total_rooms' => $totalRooms,
            'available_rooms' => $totalRooms - $outOfOrderRooms,
            'occupied_rooms' => $occupiedRooms,
            'out_of_order_rooms' => $outOfOrderRooms,
            'check_ins' => $checkIns,
            'check_outs' => $checkOuts,
            'stay_over' => max(0, $occupiedRooms - $checkIns),
        ]);

        $stats->calculateOccupancyPercentage();

        // Update batch
        $batch->update([
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'occupancy_rate' => $stats->occupancy_percentage,
        ]);

        NightAuditLog::logSuccess(
            'calculate_occupancy',
            "Calculated occupancy: {$occupiedRooms}/{$totalRooms} rooms",
            auth()->id(),
            $batch->id,
            ['occupancy_rate' => $stats->occupancy_percentage]
        );
    }

    /**
     * Complete night audit batch
     * BUG-HOTEL-002 FIX: Validate all required steps completed before allowing completion
     */
    public function completeAudit(NightAuditBatch $batch): void
    {
        // BUG-HOTEL-002 FIX: Validate all required steps are completed
        $missingSteps = [];

        if (!$batch->room_charges_posted) {
            $missingSteps[] = 'Room charges posting';
        }

        if (!$batch->fb_revenue_posted) {
            $missingSteps[] = 'F&B revenue posting';
        }

        if (!$batch->minibar_charges_posted) {
            $missingSteps[] = 'Minibar charges posting';
        }

        if (!empty($missingSteps)) {
            throw new \Exception(
                "Cannot complete audit. Missing steps: " . implode(', ', $missingSteps) . ". " .
                "Please complete all required postings before finishing the audit."
            );
        }

        DB::transaction(function () use ($batch) {
            // Calculate total revenue
            $totalRevenue = $batch->total_room_revenue + $batch->total_fb_revenue + $batch->total_other_revenue;
            $batch->update(['total_revenue' => $totalRevenue]);

            // Calculate ADR and RevPAR
            $batch->calculateADR();
            $batch->calculateRevPAR();

            // Save summary data
            $batch->update([
                'summary_data' => [
                    'total_rooms' => $batch->total_rooms,
                    'occupied_rooms' => $batch->occupied_rooms,
                    'occupancy_rate' => $batch->occupancy_rate,
                    'adr' => $batch->adr,
                    'revpar' => $batch->revpar,
                    'total_revenue' => $batch->total_revenue,
                    'breakdown' => [
                        'room_revenue' => $batch->total_room_revenue,
                        'fb_revenue' => $batch->total_fb_revenue,
                        'other_revenue' => $batch->total_other_revenue,
                    ],
                    'completed_at' => now()->toDateTimeString(),
                    'completed_by' => auth()->id(),
                ],
            ]);

            // Mark as completed
            $batch->markAsCompleted();

            NightAuditLog::logSuccess(
                'complete_audit',
                "Completed night audit batch: {$batch->batch_number}",
                auth()->id(),
                $batch->id,
                ['total_revenue' => $totalRevenue]
            );
        });
    }

    /**
     * Map F&B order type to revenue type
     */
    private function mapOrderTypeToRevenueType(string $orderType): string
    {
        return match ($orderType) {
            'restaurant_dine_in', 'restaurant_takeaway' => 'restaurant',
            'room_service' => 'room_service',
            'minibar' => 'minibar',
            default => 'other',
        };
    }
}
