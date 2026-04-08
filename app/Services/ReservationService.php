<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ReservationService — Handles reservation creation, confirmation, and cancellation.
 *
 * Validates availability, calculates rates, generates reservation numbers.
 */
class ReservationService
{
    public function __construct(
        private RoomAvailabilityService $availabilityService,
        private RateManagementService $rateService
    ) {
    }

    /**
     * Create a new reservation. Validates availability, calculates rates, generates reservation number.
     * Wraps in DB::transaction.
     *
     * @param array $data
     * @return Reservation
     * @throws \Exception
     */
    public function createReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $data['tenant_id'];
            $roomTypeId = $data['room_type_id'];
            $checkIn = Carbon::parse($data['check_in_date']);
            $checkOut = Carbon::parse($data['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            // Validate room type exists and is active
            $roomType = \App\Models\RoomType::where('id', $roomTypeId)
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();

            if (!$roomType) {
                throw new \RuntimeException("Room type not found or inactive.");
            }

            // BUG-HOTEL-001 FIX: Check availability with pessimistic locking
            // This prevents race conditions where two requests book same room
            $availableRooms = $this->availabilityService->getAvailableRoomsLocked(
                tenantId: $tenantId,
                checkIn: $checkIn->toDateString(),
                checkOut: $checkOut->toDateString(),
                roomTypeId: $roomTypeId
            );

            if ($availableRooms->isEmpty()) {
                throw new \RuntimeException("No rooms available for the selected dates.");
            }

            // Calculate rates
            $rateCalculation = $this->calculateRate(
                $roomTypeId,
                $checkIn->toDateString(),
                $checkOut->toDateString(),
                $tenantId
            );

            // Generate reservation number
            $reservationNumber = $this->generateReservationNumber($tenantId);

            // Calculate totals
            $totalAmount = $rateCalculation['total'];
            $discount = (float) ($data['discount'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? $this->getTaxRate($tenantId));
            $taxAmount = round(($totalAmount - $discount) * ($taxRate / 100), 2);
            $grandTotal = round($totalAmount - $discount + $taxAmount, 2);

            // Create reservation
            $reservation = Reservation::create([
                'tenant_id' => $tenantId,
                'guest_id' => $data['guest_id'],
                'room_type_id' => $roomTypeId,
                'room_id' => $data['room_id'] ?? null,
                'reservation_number' => $reservationNumber,
                'status' => $data['status'] ?? 'pending',
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'nights' => $nights,
                'rate_per_night' => $rateCalculation['rate_per_night'],
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $taxAmount,
                'grand_total' => $grandTotal,
                'source' => $data['source'] ?? 'direct',
                'special_requests' => $data['special_requests'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            // If specific room was provided, validate and assign
            if (!empty($data['room_id'])) {
                // BUG-HOTEL-001 FIX: Re-check availability with lock inside transaction
                $isAvailable = $this->availabilityService->isRoomAvailableLocked(
                    $data['room_id'],
                    $checkIn->toDateString(),
                    $checkOut->toDateString()
                );

                if (!$isAvailable) {
                    throw new \RuntimeException("The specified room is not available for the selected dates. It may have been booked by another request.");
                }

                // Create reservation_room record
                ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $data['room_id'],
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'rate_per_night' => $rateCalculation['rate_per_night'],
                    'status' => 'assigned',
                ]);
            }

            Log::info('Reservation created', [
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservationNumber,
                'tenant_id' => $tenantId,
                'guest_id' => $data['guest_id'],
            ]);

            return $reservation;
        });
    }

    /**
     * Confirm a pending reservation. Changes status to 'confirmed'.
     *
     * @param int $reservationId
     * @return Reservation
     */
    public function confirmReservation(int $reservationId): Reservation
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (!in_array($reservation->status, ['pending'])) {
            throw new \RuntimeException("Reservation cannot be confirmed. Current status: {$reservation->status}");
        }

        $reservation->update(['status' => 'confirmed']);

        Log::info('Reservation confirmed', [
            'reservation_id' => $reservationId,
            'reservation_number' => $reservation->reservation_number,
        ]);

        return $reservation->fresh();
    }

    /**
     * Cancel a reservation. Sets status to 'cancelled', records reason and timestamp.
     * If room was assigned, frees it. If checked_in, throws exception.
     *
     * @param int $reservationId
     * @param string|null $reason
     * @return Reservation
     */
    public function cancelReservation(int $reservationId, ?string $reason = null): Reservation
    {
        return DB::transaction(function () use ($reservationId, $reason) {
            $reservation = Reservation::findOrFail($reservationId);

            if ($reservation->status === 'checked_in') {
                throw new \RuntimeException("Cannot cancel a reservation that is already checked in. Process checkout first.");
            }

            if ($reservation->status === 'cancelled') {
                throw new \RuntimeException("Reservation is already cancelled.");
            }

            // Update reservation status
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            // Update reservation_rooms status if any
            ReservationRoom::where('reservation_id', $reservationId)
                ->update(['status' => 'cancelled']);

            Log::info('Reservation cancelled', [
                'reservation_id' => $reservationId,
                'reservation_number' => $reservation->reservation_number,
                'reason' => $reason,
            ]);

            return $reservation->fresh();
        });
    }

    /**
     * Calculate total rate for a room type over a date range.
     * Uses RateManagementService to get effective rate per night.
     * Returns ['nights' => int, 'rate_per_night' => avg, 'total' => sum, 'breakdown' => [date => rate]]
     *
     * @param int $roomTypeId
     * @param string $checkIn
     * @param string $checkOut
     * @param int $tenantId
     * @return array
     */
    public function calculateRate(int $roomTypeId, string $checkIn, string $checkOut, int $tenantId): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        $breakdown = [];
        $total = 0;
        $current = $checkInDate->copy();

        while ($current->lt($checkOutDate)) {
            $dateStr = $current->toDateString();
            $rate = $this->rateService->getEffectiveRate($roomTypeId, $dateStr, $tenantId);
            $breakdown[$dateStr] = $rate;
            $total += $rate;
            $current->addDay();
        }

        $nights = count($breakdown);
        $avgRate = $nights > 0 ? round($total / $nights, 2) : 0;

        return [
            'nights' => $nights,
            'rate_per_night' => $avgRate,
            'total' => round($total, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Generate reservation number: RSV-YYYY/MMDD-SEQ (3-digit sequence per day per tenant).
     *
     * @param int $tenantId
     * @return string
     */
    public function generateReservationNumber(int $tenantId): string
    {
        $today = now();
        $prefix = 'RSV-' . $today->format('Y/md');

        // Get the highest sequence number for today
        $lastReservation = Reservation::where('tenant_id', $tenantId)
            ->where('reservation_number', 'like', $prefix . '-%')
            ->orderBy('reservation_number', 'desc')
            ->first();

        if ($lastReservation) {
            $lastNumber = (int) substr($lastReservation->reservation_number, -3);
            $sequence = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        return $prefix . '-' . $sequence;
    }

    /**
     * Check for conflicting reservations on a specific room for date range.
     *
     * @param int $roomId
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $excludeReservationId
     * @return Collection
     */
    public function checkConflicts(
        int $roomId,
        string $checkIn,
        string $checkOut,
        ?int $excludeReservationId = null
    ): Collection {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Get conflicts from reservations table
        $conflicts = Reservation::where('room_id', $roomId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn($q) => $q->where('id', '!=', $excludeReservationId))
            ->get();

        // Get conflicts from reservation_rooms
        $reservationRoomConflicts = ReservationRoom::whereHas('reservation', function ($q) {
            $q->whereIn('status', ['confirmed', 'checked_in']);
        })
            ->where('room_id', $roomId)
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->with('reservation')
            ->get()
            ->pluck('reservation');

        return $conflicts->merge($reservationRoomConflicts)->unique('id')->values();
    }

    /**
     * Assign a room to a reservation.
     *
     * @param int $reservationId
     * @param int $roomId
     * @return Reservation
     */
    public function assignRoom(int $reservationId, int $roomId): Reservation
    {
        return DB::transaction(function () use ($reservationId, $roomId) {
            $reservation = Reservation::findOrFail($reservationId);
            $room = Room::findOrFail($roomId);

            // Verify room belongs to the same tenant
            if ($room->tenant_id !== $reservation->tenant_id) {
                throw new \RuntimeException("Room does not belong to this tenant.");
            }

            // Verify room type matches
            if ($room->room_type_id !== $reservation->room_type_id) {
                throw new \RuntimeException("Room type does not match the reservation.");
            }

            // Check room availability
            $isAvailable = $this->availabilityService->isRoomAvailable(
                $roomId,
                $reservation->check_in_date->toDateString(),
                $reservation->check_out_date->toDateString(),
                $reservationId
            );

            if (!$isAvailable) {
                throw new \RuntimeException("Room is not available for the reservation dates.");
            }

            // Assign room
            $reservation->update(['room_id' => $roomId]);

            // Create/update reservation_room
            ReservationRoom::updateOrCreate(
                [
                    'reservation_id' => $reservationId,
                    'room_id' => $roomId,
                ],
                [
                    'check_in_date' => $reservation->check_in_date->toDateString(),
                    'check_out_date' => $reservation->check_out_date->toDateString(),
                    'rate_per_night' => $reservation->rate_per_night,
                    'status' => 'assigned',
                ]
            );

            Log::info('Room assigned to reservation', [
                'reservation_id' => $reservationId,
                'room_id' => $roomId,
            ]);

            return $reservation->fresh();
        });
    }

    /**
     * Update reservation details.
     *
     * @param int $reservationId
     * @param array $data
     * @return Reservation
     */
    public function updateReservation(int $reservationId, array $data): Reservation
    {
        return DB::transaction(function () use ($reservationId, $data) {
            $reservation = Reservation::findOrFail($reservationId);

            // If dates are being changed, recalculate nights and rates
            if (isset($data['check_in_date']) || isset($data['check_out_date'])) {
                $checkIn = Carbon::parse($data['check_in_date'] ?? $reservation->check_in_date);
                $checkOut = Carbon::parse($data['check_out_date'] ?? $reservation->check_out_date);
                $data['nights'] = $checkIn->diffInDays($checkOut);

                // Recalculate rates if dates changed
                $rateCalculation = $this->calculateRate(
                    $reservation->room_type_id,
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                    $reservation->tenant_id
                );

                $data['rate_per_night'] = $rateCalculation['rate_per_night'];
                $data['total_amount'] = $rateCalculation['total'];

                // Recalculate tax and grand total
                $discount = $data['discount'] ?? $reservation->discount;
                $taxRate = $this->getTaxRate($reservation->tenant_id);
                $data['tax'] = round(($rateCalculation['total'] - $discount) * ($taxRate / 100), 2);
                $data['grand_total'] = round($rateCalculation['total'] - $discount + $data['tax'], 2);

                // Check if assigned room is still available for new dates
                if ($reservation->room_id) {
                    $isAvailable = $this->availabilityService->isRoomAvailable(
                        $reservation->room_id,
                        $checkIn->toDateString(),
                        $checkOut->toDateString(),
                        $reservationId
                    );

                    if (!$isAvailable) {
                        // Unassign room if no longer available
                        $data['room_id'] = null;
                        ReservationRoom::where('reservation_id', $reservationId)
                            ->update(['status' => 'changed']);
                    }
                }
            }

            $reservation->update($data);

            Log::info('Reservation updated', [
                'reservation_id' => $reservationId,
            ]);

            return $reservation->fresh();
        });
    }

    /**
     * Get tax rate from hotel settings.
     *
     * @param int $tenantId
     * @return float
     */
    private function getTaxRate(int $tenantId): float
    {
        $settings = \App\Models\HotelSetting::where('tenant_id', $tenantId)->first();
        return $settings?->tax_rate ?? 0.0;
    }

    /**
     * Get reservations for a guest.
     *
     * @param int $guestId
     * @return Collection
     */
    public function getReservationsByGuest(int $guestId): Collection
    {
        return Reservation::where('guest_id', $guestId)
            ->with(['roomType', 'room', 'checkInOuts'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Search reservations by various criteria.
     *
     * @param int $tenantId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchReservations(int $tenantId, array $filters = [])
    {
        $query = Reservation::where('tenant_id', $tenantId)
            ->with(['guest', 'roomType', 'room']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['guest_id'])) {
            $query->where('guest_id', $filters['guest_id']);
        }

        if (!empty($filters['room_type_id'])) {
            $query->where('room_type_id', $filters['room_type_id']);
        }

        if (!empty($filters['check_in_from'])) {
            $query->where('check_in_date', '>=', $filters['check_in_from']);
        }

        if (!empty($filters['check_in_to'])) {
            $query->where('check_in_date', '<=', $filters['check_in_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('check_in_date', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Process room upgrade or downgrade
     */
    public function processRoomChange(
        int $reservationId,
        int $toRoomId,
        int $roomTypeId,
        string $changeType,
        float $rateDifference,
        string $reason,
        ?string $notes = null
    ): \App\Models\ReservationRoomChange {
        return DB::transaction(function () use ($reservationId, $toRoomId, $roomTypeId, $changeType, $rateDifference, $reason, $notes) {
            $reservation = Reservation::findOrFail($reservationId);

            if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
                throw new \Exception('Can only change rooms for confirmed or checked-in reservations');
            }

            $fromRoomId = $reservation->room_id;

            // Create room change record
            $roomChange = \App\Models\ReservationRoomChange::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservationId,
                'from_room_id' => $fromRoomId,
                'to_room_id' => $toRoomId,
                'room_type_id' => $roomTypeId,
                'change_type' => $changeType,
                'effective_date' => now(),
                'rate_difference' => $rateDifference,
                'reason' => $reason,
                'notes' => $notes,
                'processed_by' => null, // Will be set by controller with auth user ID
            ]);

            // Update reservation with new room
            $reservation->update([
                'room_id' => $toRoomId,
                'room_type_id' => $roomTypeId,
                'rate_per_night' => $reservation->rate_per_night + $rateDifference,
                'total_amount' => round($reservation->total_amount + ($rateDifference * $reservation->nights), 2),
                'grand_total' => round($reservation->grand_total + ($rateDifference * $reservation->nights), 2),
            ]);

            // Update room status
            if ($fromRoomId) {
                $fromRoom = \App\Models\Room::find($fromRoomId);
                if ($fromRoom && $reservation->status !== 'checked_in') {
                    $fromRoom->update(['status' => 'available']);
                }
            }

            $toRoom = \App\Models\Room::find($toRoomId);
            if ($toRoom && $reservation->status === 'checked_in') {
                $toRoom->update(['status' => 'occupied']);
            }

            \App\Models\ActivityLog::record(
                'room_changed',
                "Room {$changeType} for reservation {$reservation->reservation_number}: " .
                "Room " . ($fromRoom?->number ?? 'N/A') . " -> Room {$toRoom->number}",
                $reservation,
                [
                    'from_room_id' => $fromRoomId,
                    'to_room_id' => $toRoomId,
                    'change_type' => $changeType,
                    'rate_difference' => $rateDifference,
                ]
            );

            return $roomChange;
        });
    }

    /**
     * Request early check-in or late check-out
     */
    public function requestEarlyLate(
        int $reservationId,
        string $requestType,
        string $requestedTime,
        string $reason,
        ?float $extraCharge = null
    ): \App\Models\EarlyLateRequest {
        $reservation = Reservation::findOrFail($reservationId);

        // Get standard time from hotel settings
        $hotelSettings = \App\Models\HotelSetting::where('tenant_id', $reservation->tenant_id)->first();
        $standardTime = $requestType === 'early_checkin'
            ? ($hotelSettings?->check_in_time ?? '14:00')
            : ($hotelSettings?->check_out_time ?? '12:00');

        // Calculate extra hours and charge
        $requestedTimestamp = \Carbon\Carbon::parse($requestedTime);
        $standardTimestamp = \Carbon\Carbon::parse($standardTime);

        $extraHours = 0;
        if ($requestType === 'early_checkin') {
            $extraHours = max(0, $standardTimestamp->diffInHours($requestedTimestamp, false));
        } else {
            $extraHours = max(0, $requestedTimestamp->diffInHours($standardTimestamp, false));
        }

        if ($extraCharge === null && $extraHours > 0) {
            // Default charge: 50% of daily rate per hour, max 100% for full day
            $extraCharge = min($reservation->rate_per_night, ($reservation->rate_per_night * 0.5 / 24) * $extraHours);
        }

        return \App\Models\EarlyLateRequest::create([
            'tenant_id' => $reservation->tenant_id,
            'reservation_id' => $reservationId,
            'guest_id' => $reservation->guest_id,
            'request_type' => $requestType,
            'requested_time' => $requestedTime,
            'standard_time' => $standardTime,
            'extra_hours' => $extraHours,
            'extra_charge' => $extraCharge ?? 0,
            'status' => 'pending',
            'reason' => $reason,
        ]);
    }

    /**
     * Approve early check-in or late check-out request
     */
    public function approveEarlyLateRequest(int $requestId): \App\Models\EarlyLateRequest
    {
        return DB::transaction(function () use ($requestId) {
            $request = \App\Models\EarlyLateRequest::findOrFail($requestId);
            $request->approve();

            // If early check-in and guest is already checked in, update actual check-in time
            if ($request->isEarlyCheckin()) {
                $reservation = $request->reservation;
                if ($reservation->status === 'checked_in') {
                    $reservation->update([
                        'actual_check_in_at' => $request->requested_time,
                    ]);
                }
            }

            // Add extra charge to reservation if applicable
            if ($request->extra_charge > 0) {
                $reservation = $request->reservation;
                $reservation->increment('grand_total', $request->extra_charge);

                \App\Models\ActivityLog::record(
                    'early_late_fee_applied',
                    "Extra charge applied for {$request->request_type}: {$request->extra_charge}",
                    $reservation,
                    ['extra_charge' => $request->extra_charge]
                );
            }

            \App\Models\ActivityLog::record(
                'early_late_request_approved',
                "{$request->request_type} approved for reservation {$request->reservation->reservation_number}",
                $request->reservation,
                [
                    'request_type' => $request->request_type,
                    'requested_time' => $request->requested_time,
                    'extra_charge' => $request->extra_charge,
                ]
            );

            return $request->fresh();
        });
    }

    /**
     * Reject early check-in or late check-out request
     */
    public function rejectEarlyLateRequest(int $requestId, string $reason): \App\Models\EarlyLateRequest
    {
        $request = \App\Models\EarlyLateRequest::findOrFail($requestId);
        $request->reject($reason);

        \App\Models\ActivityLog::record(
            'early_late_request_rejected',
            "{$request->request_type} rejected for reservation {$request->reservation->reservation_number}. Reason: $reason",
            $request->reservation,
            ['reason' => $reason]
        );

        return $request;
    }

    /**
     * Record actual check-in time
     */
    public function recordActualCheckIn(int $reservationId, ?\Carbon\Carbon $actualTime = null): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        $reservation->update([
            'actual_check_in_at' => $actualTime ?? now(),
        ]);

        \App\Models\ActivityLog::record(
            'actual_check_in_recorded',
            "Actual check-in recorded for reservation {$reservation->reservation_number}",
            $reservation
        );
    }

    /**
     * Record actual check-out time
     */
    public function recordActualCheckOut(int $reservationId, ?\Carbon\Carbon $actualTime = null): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        $reservation->update([
            'actual_check_out_at' => $actualTime ?? now(),
        ]);

        // Complete any active group booking if this is the last reservation
        if ($reservation->group_booking_id) {
            $groupBooking = \App\Models\GroupBooking::find($reservation->group_booking_id);
            if ($groupBooking && $groupBooking->status === 'active') {
                $activeReservations = $groupBooking->reservations()
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->count();

                if ($activeReservations === 0) {
                    app(\App\Services\GroupBookingService::class)->completeGroupBooking($groupBooking->id);
                }
            }
        }

        \App\Models\ActivityLog::record(
            'actual_check_out_recorded',
            "Actual check-out recorded for reservation {$reservation->reservation_number}",
            $reservation
        );
    }
}
