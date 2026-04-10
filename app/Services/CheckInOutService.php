<?php

namespace App\Services;

use App\Models\CheckInOut;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CheckInOutService — Handles check-in and check-out operations.
 *
 * Manages the check-in/check-out process, room assignment, and charge calculations.
 */
class CheckInOutService
{
    public function __construct(
        private RoomAvailabilityService $availabilityService,
        private HousekeepingService $housekeepingService,
        private HousekeepingStatusService $housekeepingStatusService // BUG-HOTEL-003 FIX
    ) {
    }

    /**
     * Process check-in for a reservation.
     * - Validates reservation status is 'confirmed'
     * - Assigns room if not already assigned (or use provided room_id)
     * - Creates CheckInOut record with type='check_in'
     * - Updates reservation status to 'checked_in'
     * - Updates room status to 'occupied'
     * - Updates guest total_stays and last_stay_at
     * Wraps in DB::transaction.
     *
     * @param int $reservationId
     * @param array $data
     * @return CheckInOut
     */
    public function processCheckIn(int $reservationId, array $data): CheckInOut
    {
        return DB::transaction(function () use ($reservationId, $data) {
            $reservation = Reservation::with(['roomType', 'guest'])
                ->findOrFail($reservationId);

            // Validate reservation status
            if (!$reservation->isConfirmed()) {
                throw new \RuntimeException(
                    "Reservation must be confirmed before check-in. Current status: {$reservation->status}"
                );
            }

            // Determine room to assign
            $room = null;
            if (!empty($data['room_id'])) {
                // Use provided room
                $room = Room::findOrFail($data['room_id']);

                // Validate room
                if ($room->tenant_id !== $reservation->tenant_id) {
                    throw new \RuntimeException("Room does not belong to this tenant.");
                }

                if ($room->room_type_id !== $reservation->room_type_id) {
                    throw new \RuntimeException("Room type does not match the reservation.");
                }

                // Check room availability
                $isAvailable = $this->availabilityService->isRoomAvailable(
                    $room->id,
                    $reservation->check_in_date->toDateString(),
                    $reservation->check_out_date->toDateString(),
                    $reservationId
                );

                if (!$isAvailable) {
                    throw new \RuntimeException("Selected room is not available for the reservation dates.");
                }
            } elseif ($reservation->room_id) {
                // Use already assigned room
                $room = Room::find($reservation->room_id);
            } else {
                // Auto-assign room
                $room = $this->autoAssignRoom($reservation);

                if (!$room) {
                    throw new \RuntimeException("No available rooms for this reservation.");
                }
            }

            // Update room status
            $room->update(['status' => 'occupied']);

            // Update reservation with room assignment
            $reservation->update([
                'room_id' => $room->id,
                'status' => 'checked_in',
            ]);

            // Create or update reservation_room record
            \App\Models\ReservationRoom::updateOrCreate(
                [
                    'reservation_id' => $reservationId,
                    'room_id' => $room->id,
                ],
                [
                    'check_in_date' => $reservation->check_in_date->toDateString(),
                    'check_out_date' => $reservation->check_out_date->toDateString(),
                    'rate_per_night' => $reservation->rate_per_night,
                    'status' => 'checked_in',
                ]
            );

            // Create CheckInOut record
            $checkInOut = CheckInOut::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservationId,
                'room_id' => $room->id,
                'guest_id' => $reservation->guest_id,
                'type' => 'check_in',
                'processed_at' => now(),
                'processed_by' => $data['processed_by'] ?? null,
                'key_card_number' => $data['key_card_number'] ?? null,
                'deposit_amount' => $data['deposit_amount'] ?? null,
                'deposit_method' => $data['deposit_method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update guest stats
            $guest = Guest::find($reservation->guest_id);
            if ($guest) {
                $guest->update([
                    'total_stays' => $guest->total_stays + 1,
                    'last_stay_at' => now(),
                ]);
            }

            Log::info('Check-in processed', [
                'reservation_id' => $reservationId,
                'room_id' => $room->id,
                'room_number' => $room->number,
                'guest_id' => $reservation->guest_id,
            ]);

            return $checkInOut;
        });
    }

    /**
     * Process check-out.
     * - Validates reservation status is 'checked_in'
     * - Creates CheckInOut record with type='check_out'
     * - Updates reservation status to 'checked_out'
     * - Updates room status to 'cleaning'
     * - Auto-creates housekeeping task (checkout_clean) via HousekeepingService
     * Wraps in DB::transaction.
     *
     * @param int $reservationId
     * @param array $data
     * @return CheckInOut
     */
    public function processCheckOut(int $reservationId, array $data): CheckInOut
    {
        return DB::transaction(function () use ($reservationId, $data) {
            $reservation = Reservation::with(['room', 'guest'])
                ->findOrFail($reservationId);

            // Validate reservation status
            if (!$reservation->isCheckedIn()) {
                throw new \RuntimeException(
                    "Reservation must be checked in before check-out. Current status: {$reservation->status}"
                );
            }

            $room = $reservation->room;

            if (!$room) {
                throw new \RuntimeException("No room assigned to this reservation.");
            }

            // Calculate final charges
            $charges = $this->calculateCharges($reservationId);

            // Create CheckInOut record
            $checkInOut = CheckInOut::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservationId,
                'room_id' => $room->id,
                'guest_id' => $reservation->guest_id,
                'type' => 'check_out',
                'processed_at' => now(),
                'processed_by' => $data['processed_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update reservation status
            $reservation->update([
                'status' => 'checked_out',
                'grand_total' => $charges['grand_total'],
            ]);

            // Update reservation_room status
            \App\Models\ReservationRoom::where('reservation_id', $reservationId)
                ->where('room_id', $room->id)
                ->update(['status' => 'checked_out']);

            // BUG-HOTEL-003 FIX: Mark room as dirty (not cleaning!)
            // Room must go through proper cleaning workflow: dirty → cleaning → clean → available
            $this->housekeepingStatusService->markRoomDirty($room, 'checkout');

            Log::info('Check-out processed', [
                'reservation_id' => $reservationId,
                'room_id' => $room->id,
                'room_number' => $room->number,
                'room_status_after_checkout' => 'dirty',
                'guest_id' => $reservation->guest_id,
                'total_charges' => $charges['grand_total'],
            ]);

            return $checkInOut;
        });
    }

    /**
     * Auto-assign the best available room for a reservation based on room type.
     * Prefers lowest floor number, then lowest room number.
     *
     * @param Reservation $reservation
     * @return Room|null
     */
    public function autoAssignRoom(Reservation $reservation): ?Room
    {
        return $this->availabilityService->findBestAvailableRoom(
            $reservation->room_type_id,
            $reservation->check_in_date->toDateString(),
            $reservation->check_out_date->toDateString()
        );
    }

    /**
     * Calculate all charges for a reservation (room charges, extras, tax, deposit).
     * Returns summary array.
     *
     * @param int $reservationId
     * @return array
     */
    public function calculateCharges(int $reservationId): array
    {
        $reservation = Reservation::with(['roomType', 'checkInOuts', 'minibarCharges.item'])
            ->findOrFail($reservationId);

        // Base room charges
        $roomCharge = (float) $reservation->total_amount;

        // Get minibar charges
        $minibarCharges = $reservation->minibarCharges()
            ->where('status', 'pending')
            ->get();

        $minibarTotal = $minibarCharges->sum('total');
        $minibarItems = $minibarCharges->map(function ($charge) {
            return [
                'item' => $charge->item->name,
                'quantity' => $charge->quantity,
                'unit_price' => (float) $charge->unit_price,
                'total' => (float) $charge->total,
            ];
        })->toArray();

        // Additional charges (manual additions)
        $additionalCharges = (float) ($reservation->additional_charges ?? 0);

        // Get deposits paid
        $checkIn = $reservation->checkInOuts()->where('type', 'check_in')->first();
        $depositPaid = $checkIn ? (float) $checkIn->deposit_amount : 0;

        // Apply discount
        $discount = (float) $reservation->discount;

        // Calculate subtotal
        $subtotal = $roomCharge + $minibarTotal + $additionalCharges - $discount;

        // Calculate tax
        $taxRate = $this->getTaxRate($reservation->tenant_id);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        // Grand total
        $grandTotal = round($subtotal + $taxAmount, 2);

        // Balance due
        $balanceDue = max(0, $grandTotal - $depositPaid);

        return [
            'room_charge' => round($roomCharge, 2),
            'minibar_charges' => round($minibarTotal, 2),
            'minibar_items' => $minibarItems,
            'additional_charges' => round($additionalCharges, 2),
            'discount' => round($discount, 2),
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'deposit_paid' => round($depositPaid, 2),
            'balance_due' => round($balanceDue, 2),
            'nights' => $reservation->nights,
            'rate_per_night' => (float) $reservation->rate_per_night,
            'check_in_date' => $reservation->check_in_date->toDateString(),
            'check_out_date' => $reservation->check_out_date->toDateString(),
        ];
    }

    /**
     * Extend a guest's stay (modify check-out date).
     *
     * @param int $reservationId
     * @param string $newCheckOutDate
     * @return Reservation
     */
    public function extendStay(int $reservationId, string $newCheckOutDate): Reservation
    {
        return DB::transaction(function () use ($reservationId, $newCheckOutDate) {
            $reservation = Reservation::with('room')->findOrFail($reservationId);

            if (!$reservation->isCheckedIn()) {
                throw new \RuntimeException("Can only extend stay for checked-in reservations.");
            }

            $newCheckOut = Carbon::parse($newCheckOutDate);
            $currentCheckOut = $reservation->check_out_date;

            if ($newCheckOut->lte($currentCheckOut)) {
                throw new \RuntimeException("New check-out date must be after current check-out date.");
            }

            // Check if room is available for extended dates
            $isAvailable = $this->availabilityService->isRoomAvailable(
                $reservation->room_id,
                $currentCheckOut->toDateString(),
                $newCheckOut->toDateString(),
                $reservationId
            );

            if (!$isAvailable) {
                throw new \RuntimeException("Room is not available for the extended dates.");
            }

            // Recalculate rates and totals
            $rateService = app(RateManagementService::class);
            $extensionRates = $rateService->getRatesForDateRange(
                $reservation->room_type_id,
                $currentCheckOut->toDateString(),
                $newCheckOut->toDateString(),
                $reservation->tenant_id
            );

            $additionalCharge = array_sum($extensionRates);
            $newTotal = $reservation->total_amount + $additionalCharge;
            $newNights = $reservation->check_in_date->diffInDays($newCheckOut);

            // Recalculate tax and grand total
            $taxRate = $this->getTaxRate($reservation->tenant_id);
            $taxAmount = round(($newTotal - $reservation->discount) * ($taxRate / 100), 2);
            $grandTotal = round($newTotal - $reservation->discount + $taxAmount, 2);

            // Update reservation
            $reservation->update([
                'check_out_date' => $newCheckOut->toDateString(),
                'nights' => $newNights,
                'total_amount' => round($newTotal, 2),
                'tax' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);

            // Update reservation_room
            \App\Models\ReservationRoom::where('reservation_id', $reservationId)
                ->where('room_id', $reservation->room_id)
                ->update(['check_out_date' => $newCheckOut->toDateString()]);

            Log::info('Stay extended', [
                'reservation_id' => $reservationId,
                'new_check_out' => $newCheckOut->toDateString(),
                'additional_charge' => $additionalCharge,
            ]);

            return $reservation->fresh();
        });
    }

    /**
     * Early check-out (before scheduled date).
     *
     * @param int $reservationId
     * @param array $data
     * @return CheckInOut
     */
    public function earlyCheckOut(int $reservationId, array $data = []): CheckInOut
    {
        return DB::transaction(function () use ($reservationId, $data) {
            $reservation = Reservation::findOrFail($reservationId);

            if (!$reservation->isCheckedIn()) {
                throw new \RuntimeException("Reservation must be checked in.");
            }

            // Recalculate charges based on actual nights stayed
            $actualNights = $reservation->check_in_date->diffInDays(now());

            // Note: This doesn't automatically reduce charges - policy decision
            // Hotel may charge for full reservation or apply cancellation fees

            return $this->processCheckOut($reservationId, array_merge($data, [
                'notes' => ($data['notes'] ?? '') . ' (Early check-out after ' . $actualNights . ' nights)',
            ]));
        });
    }

    /**
     * Get today's arrivals (reservations checking in today).
     *
     * @param int $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getTodaysArrivals(int $tenantId): \Illuminate\Support\Collection
    {
        return Reservation::with(['guest', 'roomType', 'room'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', today())
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get today's departures (reservations checking out today).
     *
     * @param int $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getTodaysDepartures(int $tenantId): \Illuminate\Support\Collection
    {
        return Reservation::with(['guest', 'room', 'roomType'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->orderBy('check_out_date')
            ->get();
    }

    /**
     * Get in-house guests (currently checked in).
     *
     * @param int $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getInHouseGuests(int $tenantId): \Illuminate\Support\Collection
    {
        return Reservation::with(['guest', 'room', 'roomType'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'checked_in')
            ->orderBy('check_in_date')
            ->get();
    }

    /**
     * Change room for a checked-in guest.
     *
     * @param int $reservationId
     * @param int $newRoomId
     * @param string|null $reason
     * @return array
     */
    public function changeRoom(int $reservationId, int $newRoomId, ?string $reason = null): array
    {
        return DB::transaction(function () use ($reservationId, $newRoomId, $reason) {
            $reservation = Reservation::with('room', 'roomType')->findOrFail($reservationId);

            if (!$reservation->isCheckedIn()) {
                throw new \RuntimeException("Can only change room for checked-in reservations.");
            }

            $oldRoom = $reservation->room;
            $newRoom = Room::findOrFail($newRoomId);

            // Validate new room
            if ($newRoom->tenant_id !== $reservation->tenant_id) {
                throw new \RuntimeException("Room does not belong to this tenant.");
            }

            // Check if room type is different (upgrade/downgrade)
            $isUpgrade = $newRoom->room_type_id !== $reservation->room_type_id;
            $changeType = 'same_type';
            $rateDifference = 0;

            if ($isUpgrade) {
                $oldRate = $reservation->rate_per_night;
                $newRate = $newRoom->roomType->base_rate;
                $rateDifference = $newRate - $oldRate;
                $changeType = $rateDifference > 0 ? 'upgrade' : 'downgrade';
            }

            // Check new room availability for remaining dates
            $isAvailable = $this->availabilityService->isRoomAvailable(
                $newRoomId,
                now()->toDateString(),
                $reservation->check_out_date->toDateString(),
                $reservationId
            );

            if (!$isAvailable) {
                throw new \RuntimeException("New room is not available for the remaining dates.");
            }

            // Calculate remaining nights
            $remainingNights = now()->diffInDays($reservation->check_out_date);
            $totalRateDifference = $rateDifference * $remainingNights;

            // Update old reservation_room
            \App\Models\ReservationRoom::where('reservation_id', $reservationId)
                ->where('room_id', $oldRoom->id)
                ->update(['status' => 'changed']);

            // Update reservation with new room
            $reservation->update([
                'room_id' => $newRoomId,
                'room_type_id' => $newRoom->room_type_id,
                'rate_per_night' => $isUpgrade ? $newRoom->roomType->base_rate : $reservation->rate_per_night,
                'total_amount' => $reservation->total_amount + $totalRateDifference,
            ]);

            // Recalculate grand total with new rate
            $charges = $this->calculateCharges($reservationId);
            $reservation->update(['grand_total' => $charges['grand_total']]);

            // Create new reservation_room
            \App\Models\ReservationRoom::create([
                'reservation_id' => $reservationId,
                'room_id' => $newRoomId,
                'check_in_date' => now()->toDateString(),
                'check_out_date' => $reservation->check_out_date->toDateString(),
                'rate_per_night' => $reservation->rate_per_night,
                'status' => 'checked_in',
            ]);

            // Create room change record
            $roomChange = \App\Models\ReservationRoomChange::create([
                'tenant_id' => $reservation->tenant_id,
                'reservation_id' => $reservationId,
                'from_room_id' => $oldRoom->id,
                'to_room_id' => $newRoomId,
                'room_type_id' => $newRoom->room_type_id,
                'change_type' => $changeType,
                'effective_date' => now(),
                'rate_difference' => $rateDifference,
                'reason' => $reason,
                'notes' => "Room changed from {$oldRoom->number} to {$newRoom->number}. " .
                    "Rate difference: Rp " . number_format($rateDifference, 0, ',', '.') . " per night. " .
                    "Remaining nights: {$remainingNights}. Total difference: Rp " . number_format($totalRateDifference, 0, ',', '.'),
                'processed_by' => Auth::id(),
            ]);

            // Update old room status
            $this->housekeepingStatusService->markRoomDirty($oldRoom, 'room_change');

            // Update new room status
            $newRoom->update(['status' => 'occupied']);

            Log::info('Room changed', [
                'reservation_id' => $reservationId,
                'old_room_id' => $oldRoom->id,
                'new_room_id' => $newRoomId,
                'change_type' => $changeType,
                'rate_difference' => $rateDifference,
                'total_difference' => $totalRateDifference,
                'reason' => $reason,
            ]);

            return [
                'reservation' => $reservation->fresh(),
                'room_change' => $roomChange,
                'rate_difference' => $rateDifference,
                'total_difference' => $totalRateDifference,
                'remaining_nights' => $remainingNights,
                'change_type' => $changeType,
            ];
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
}
