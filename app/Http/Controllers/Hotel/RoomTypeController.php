<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        $roomTypes = RoomType::withCount('rooms')
            ->where('tenant_id', $tid)
            ->orderBy('name')
            ->get();

        return view('hotel.room-types.index', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'base_occupancy' => 'nullable|integer|min:1',
            'max_occupancy' => 'nullable|integer|min:1',
            'base_rate' => 'required|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
        ]);

        $tid = $this->tenantId();

        // Check for unique code per tenant
        if (RoomType::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'Room type code already exists.'])->withInput();
        }

        $roomType = RoomType::create([
            'tenant_id' => $tid,
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'base_occupancy' => $data['base_occupancy'] ?? 1,
            'max_occupancy' => $data['max_occupancy'] ?? 2,
            'base_rate' => $data['base_rate'],
            'amenities' => $data['amenities'] ?? [],
            'is_active' => true,
        ]);

        ActivityLog::record('room_type_created', "Room type created: {$roomType->name}", $roomType, [], $roomType->toArray());

        return back()->with('success', "Room type {$roomType->name} created successfully.");
    }

    public function update(Request $request, RoomType $roomType)
    {
        abort_unless($roomType->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'base_occupancy' => 'nullable|integer|min:1',
            'max_occupancy' => 'nullable|integer|min:1',
            'base_rate' => 'required|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Check for unique code per tenant (excluding current)
        if (RoomType::where('tenant_id', $this->tenantId())->where('code', $data['code'])->where('id', '!=', $roomType->id)->exists()) {
            return back()->withErrors(['code' => 'Room type code already exists.'])->withInput();
        }

        $old = $roomType->getOriginal();
        $roomType->update($data);

        ActivityLog::record('room_type_updated', "Room type updated: {$roomType->name}", $roomType, $old, $roomType->fresh()->toArray());

        return back()->with('success', "Room type {$roomType->name} updated successfully.");
    }

    public function destroy(RoomType $roomType)
    {
        abort_unless($roomType->tenant_id === $this->tenantId(), 403);

        // Check for active rooms
        $activeRooms = Room::where('room_type_id', $roomType->id)->where('is_active', true)->count();

        if ($activeRooms > 0) {
            return back()->with('error', "Cannot delete room type with {$activeRooms} active rooms.");
        }

        ActivityLog::record('room_type_deleted', "Room type deleted: {$roomType->name} ({$roomType->code})", $roomType, $roomType->toArray());
        $roomType->delete();

        return back()->with('success', 'Room type deleted successfully.');
    }
}
