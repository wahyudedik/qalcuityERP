<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $query = Room::with('roomType')
            ->where('tenant_id', $tid);

        // Filters
        if ($request->type) {
            $query->where('room_type_id', $request->type);
        }

        if ($request->floor) {
            $query->where('floor', $request->floor);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('building', 'like', "%$s%");
            });
        }

        $rooms = $query->orderBy('number')->paginate(20)->withQueryString();

        // Filter options
        $roomTypes = RoomType::where('tenant_id', $tid)->where('is_active', true)->get();
        $floors = Room::where('tenant_id', $tid)->whereNotNull('floor')->distinct()->pluck('floor');
        $statuses = ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'];

        return view('hotel.rooms.index', compact('rooms', 'roomTypes', 'floors', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:20',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:10',
            'building' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order',
            'description' => 'nullable|string',
        ]);

        $tid = $this->tenantId();

        // Check for unique number per tenant
        if (Room::where('tenant_id', $tid)->where('number', $data['number'])->exists()) {
            return back()->withErrors(['number' => 'Room number already exists.'])->withInput();
        }

        // Verify room type belongs to tenant
        $roomType = RoomType::where('id', $data['room_type_id'])->where('tenant_id', $tid)->first();
        if (!$roomType) {
            return back()->withErrors(['room_type_id' => 'Invalid room type.'])->withInput();
        }

        $room = Room::create([
            'tenant_id' => $tid,
            'room_type_id' => $data['room_type_id'],
            'number' => $data['number'],
            'floor' => $data['floor'] ?? null,
            'building' => $data['building'] ?? null,
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('room_created', "Room created: {$room->number}", $room, [], $room->toArray());

        return back()->with('success', "Room {$room->number} created successfully.");
    }

    public function update(Request $request, Room $room)
    {
        abort_unless($room->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'number' => 'required|string|max:20',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:10',
            'building' => 'nullable|string|max:50',
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check for unique number per tenant (excluding current)
        if (Room::where('tenant_id', $this->tenantId())->where('number', $data['number'])->where('id', '!=', $room->id)->exists()) {
            return back()->withErrors(['number' => 'Room number already exists.'])->withInput();
        }

        // Verify room type belongs to tenant
        $roomType = RoomType::where('id', $data['room_type_id'])->where('tenant_id', $this->tenantId())->first();
        if (!$roomType) {
            return back()->withErrors(['room_type_id' => 'Invalid room type.'])->withInput();
        }

        $old = $room->getOriginal();
        $room->update($data);

        ActivityLog::record('room_updated', "Room updated: {$room->number}", $room, $old, $room->fresh()->toArray());

        return back()->with('success', "Room {$room->number} updated successfully.");
    }

    public function destroy(Room $room)
    {
        abort_unless($room->tenant_id === $this->tenantId(), 403);

        // Check for active reservations
        $activeReservations = Reservation::where('room_id', $room->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->exists();

        if ($activeReservations) {
            return back()->with('error', 'Cannot delete room with active reservations.');
        }

        ActivityLog::record('room_deleted', "Room deleted: {$room->number}", $room, $room->toArray());
        $room->delete();

        return back()->with('success', 'Room deleted successfully.');
    }

    public function availability(Request $request, RoomAvailabilityService $availabilityService)
    {
        $tid = $this->tenantId();

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $calendar = $availabilityService->getOccupancyCalendar($tid, (int) $month, (int) $year);

        $roomTypes = RoomType::withCount('rooms')
            ->where('tenant_id', $tid)
            ->where('is_active', true)
            ->get();

        return view('hotel.rooms.availability', compact('calendar', 'roomTypes', 'month', 'year'));
    }

    public function updateStatus(Request $request, Room $room)
    {
        abort_unless($room->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order',
        ]);

        $old = $room->getOriginal();
        $room->update(['status' => $data['status']]);

        ActivityLog::record('room_status_changed', "Room {$room->number} status changed: {$old['status']} → {$room->status}", $room, $old, $room->fresh()->toArray());

        return back()->with('success', "Room {$room->number} status updated to {$data['status']}.");
    }
}
