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
     */
    public function postRoomCharges(NightAuditBatch $batch): array
    {
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

        // Update batch totals
        $batch->increment('occupied_rooms', $reservations->count());
        $batch->increment('total_room_revenue', $totalRevenue);

        return [
            'posted_count' => $postedCount,
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Post F&B revenue from previous day
     */
    public function postFBRevenue(NightAuditBatch $batch): array
    {
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

        // Update batch
        $batch->increment('total_fb_revenue', $totalRevenue);

        return [
            'posted_count' => $postedCount,
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Post minibar charges
     */
    public function postMinibarCharges(NightAuditBatch $batch): array
    {
        $postedCount = 0;
        $totalRevenue = 0;

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

            // Mark transaction as billed
            $transaction->update(['billing_status' => 'billed']);

            $postedCount++;
            $totalRevenue += $transaction->total_charge;
        }

        $batch->increment('total_other_revenue', $totalRevenue);

        return [
            'posted_count' => $postedCount,
            'total_revenue' => $totalRevenue,
        ];
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
     */
    public function completeAudit(NightAuditBatch $batch): void
    {
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
