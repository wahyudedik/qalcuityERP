<?php

namespace App\Services;

use App\Models\GroupBooking;
use App\Models\Reservation;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * GroupBookingService - Manages group bookings, multiple room reservations, and group benefits
 */
class GroupBookingService
{
    /**
     * Create a new group booking
     */
    public function createGroupBooking(array $data): GroupBooking
    {
        return DB::transaction(function () use ($data) {
            $groupCode = GroupBooking::generateGroupCode($data['tenant_id']);

            $groupBooking = GroupBooking::create([
                'tenant_id' => $data['tenant_id'],
                'organizer_guest_id' => $data['organizer_guest_id'],
                'group_name' => $data['group_name'],
                'group_code' => $groupCode,
                'type' => $data['type'] ?? 'corporate',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_rooms' => $data['total_rooms'] ?? 1,
                'total_guests' => $data['total_guests'] ?? 1,
                'total_amount' => $data['total_amount'] ?? 0,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            \App\Models\ActivityLog::record(
                'group_booking_created',
                "Group booking created: {$groupBooking->group_code} - {$groupBooking->group_name}",
                $groupBooking,
                [],
                $groupBooking->toArray()
            );

            return $groupBooking;
        });
    }

    /**
     * Add a reservation to a group booking
     */
    public function addReservationToGroup(int $groupBookingId, int $reservationId): Reservation
    {
        return DB::transaction(function () use ($groupBookingId, $reservationId) {
            $groupBooking = GroupBooking::findOrFail($groupBookingId);
            $reservation = Reservation::findOrFail($reservationId);

            $reservation->update([
                'group_booking_id' => $groupBookingId,
            ]);

            // Update group totals
            $this->recalculateGroupTotals($groupBooking);

            \App\Models\ActivityLog::record(
                'reservation_added_to_group',
                "Reservation {$reservation->reservation_number} added to group {$groupBooking->group_code}",
                $reservation,
                ['group_booking_id' => $groupBookingId]
            );

            return $reservation;
        });
    }

    /**
     * Remove a reservation from a group booking
     */
    public function removeReservationFromGroup(int $reservationId): void
    {
        DB::transaction(function () use ($reservationId) {
            $reservation = Reservation::findOrFail($reservationId);
            $groupBookingId = $reservation->group_booking_id;

            if (!$groupBookingId) {
                throw new \Exception('Reservation is not part of a group booking');
            }

            $reservation->update(['group_booking_id' => null]);

            // Update group totals
            $groupBooking = GroupBooking::findOrFail($groupBookingId);
            $this->recalculateGroupTotals($groupBooking);

            \App\Models\ActivityLog::record(
                'reservation_removed_from_group',
                "Reservation {$reservation->reservation_number} removed from group",
                $reservation,
                ['group_booking_id' => $groupBookingId]
            );
        });
    }

    /**
     * Recalculate group booking totals based on associated reservations
     */
    public function recalculateGroupTotals(GroupBooking $groupBooking): void
    {
        $reservations = $groupBooking->reservations;

        $totalRooms = $reservations->count();
        $totalGuests = $reservations->sum('adults') + $reservations->sum('children');
        $totalAmount = $reservations->sum('grand_total');
        $paidAmount = $reservations->where('status', '!=', 'cancelled')->sum(function ($r) {
            return in_array($r->status, ['checked_in', 'checked_out']) ? $r->grand_total : 0;
        });

        // Determine payment status
        $paymentStatus = 'unpaid';
        if ($paidAmount > 0) {
            $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : 'partial';
        }

        $groupBooking->update([
            'total_rooms' => $totalRooms,
            'total_guests' => $totalGuests,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * Get all reservations for a group booking
     */
    public function getGroupReservations(int $groupBookingId): Collection
    {
        return GroupBooking::findOrFail($groupBookingId)
            ->reservations()
            ->with(['guest', 'roomType', 'room'])
            ->orderBy('check_in_date')
            ->get();
    }

    /**
     * Confirm a group booking
     */
    public function confirmGroupBooking(int $groupBookingId): GroupBooking
    {
        $groupBooking = GroupBooking::findOrFail($groupBookingId);

        $groupBooking->update([
            'status' => 'confirmed',
        ]);

        // Confirm all pending reservations in the group
        $groupBooking->reservations()
            ->where('status', 'pending')
            ->each(function ($reservation) {
                app(ReservationService::class)->confirmReservation($reservation->id);
            });

        \App\Models\ActivityLog::record(
            'group_booking_confirmed',
            "Group booking confirmed: {$groupBooking->group_code}",
            $groupBooking
        );

        return $groupBooking->fresh();
    }

    /**
     * Cancel a group booking
     */
    public function cancelGroupBooking(int $groupBookingId, string $reason): GroupBooking
    {
        return DB::transaction(function () use ($groupBookingId, $reason) {
            $groupBooking = GroupBooking::findOrFail($groupBookingId);

            $groupBooking->update([
                'status' => 'cancelled',
                'notes' => trim(($groupBooking->notes ?? '') . "\n\nCancellation Reason: $reason"),
            ]);

            // Cancel all reservations in the group
            $groupBooking->reservations()->each(function ($reservation) use ($reason) {
                if (!in_array($reservation->status, ['cancelled', 'checked_out'])) {
                    app(ReservationService::class)->cancelReservation($reservation->id, $reason);
                }
            });

            \App\Models\ActivityLog::record(
                'group_booking_cancelled',
                "Group booking cancelled: {$groupBooking->group_code}. Reason: $reason",
                $groupBooking
            );

            return $groupBooking->fresh();
        });
    }

    /**
     * Activate a group booking (when first guest checks in)
     */
    public function activateGroupBooking(int $groupBookingId): GroupBooking
    {
        $groupBooking = GroupBooking::findOrFail($groupBookingId);

        if ($groupBooking->status !== 'confirmed') {
            throw new \Exception('Only confirmed group bookings can be activated');
        }

        $groupBooking->update(['status' => 'active']);

        \App\Models\ActivityLog::record(
            'group_booking_activated',
            "Group booking activated: {$groupBooking->group_code}",
            $groupBooking
        );

        return $groupBooking->fresh();
    }

    /**
     * Complete a group booking (when last guest checks out)
     */
    public function completeGroupBooking(int $groupBookingId): GroupBooking
    {
        $groupBooking = GroupBooking::findOrFail($groupBookingId);

        // Check if all reservations are checked out
        $activeReservations = $groupBooking->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        if ($activeReservations > 0) {
            throw new \Exception("Cannot complete group booking. $activeReservations reservations still active.");
        }

        $groupBooking->update([
            'status' => 'completed',
        ]);

        // Update organizer guest stats
        $organizer = $groupBooking->organizer;
        if ($organizer) {
            app(GuestPreferenceService::class)->awardPoints(
                $organizer,
                1000,
                "Completed group booking: {$groupBooking->group_code}"
            );
        }

        \App\Models\ActivityLog::record(
            'group_booking_completed',
            "Group booking completed: {$groupBooking->group_code}",
            $groupBooking
        );

        return $groupBooking->fresh();
    }

    /**
     * Add benefit to group booking
     */
    public function addBenefit(int $groupBookingId, string $benefit): GroupBooking
    {
        $groupBooking = GroupBooking::findOrFail($groupBookingId);
        $groupBooking->addBenefit($benefit);

        \App\Models\ActivityLog::record(
            'group_benefit_added',
            "Benefit added to group {$groupBooking->group_code}: $benefit",
            $groupBooking,
            ['benefit' => $benefit]
        );

        return $groupBooking->fresh();
    }

    /**
     * Process payment for group booking
     */
    public function processPayment(int $groupBookingId, float $amount, string $method = 'cash'): GroupBooking
    {
        return DB::transaction(function () use ($groupBookingId, $amount, $method) {
            $groupBooking = GroupBooking::findOrFail($groupBookingId);

            $groupBooking->increment('paid_amount', $amount);

            // Recalculate payment status
            $newPercentage = $groupBooking->payment_percentage;
            $paymentStatus = $groupBooking->payment_status;

            if ($newPercentage >= 100) {
                $paymentStatus = 'paid';
            } elseif ($newPercentage > 0) {
                $paymentStatus = 'partial';
            }

            $groupBooking->update(['payment_status' => $paymentStatus]);

            \App\Models\ActivityLog::record(
                'group_payment_processed',
                "Payment processed for group {$groupBooking->group_code}: $amount using $method",
                $groupBooking,
                ['amount' => $amount, 'method' => $method, 'payment_status' => $paymentStatus]
            );

            return $groupBooking->fresh();
        });
    }

    /**
     * Get groups by type
     */
    public function getGroupsByType(string $type, int $tenantId): Collection
    {
        return GroupBooking::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Search groups
     */
    public function searchGroups(string $query, int $tenantId): Collection
    {
        return GroupBooking::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('group_name', 'like', "%{$query}%")
                    ->orWhere('group_code', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
