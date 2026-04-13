<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\ReservationRoomChange;
use App\Services\CheckInOutService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomChangeController extends Controller
{
    private CheckInOutService $checkInOutService;
    private RoomAvailabilityService $availabilityService;

    public function __construct(
        CheckInOutService $checkInOutService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->checkInOutService = $checkInOutService;
        $this->availabilityService = $availabilityService;
    }

    // tenantId() inherited from parent Controller

    private function getUserId(): int
    {
        return Auth::id() ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Show room change form
     */
    public function showChangeForm(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        if (!$reservation->isCheckedIn()) {
            return back()->withErrors(['error' => 'Can only change room for checked-in reservations.']);
        }

        // Get available rooms
        $availableRooms = Room::where('tenant_id', $reservation->tenant_id)
            ->where('id', '!=', $reservation->room_id)
            ->where('status', 'available')
            ->with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('number')
            ->get();

        // Group by room type
        $groupedRooms = $availableRooms->groupBy('roomType.name');

        return view('hotel.room-changes.change-form', compact('reservation', 'availableRooms', 'groupedRooms'));
    }

    /**
     * Process room change
     */
    public function processRoomChange(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'new_room_id' => 'required|exists:rooms,id',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->checkInOutService->changeRoom(
                $reservation->id,
                $data['new_room_id'],
                $data['reason']
            );

            ActivityLog::record(
                'room_changed',
                "Room changed: {$reservation->reservation_number} - {$result['change_type']}",
                $reservation
            );

            $message = "Room changed successfully. " .
                ucfirst($result['change_type']) . " - " .
                "Rate difference: Rp " . number_format($result['rate_difference'], 0, ',', '.') . "/night";

            return redirect()->route('hotel.reservations.show', $reservation)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Get available rooms for date range (AJAX)
     */
    public function getAvailableRooms(Request $request)
    {
        $data = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);

        $tenantId = $this->tenantId();

        $availableRooms = Room::where('tenant_id', $tenantId)
            ->where('status', 'available')
            ->when($data['room_type_id'], function ($query) use ($data) {
                $query->where('room_type_id', $data['room_type_id']);
            })
            ->with('roomType')
            ->get();

        return response()->json([
            'rooms' => $availableRooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'number' => $room->number,
                    'floor' => $room->floor,
                    'room_type' => $room->roomType->name,
                    'base_rate' => $room->roomType->base_rate,
                    'amenities' => $room->roomType->amenities,
                ];
            }),
        ]);
    }

    /**
     * Show visual room status map
     */
    public function roomMap(Request $request)
    {
        $tenantId = $this->tenantId();
        $floor = $request->query('floor');
        $status = $request->query('status');

        $rooms = Room::where('tenant_id', $tenantId)
            ->with(['roomType', 'currentReservation.guest'])
            ->when($floor, function ($query) use ($floor) {
                $query->where('floor', $floor);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        // Get floors
        $floors = Room::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('floor')
            ->sort()
            ->values();

        // Room status counts
        $statusCounts = [
            'available' => Room::where('tenant_id', $tenantId)->where('status', 'available')->count(),
            'occupied' => Room::where('tenant_id', $tenantId)->where('status', 'occupied')->count(),
            'dirty' => Room::where('tenant_id', $tenantId)->where('status', 'dirty')->count(),
            'cleaning' => Room::where('tenant_id', $tenantId)->where('status', 'cleaning')->count(),
            'clean' => Room::where('tenant_id', $tenantId)->where('status', 'clean')->count(),
            'out_of_order' => Room::where('tenant_id', $tenantId)->where('status', 'out_of_order')->count(),
        ];

        return view('hotel.room-changes.room-map', compact('rooms', 'floors', 'statusCounts'));
    }

    /**
     * Get room change history
     */
    public function history(Reservation $reservation)
    {
        abort_unless($reservation->tenant_id === $this->tenantId(), 403);

        $changes = ReservationRoomChange::where('reservation_id', $reservation->id)
            ->with(['fromRoom', 'toRoom', 'processor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['changes' => $changes]);
    }
}
