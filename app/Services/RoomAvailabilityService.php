<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * RoomAvailabilityService — Handles room availability checking and occupancy tracking.
 *
 * Provides methods for checking room availability, getting available rooms list,
 * and building occupancy calendars.
 */
class RoomAvailabilityService
{
    /**
     * Reservation statuses that block room availability.
     * BUG-HOTEL-001 FIX: Added 'pending' to prevent double booking during pending state
     */
    public const BLOCKING_STATUSES = ['pending', 'confirmed', 'checked_in'];

    /**
     * Get availability summary for date range, optionally filtered by room type.
     * Returns array of dates with available/total counts per room type.
     */
    public function getAvailability(
        int $tenantId,
        string $startDate,
        string $endDate,
        ?int $roomTypeId = null
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $availability = [];

        // Get all active room types for this tenant
        $roomTypesQuery = RoomType::where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($roomTypeId) {
            $roomTypesQuery->where('id', $roomTypeId);
        }

        $roomTypes = $roomTypesQuery->with('rooms')->get();

        // Build availability for each date
        $current = $start->copy();
        while ($current->lt($end)) {
            $dateStr = $current->toDateString();
            $dateAvailability = [
                'date' => $dateStr,
                'day_name' => $current->format('l'),
                'room_types' => [],
            ];

            foreach ($roomTypes as $roomType) {
                $totalRooms = $roomType->rooms->count();
                $occupiedRooms = $this->getOccupiedRoomsCount(
                    $tenantId,
                    $dateStr,
                    $roomType->id
                );
                $availableRooms = $totalRooms - $occupiedRooms;

                $dateAvailability['room_types'][$roomType->id] = [
                    'name' => $roomType->name,
                    'total' => $totalRooms,
                    'occupied' => $occupiedRooms,
                    'available' => max(0, $availableRooms),
                    'occupancy_rate' => $totalRooms > 0
                        ? round(($occupiedRooms / $totalRooms) * 100, 1)
                        : 0,
                ];
            }

            $availability[] = $dateAvailability;
            $current->addDay();
        }

        return $availability;
    }

    /**
     * Check if a specific room is available for given dates.
     * Excludes a specific reservation (for edit scenarios).
     * Checks against reservations with status in ['pending','confirmed','checked_in'].
     */
    public function isRoomAvailable(
        int $roomId,
        string $checkIn,
        string $checkOut,
        ?int $excludeReservationId = null
    ): bool {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Check if room exists and is active
        $room = Room::find($roomId);
        if (! $room || ! $room->is_active) {
            return false;
        }

        // Check for conflicts via room_id directly on reservation
        $conflictingReservation = Reservation::where('room_id', $roomId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->exists();

        if ($conflictingReservation) {
            return false;
        }

        // Check for conflicts via reservation_rooms
        $conflictingReservationRoom = ReservationRoom::whereHas('reservation', function ($q) {
            $q->whereIn('status', self::BLOCKING_STATUSES);
        })
            ->where('room_id', $roomId)
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn ($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->exists();

        return ! $conflictingReservationRoom;
    }

    /**
     * BUG-HOTEL-001 FIX: Check if room is available with pessimistic locking.
     * This must be called inside a DB::transaction to be effective.
     * Locks the room row to prevent concurrent bookings.
     */
    public function isRoomAvailableLocked(
        int $roomId,
        string $checkIn,
        string $checkOut,
        ?int $excludeReservationId = null
    ): bool {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // BUG-HOTEL-001 FIX: Lock the room row to prevent race conditions
        $room = Room::where('id', $roomId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $room || ! $room->is_active || $room->status !== 'available') {
            return false;
        }

        // Check for conflicts via room_id directly on reservation
        $conflictingReservation = Reservation::where('room_id', $roomId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->exists();

        if ($conflictingReservation) {
            return false;
        }

        // Check for conflicts via reservation_rooms
        $conflictingReservationRoom = ReservationRoom::whereHas('reservation', function ($q) {
            $q->whereIn('status', self::BLOCKING_STATUSES);
        })
            ->where('room_id', $roomId)
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->when($excludeReservationId, fn ($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->exists();

        return ! $conflictingReservationRoom;
    }

    /**
     * Get list of available rooms for given dates and optional room type filter.
     * Only returns rooms with status 'available' and is_active=true that have no conflicting reservations.
     */
    public function getAvailableRooms(
        int $tenantId,
        string $checkIn,
        string $checkOut,
        ?int $roomTypeId = null
    ): Collection {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Get all available and active rooms
        $roomsQuery = Room::where('tenant_id', $tenantId)
            ->where('status', 'available')
            ->where('is_active', true)
            ->with('roomType');

        if ($roomTypeId) {
            $roomsQuery->where('room_type_id', $roomTypeId);
        }

        $rooms = $roomsQuery->get();

        // Filter out rooms with conflicts
        return $rooms->filter(function ($room) use ($checkIn, $checkOut) {
            return $this->isRoomAvailable($room->id, $checkIn, $checkOut);
        })->values();
    }

    /**
     * BUG-HOTEL-001 FIX: Get available rooms with pessimistic locking.
     * This prevents race conditions by locking rooms during the check.
     * Must be called inside a DB::transaction.
     */
    public function getAvailableRoomsLocked(
        int $tenantId,
        string $checkIn,
        string $checkOut,
        ?int $roomTypeId = null
    ): Collection {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Get all available and active rooms WITH LOCK
        $roomsQuery = Room::where('tenant_id', $tenantId)
            ->where('status', 'available')
            ->where('is_active', true)
            ->with('roomType');

        if ($roomTypeId) {
            $roomsQuery->where('room_type_id', $roomTypeId);
        }

        // BUG-HOTEL-001 FIX: Lock rooms to prevent concurrent booking
        $rooms = $roomsQuery->lockForUpdate()->get();

        // Filter out rooms with conflicts
        return $rooms->filter(function ($room) use ($checkIn, $checkOut) {
            return $this->isRoomAvailable($room->id, $checkIn, $checkOut);
        })->values();
    }

    /**
     * Build occupancy calendar for a month.
     * Returns array keyed by date, each containing room type stats (total, occupied, available, occupancy_rate).
     *
     * @param  int  $month  1-12
     */
    public function getOccupancyCalendar(int $tenantId, int $month, int $year): array
    {
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $calendar = [];
        $current = $startOfMonth->copy();

        // Get all room types
        $roomTypes = RoomType::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount('rooms')
            ->get()
            ->keyBy('id');

        while ($current->lte($endOfMonth)) {
            $dateStr = $current->toDateString();
            $dayStats = [
                'date' => $dateStr,
                'day_of_week' => $current->dayOfWeek,
                'is_weekend' => $current->isWeekend(),
                'room_types' => [],
            ];

            foreach ($roomTypes as $roomType) {
                $total = $roomType->rooms_count;
                $occupied = $this->getOccupiedRoomsCount($tenantId, $dateStr, $roomType->id);
                $available = max(0, $total - $occupied);

                $dayStats['room_types'][$roomType->id] = [
                    'name' => $roomType->name,
                    'total' => $total,
                    'occupied' => $occupied,
                    'available' => $available,
                    'occupancy_rate' => $total > 0
                        ? round(($occupied / $total) * 100, 1)
                        : 0,
                ];
            }

            // Calculate overall stats for the day
            $totalAll = $roomTypes->sum('rooms_count');
            $occupiedAll = array_sum(array_column($dayStats['room_types'], 'occupied'));
            $dayStats['overall'] = [
                'total' => $totalAll,
                'occupied' => $occupiedAll,
                'available' => max(0, $totalAll - $occupiedAll),
                'occupancy_rate' => $totalAll > 0
                    ? round(($occupiedAll / $totalAll) * 100, 1)
                    : 0,
            ];

            $calendar[$dateStr] = $dayStats;
            $current->addDay();
        }

        return $calendar;
    }

    /**
     * Get count of occupied rooms for a specific date and room type.
     */
    public function getOccupiedRoomsCount(int $tenantId, string $date, ?int $roomTypeId = null): int
    {
        $dateCarbon = Carbon::parse($date);

        // Count reservations with room_id directly assigned
        $directCount = Reservation::where('tenant_id', $tenantId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->when($roomTypeId, fn ($q) => $q->where('room_type_id', $roomTypeId))
            ->where('check_in_date', '<=', $dateCarbon)
            ->where('check_out_date', '>', $dateCarbon)
            ->whereNotNull('room_id')
            ->count();

        // Count reservations via reservation_rooms (for multi-room bookings)
        $reservationRoomsCount = ReservationRoom::whereHas('reservation', function ($q) use ($tenantId, $roomTypeId, $dateCarbon) {
            $q->where('tenant_id', $tenantId)
                ->whereIn('status', self::BLOCKING_STATUSES)
                ->when($roomTypeId, fn ($subQ) => $subQ->where('room_type_id', $roomTypeId))
                ->where('check_in_date', '<=', $dateCarbon)
                ->where('check_out_date', '>', $dateCarbon);
        })->count();

        // Avoid double-counting: if a reservation has both room_id and reservation_rooms,
        // we count only once (prefer reservation_rooms as it's more specific)
        return $reservationRoomsCount > 0 ? $reservationRoomsCount : $directCount;
    }

    /**
     * Get occupancy rate for a specific date.
     */
    public function getOccupancyRate(int $tenantId, string $date, ?int $roomTypeId = null): float
    {
        // Get total rooms
        $totalQuery = Room::where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($roomTypeId) {
            $totalQuery->where('room_type_id', $roomTypeId);
        }

        $totalRooms = $totalQuery->count();

        if ($totalRooms === 0) {
            return 0.0;
        }

        $occupiedRooms = $this->getOccupiedRoomsCount($tenantId, $date, $roomTypeId);

        return round(($occupiedRooms / $totalRooms) * 100, 2);
    }

    /**
     * Get rooms that need checkout today.
     */
    public function getRoomsForCheckoutToday(int $tenantId): Collection
    {
        return Reservation::with(['room', 'guest', 'roomType'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->get();
    }

    /**
     * Get rooms that need check-in today.
     */
    public function getRoomsForCheckInToday(int $tenantId): Collection
    {
        return Reservation::with(['room', 'guest', 'roomType'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', today())
            ->get();
    }

    /**
     * Get upcoming availability for a room type (next N days).
     */
    public function getUpcomingAvailability(int $roomTypeId, int $days = 30): array
    {
        $roomType = RoomType::withCount('rooms')->find($roomTypeId);

        if (! $roomType) {
            return [];
        }

        $availability = [];
        $current = now()->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $dateStr = $current->toDateString();
            $occupied = $this->getOccupiedRoomsCount($roomType->tenant_id, $dateStr, $roomTypeId);
            $total = $roomType->rooms_count;

            $availability[] = [
                'date' => $dateStr,
                'total' => $total,
                'occupied' => $occupied,
                'available' => max(0, $total - $occupied),
                'occupancy_rate' => $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
            ];

            $current->addDay();
        }

        return $availability;
    }

    /**
     * Find the best available room for a room type.
     * Prefers lowest floor number, then lowest room number.
     */
    public function findBestAvailableRoom(int $roomTypeId, string $checkIn, string $checkOut): ?Room
    {
        $availableRooms = $this->getAvailableRooms(
            tenantId: RoomType::find($roomTypeId)?->tenant_id ?? 0,
            checkIn: $checkIn,
            checkOut: $checkOut,
            roomTypeId: $roomTypeId
        );

        // Sort by floor (ascending), then by room number (ascending)
        return $availableRooms->sortBy(function ($room) {
            return [$room->floor ?? 0, (int) $room->number];
        })->first();
    }
}
