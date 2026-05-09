<?php

namespace App\Http\Controllers\Api;

use App\Models\Guest;
use App\Models\HotelBilling;
use App\Models\HousekeepingStatus;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class HotelApiController extends ApiBaseController
{
    /**
     * Get all rooms
     */
    public function rooms(Request $request)
    {
        $query = Room::where('tenant_id', $this->getTenantId())
            ->with(['roomType', 'currentReservation']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $rooms = $query->paginate($request->get('per_page', 20));

        return $this->success($rooms);
    }

    /**
     * Get room detail
     */
    public function room($id)
    {
        $room = Room::where('tenant_id', $this->getTenantId())
            ->with(['roomType', 'reservations', 'housekeeping'])
            ->findOrFail($id);

        return $this->success($room);
    }

    /**
     * Create room
     */
    public function createRoom(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|integer',
            'status' => 'nullable|in:available,occupied,maintenance,cleaned',
            'price_override' => 'nullable|numeric|min:0',
        ]);

        $room = Room::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'available',
        ]));

        return $this->success($room, 'Room created successfully', 201);
    }

    /**
     * Update room
     */
    public function updateRoom(Request $request, $id)
    {
        $room = Room::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'room_number' => 'sometimes|string|unique:rooms,room_number,'.$id,
            'room_type_id' => 'sometimes|exists:room_types,id',
            'floor' => 'nullable|integer',
            'status' => 'sometimes|in:available,occupied,maintenance,cleaned',
            'price_override' => 'nullable|numeric|min:0',
        ]);

        $room->update($validated);

        return $this->success($room, 'Room updated successfully');
    }

    /**
     * Get reservations
     */
    public function reservations(Request $request)
    {
        $query = Reservation::where('tenant_id', $this->getTenantId())
            ->with(['guest', 'room', 'roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('check_in_date')) {
            $query->whereDate('check_in_date', $request->check_in_date);
        }

        if ($request->filled('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }

        $reservations = $query->latest('check_in_date')->paginate($request->get('per_page', 20));

        return $this->success($reservations);
    }

    /**
     * Get reservation detail
     */
    public function reservation($id)
    {
        $reservation = Reservation::where('tenant_id', $this->getTenantId())
            ->with(['guest', 'room', 'roomType', 'billings'])
            ->findOrFail($id);

        return $this->success($reservation);
    }

    /**
     * Create reservation
     */
    public function createReservation(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'special_requests' => 'nullable|string',
        ]);

        $reservation = Reservation::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'reservation_number' => 'RES-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'confirmed',
        ]));

        return $this->success($reservation, 'Reservation created successfully', 201);
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus(Request $request, $id)
    {
        $reservation = Reservation::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show',
        ]);

        $reservation->update($validated);

        return $this->success($reservation, 'Reservation status updated successfully');
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation($id)
    {
        $reservation = Reservation::where('tenant_id', $this->getTenantId())->findOrFail($id);

        if ($reservation->status === 'checked_in') {
            return $this->error('Cannot cancel checked-in reservation');
        }

        $reservation->update(['status' => 'cancelled']);

        return $this->success($reservation, 'Reservation cancelled successfully');
    }

    /**
     * Get room types
     */
    public function roomTypes(Request $request)
    {
        $query = RoomType::where('tenant_id', $this->getTenantId());

        $roomTypes = $query->paginate($request->get('per_page', 20));

        return $this->success($roomTypes);
    }

    /**
     * Create room type
     */
    public function createRoomType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
        ]);

        $roomType = RoomType::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($roomType, 'Room type created successfully', 201);
    }

    /**
     * Get guests
     */
    public function guests(Request $request)
    {
        $query = Guest::where('tenant_id', $this->getTenantId());

        if ($request->filled('search')) {
            $query->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%");
        }

        $guests = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($guests);
    }

    /**
     * Get guest detail
     */
    public function guest($id)
    {
        $guest = Guest::where('tenant_id', $this->getTenantId())
            ->with(['reservations'])
            ->findOrFail($id);

        return $this->success($guest);
    }

    /**
     * Get billing for reservation
     */
    public function getBilling($reservationId)
    {
        $billing = HotelBilling::where('tenant_id', $this->getTenantId())
            ->where('reservation_id', $reservationId)
            ->with(['reservation', 'charges'])
            ->firstOrFail();

        return $this->success($billing);
    }

    /**
     * Add charge to billing
     */
    public function addCharge(Request $request, $reservationId)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:room_service,food_beverage,laundry,telephone,minibar,other',
        ]);

        $billing = HotelBilling::where('tenant_id', $this->getTenantId())
            ->where('reservation_id', $reservationId)
            ->firstOrFail();

        $charge = $billing->charges()->create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
        ]));

        return $this->success($charge, 'Charge added successfully', 201);
    }

    /**
     * Get housekeeping status
     */
    public function housekeepingStatus(Request $request)
    {
        $query = HousekeepingStatus::where('tenant_id', $this->getTenantId())
            ->with(['room', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $statuses = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($statuses);
    }

    /**
     * Update housekeeping status
     */
    public function updateHousekeepingStatus(Request $request, $roomId)
    {
        $validated = $request->validate([
            'status' => 'required|in:dirty,cleaning,cleaned,inspected,maintenance',
            'notes' => 'nullable|string',
        ]);

        $status = HousekeepingStatus::updateOrCreate(
            ['tenant_id' => $this->getTenantId(), 'room_id' => $roomId],
            array_merge($validated, ['updated_at' => now()])
        );

        // Update room status
        $room = Room::where('tenant_id', $this->getTenantId())->findOrFail($roomId);
        if ($validated['status'] === 'cleaned' || $validated['status'] === 'inspected') {
            $room->update(['status' => 'available']);
        }

        return $this->success($status, 'Housekeeping status updated successfully');
    }
}
