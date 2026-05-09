<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class HotelDashboardController extends Controller
{
    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        // Total rooms
        $totalRooms = Room::where('tenant_id', $tid)->where('is_active', true)->count();

        // Occupied rooms (rooms with status 'occupied')
        $occupiedRooms = Room::where('tenant_id', $tid)
            ->where('is_active', true)
            ->where('status', 'occupied')
            ->count();

        // Occupancy rate
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Today's expected arrivals (reservations with check_in_date = today, status confirmed)
        $expectedArrivals = Reservation::with(['guest', 'roomType'])
            ->where('tenant_id', $tid)
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', today())
            ->get();

        // Today's expected departures (reservations with check_out_date = today, status checked_in)
        $expectedDepartures = Reservation::with(['guest', 'room', 'roomType'])
            ->where('tenant_id', $tid)
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->get();

        // Pending housekeeping tasks count
        $pendingHousekeeping = HousekeepingTask::where('tenant_id', $tid)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // This month's revenue (sum of grand_total for checked_out reservations)
        $monthlyRevenue = Reservation::where('tenant_id', $tid)
            ->where('status', 'checked_out')
            ->whereYear('check_out_date', now()->year)
            ->whereMonth('check_out_date', now()->month)
            ->sum('grand_total');

        // Recent reservations (last 10)
        $recentReservations = Reservation::with(['guest', 'roomType', 'room'])
            ->where('tenant_id', $tid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Room status summary (count by status)
        $roomStatusSummary = Room::where('tenant_id', $tid)
            ->where('is_active', true)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Room types with room counts
        $roomTypes = RoomType::withCount('rooms')
            ->where('tenant_id', $tid)
            ->where('is_active', true)
            ->get();

        return view('hotel.dashboard', compact(
            'totalRooms',
            'occupiedRooms',
            'occupancyRate',
            'expectedArrivals',
            'expectedDepartures',
            'pendingHousekeeping',
            'monthlyRevenue',
            'recentReservations',
            'roomStatusSummary',
            'roomTypes'
        ));
    }
}
