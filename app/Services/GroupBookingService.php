<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CheckInOut;
use App\Models\GroupBooking;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            ActivityLog::record(
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

            ActivityLog::record(
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

            if (! $groupBookingId) {
                throw new \Exception('Reservation is not part of a group booking');
            }

            $reservation->update(['group_booking_id' => null]);

            // Update group totals
            $groupBooking = GroupBooking::findOrFail($groupBookingId);
            $this->recalculateGroupTotals($groupBooking);

            ActivityLog::record(
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

        ActivityLog::record(
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
                'notes' => trim(($groupBooking->notes ?? '')."\n\nCancellation Reason: $reason"),
            ]);

            // Cancel all reservations in the group
            $groupBooking->reservations()->each(function ($reservation) use ($reason) {
                if (! in_array($reservation->status, ['cancelled', 'checked_out'])) {
                    app(ReservationService::class)->cancelReservation($reservation->id, $reason);
                }
            });

            ActivityLog::record(
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

        ActivityLog::record(
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

        ActivityLog::record(
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

        ActivityLog::record(
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

            ActivityLog::record(
                'group_payment_processed',
                "Payment processed for group {$groupBooking->group_code}: $amount using $method",
                $groupBooking,
                ['amount' => $amount, 'method' => $method, 'payment_status' => $paymentStatus]
            );

            return $groupBooking->fresh();
        });
    }

    /**
     * Create individual reservations from room block
     * This creates separate reservations for each room in the block
     */
    public function createIndividualReservations(int $groupBookingId, array $roomConfigs): Collection
    {
        return DB::transaction(function () use ($groupBookingId, $roomConfigs) {
            $groupBooking = GroupBooking::with('organizer')->findOrFail($groupBookingId);
            $createdReservations = collect();

            foreach ($roomConfigs as $roomConfig) {
                // Create guest for this room if not provided
                $guest = isset($roomConfig['guest_id'])
                    ? Guest::findOrFail($roomConfig['guest_id'])
                    : $this->createGuestForRoom($groupBooking, $roomConfig);

                // Create individual reservation
                $reservation = Reservation::create([
                    'tenant_id' => $groupBooking->tenant_id,
                    'guest_id' => $guest->id,
                    'group_booking_id' => $groupBookingId,
                    'room_type_id' => $roomConfig['room_type_id'],
                    'room_id' => $roomConfig['room_id'] ?? null,
                    'reservation_number' => Reservation::generateReservationNumber($groupBooking->tenant_id),
                    'status' => 'confirmed',
                    'check_in_date' => $groupBooking->start_date,
                    'check_out_date' => $groupBooking->end_date,
                    'adults' => $roomConfig['adults'] ?? 1,
                    'children' => $roomConfig['children'] ?? 0,
                    'nights' => $groupBooking->start_date->diffInDays($groupBooking->end_date),
                    'rate_per_night' => $roomConfig['rate_per_night'],
                    'total_amount' => $roomConfig['rate_per_night'] * $groupBooking->start_date->diffInDays($groupBooking->end_date),
                    'discount' => $roomConfig['discount'] ?? 0,
                    'tax' => 0, // Will be calculated
                    'grand_total' => 0, // Will be calculated
                    'source' => 'group_booking',
                    'special_requests' => $roomConfig['special_requests'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                // Calculate tax and grand total
                $taxRate = app(CheckInOutService::class)->calculateCharges($reservation->id)['tax_rate'] ?? 0;
                $taxAmount = round(($reservation->total_amount - $reservation->discount) * ($taxRate / 100), 2);
                $grandTotal = round($reservation->total_amount - $reservation->discount + $taxAmount, 2);

                $reservation->update([
                    'tax' => $taxAmount,
                    'grand_total' => $grandTotal,
                ]);

                // Create reservation_room if room assigned
                if ($reservation->room_id) {
                    ReservationRoom::create([
                        'reservation_id' => $reservation->id,
                        'room_id' => $reservation->room_id,
                        'check_in_date' => $reservation->check_in_date,
                        'check_out_date' => $reservation->check_out_date,
                        'rate_per_night' => $reservation->rate_per_night,
                        'status' => 'confirmed',
                    ]);
                }

                $createdReservations->push($reservation);
            }

            // Update group totals
            $this->recalculateGroupTotals($groupBooking);

            ActivityLog::record(
                'group_room_block_created',
                "Created {$createdReservations->count()} individual reservations for group {$groupBooking->group_code}",
                $groupBooking
            );

            return $createdReservations;
        });
    }

    /**
     * Create guest for room if not provided
     */
    private function createGuestForRoom(GroupBooking $groupBooking, array $roomConfig): Guest
    {
        $roomNumber = $roomConfig['room_number'] ?? '';
        $guestData = [
            'tenant_id' => $groupBooking->tenant_id,
            'first_name' => $roomConfig['guest_first_name'] ?? 'Guest',
            'last_name' => $roomConfig['guest_last_name'] ?? "Room {$roomNumber}",
            'email' => $roomConfig['guest_email'] ?? null,
            'phone' => $roomConfig['guest_phone'] ?? null,
            'id_type' => $roomConfig['guest_id_type'] ?? null,
            'id_number' => $roomConfig['guest_id_number'] ?? null,
        ];

        return Guest::create($guestData);
    }

    /**
     * Check in individual guest from group
     * Each reservation can be checked in independently
     */
    public function checkInGroupMember(int $reservationId, array $data = []): CheckInOut
    {
        $reservation = Reservation::with('groupBooking')->findOrFail($reservationId);

        if (! $reservation->group_booking_id) {
            throw new \Exception('Reservation is not part of a group booking');
        }

        // Use existing check-in service
        return app(CheckInOutService::class)->processCheckIn($reservationId, $data);
    }

    /**
     * Check out individual guest from group
     * Each reservation can be checked out independently
     */
    public function checkOutGroupMember(int $reservationId, array $data = []): CheckInOut
    {
        $reservation = Reservation::with('groupBooking')->findOrFail($reservationId);

        if (! $reservation->group_booking_id) {
            throw new \Exception('Reservation is not part of a group booking');
        }

        // Use existing check-out service
        $checkInOut = app(CheckInOutService::class)->processCheckOut($reservationId, $data);

        // Check if all group members have checked out
        $this->checkGroupCompletion($reservation->group_booking_id);

        return $checkInOut;
    }

    /**
     * Check if all group members have completed and auto-complete group
     */
    private function checkGroupCompletion(int $groupBookingId): void
    {
        $groupBooking = GroupBooking::findOrFail($groupBookingId);

        if ($groupBooking->status !== 'active') {
            return;
        }

        $activeReservations = $groupBooking->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        if ($activeReservations === 0) {
            $this->completeGroupBooking($groupBookingId);
        }
    }

    /**
     * Generate group billing summary
     * Consolidates all charges, payments, and balances
     */
    public function generateGroupBillingSummary(int $groupBookingId): array
    {
        $groupBooking = GroupBooking::with(['reservations.guest', 'reservations.room', 'organizer'])
            ->findOrFail($groupBookingId);

        $reservations = $groupBooking->reservations;

        // Calculate totals
        $totalRoomCharges = $reservations->sum('total_amount');
        $totalDiscounts = $reservations->sum('discount');
        $totalTaxes = $reservations->sum('tax');
        $grandTotal = $reservations->sum('grand_total');

        // Payment breakdown
        $totalPaid = $groupBooking->paid_amount;
        $balance = $grandTotal - $totalPaid;
        $paymentPercentage = $grandTotal > 0 ? round(($totalPaid / $grandTotal) * 100, 2) : 0;

        // Individual billing details
        $individualBills = $reservations->map(function ($reservation) {
            return [
                'reservation_number' => $reservation->reservation_number,
                'guest_name' => $reservation->guest->full_name,
                'room' => $reservation->room ? $reservation->room->number : 'Not assigned',
                'room_type' => $reservation->roomType->name,
                'check_in' => $reservation->check_in_date->format('Y-m-d'),
                'check_out' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->nights,
                'rate_per_night' => (float) $reservation->rate_per_night,
                'room_charge' => (float) $reservation->total_amount,
                'discount' => (float) $reservation->discount,
                'tax' => (float) $reservation->tax,
                'grand_total' => (float) $reservation->grand_total,
                'status' => $reservation->status,
            ];
        });

        // Payment history (if you have a payments table, otherwise use paid_amount)
        $paymentHistory = [
            'total_paid' => (float) $totalPaid,
            'payment_status' => $groupBooking->payment_status,
            'last_payment_at' => $groupBooking->updated_at,
        ];

        return [
            'group_booking' => [
                'group_code' => $groupBooking->group_code,
                'group_name' => $groupBooking->group_name,
                'organizer' => $groupBooking->organizer->full_name,
                'type' => $groupBooking->type,
                'status' => $groupBooking->status,
                'start_date' => $groupBooking->start_date->format('Y-m-d'),
                'end_date' => $groupBooking->end_date->format('Y-m-d'),
                'total_rooms' => $groupBooking->total_rooms,
                'total_guests' => $groupBooking->total_guests,
            ],
            'billing_summary' => [
                'total_room_charges' => round($totalRoomCharges, 2),
                'total_discounts' => round($totalDiscounts, 2),
                'total_taxes' => round($totalTaxes, 2),
                'grand_total' => round($grandTotal, 2),
                'total_paid' => round($totalPaid, 2),
                'balance_due' => round($balance, 2),
                'payment_percentage' => $paymentPercentage,
            ],
            'individual_bills' => $individualBills,
            'payment_history' => $paymentHistory,
        ];
    }

    /**
     * Process group payment (master billing)
     * Payment applied to entire group, not individual reservations
     */
    public function processGroupPayment(int $groupBookingId, float $amount, string $method = 'cash', ?string $reference = null): GroupBooking
    {
        return DB::transaction(function () use ($groupBookingId, $amount, $method, $reference) {
            $groupBooking = GroupBooking::findOrFail($groupBookingId);

            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than zero');
            }

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

            ActivityLog::record(
                'group_payment_processed',
                "Payment processed for group {$groupBooking->group_code}: $amount using $method",
                $groupBooking,
                ['amount' => $amount, 'method' => $method, 'payment_status' => $paymentStatus, 'reference' => $reference]
            );

            return $groupBooking->fresh();
        });
    }

    /**
     * Split group bill - allocate charges to individual guests
     */
    public function splitGroupBill(int $groupBookingId): array
    {
        $groupBooking = GroupBooking::with('reservations.guest')->findOrFail($groupBookingId);

        $totalAmount = $groupBooking->total_amount;
        $reservations = $groupBooking->reservations;

        // Calculate proportional split
        $splitDetails = $reservations->map(function ($reservation) use ($totalAmount) {
            $proportion = $totalAmount > 0 ? ($reservation->grand_total / $totalAmount) : 0;

            return [
                'reservation_number' => $reservation->reservation_number,
                'guest_name' => $reservation->guest->full_name,
                'room_charge' => (float) $reservation->grand_total,
                'proportion' => round($proportion * 100, 2),
                'status' => $reservation->status,
            ];
        });

        return [
            'group_code' => $groupBooking->group_code,
            'total_amount' => (float) $totalAmount,
            'split_details' => $splitDetails,
            'split_method' => 'proportional',
        ];
    }

    /**
     * Get all groups by type
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
