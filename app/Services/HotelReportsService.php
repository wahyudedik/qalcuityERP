<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\HotelGuest;
use App\Models\FbOrder;
use App\Models\SpaBooking;
use App\Models\HousekeepingTask;
use App\Models\NightAuditBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelReportsService
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Generate Daily Operations Report
     */
    public function generateDailyOperationsReport(Carbon $date): array
    {
        // Room Statistics
        $totalRooms = Room::where('tenant_id', $this->tenantId)->where('is_active', true)->count();
        $occupiedRooms = Room::where('tenant_id', $this->tenantId)->where('status', 'occupied')->count();
        $vacantRooms = Room::where('tenant_id', $this->tenantId)->where('status', 'vacant_clean')->count();
        $outOfOrder = Room::where('tenant_id', $this->tenantId)->where('status', 'out_of_order')->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0;

        // Reservation Activity
        $arrivals = Reservation::where('tenant_id', $this->tenantId)
            ->whereDate('check_in_date', $date)
            ->where('status', 'confirmed')
            ->count();

        $departures = Reservation::where('tenant_id', $this->tenantId)
            ->whereDate('check_out_date', $date)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->count();

        $inHouse = Reservation::where('tenant_id', $this->tenantId)
            ->where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', $date)
            ->whereDate('check_out_date', '>=', $date)
            ->count();

        // Revenue Summary
        $roomRevenue = Reservation::where('tenant_id', $this->tenantId)
            ->whereDate('check_in_date', $date)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $fbRevenue = FbOrder::where('tenant_id', $this->tenantId)
            ->whereDate('order_date', $date)
            ->where('status', 'completed')
            ->sum('total_amount');

        $spaRevenue = SpaBooking::where('tenant_id', $this->tenantId)
            ->whereDate('booking_date', $date)
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalRevenue = $roomRevenue + $fbRevenue + $spaRevenue;

        // Housekeeping Status
        $hkCompleted = HousekeepingTask::where('tenant_id', $this->tenantId)
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->count();

        $hkPending = HousekeepingTask::where('tenant_id', $this->tenantId)
            ->whereDate('created_at', $date)
            ->where('status', 'pending')
            ->count();

        // Guest Statistics
        $newGuests = HotelGuest::where('tenant_id', $this->tenantId)
            ->whereDate('created_at', $date)
            ->count();

        $returningGuests = HotelGuest::where('tenant_id', $this->tenantId)
            ->whereDate('created_at', '<', $date)
            ->whereHas('reservations', function ($q) use ($date) {
                $q->whereDate('check_in_date', $date);
            })
            ->count();

        return [
            'date' => $date->format('Y-m-d'),
            'room_statistics' => [
                'total_rooms' => $totalRooms,
                'occupied' => $occupiedRooms,
                'vacant_clean' => $vacantRooms,
                'out_of_order' => $outOfOrder,
                'occupancy_rate' => $occupancyRate,
            ],
            'reservation_activity' => [
                'arrivals' => $arrivals,
                'departures' => $departures,
                'in_house' => $inHouse,
                'no_shows' => Reservation::where('tenant_id', $this->tenantId)
                    ->whereDate('check_in_date', $date)
                    ->where('status', 'no_show')
                    ->count(),
            ],
            'revenue_summary' => [
                'room_revenue' => round($roomRevenue, 2),
                'fb_revenue' => round($fbRevenue, 2),
                'spa_revenue' => round($spaRevenue, 2),
                'total_revenue' => round($totalRevenue, 2),
            ],
            'housekeeping' => [
                'tasks_completed' => $hkCompleted,
                'tasks_pending' => $hkPending,
                'completion_rate' => ($hkCompleted + $hkPending) > 0
                    ? round(($hkCompleted / ($hkCompleted + $hkPending)) * 100, 2)
                    : 0,
            ],
            'guest_statistics' => [
                'new_guests' => $newGuests,
                'returning_guests' => $returningGuests,
                'total_check_ins' => $arrivals,
            ],
        ];
    }

    /**
     * Generate Revenue Report with multi-dimensional analysis
     */
    public function generateRevenueReport(Carbon $startDate, Carbon $endDate, string $groupBy = 'day'): array
    {
        // Overall Revenue Summary
        $roomRevenue = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('AVG(total_amount) as avg_booking_value')
            )
            ->first();

        $fbRevenue = FbOrder::where('tenant_id', $this->tenantId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as order_count')
            )
            ->first();

        $spaRevenue = SpaBooking::where('tenant_id', $this->tenantId)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as booking_count')
            )
            ->first();

        // Revenue by Source
        $revenueBySource = [
            'rooms' => [
                'amount' => round($roomRevenue->total ?? 0, 2),
                'count' => $roomRevenue->booking_count ?? 0,
                'percentage' => 0,
            ],
            'food_beverage' => [
                'amount' => round($fbRevenue->total ?? 0, 2),
                'count' => $fbRevenue->order_count ?? 0,
                'percentage' => 0,
            ],
            'spa' => [
                'amount' => round($spaRevenue->total ?? 0, 2),
                'count' => $spaRevenue->booking_count ?? 0,
                'percentage' => 0,
            ],
        ];

        $totalRevenue = array_sum(array_column($revenueBySource, 'amount'));

        foreach ($revenueBySource as &$source) {
            $source['percentage'] = $totalRevenue > 0
                ? round(($source['amount'] / $totalRevenue) * 100, 2)
                : 0;
        }

        // Daily Revenue Trend
        $dailyTrend = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(check_in_date) as date'),
                DB::raw('SUM(total_amount) as room_revenue'),
                DB::raw('COUNT(*) as bookings')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $fbRev = FbOrder::where('tenant_id', $this->tenantId)
                    ->whereDate('order_date', $item->date)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $spaRev = SpaBooking::where('tenant_id', $this->tenantId)
                    ->whereDate('booking_date', $item->date)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $item->fb_revenue = round($fbRev, 2);
                $item->spa_revenue = round($spaRev, 2);
                $item->total_revenue = round($item->room_revenue + $fbRev + $spaRev, 2);

                return $item;
            });

        // Revenue by Room Type
        $revenueByRoomType = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->join('room_types', 'reservations.room_type_id', '=', 'room_types.id')
            ->select(
                'room_types.name as room_type',
                DB::raw('SUM(reservations.total_amount) as revenue'),
                DB::raw('COUNT(*) as bookings'),
                DB::raw('AVG(reservations.total_amount) as avg_rate')
            )
            ->groupBy('room_types.id', 'room_types.name')
            ->orderByDesc('revenue')
            ->get();

        // ADR and RevPAR
        $availableRoomNights = Room::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->count() * max(1, $startDate->diffInDays($endDate));

        $soldRoomNights = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->count();

        $adr = $soldRoomNights > 0 ? ($roomRevenue->total / $soldRoomNights) : 0;
        $revpar = $availableRoomNights > 0 ? ($roomRevenue->total / $availableRoomNights) : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => max(1, $startDate->diffInDays($endDate)),
            ],
            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'room_revenue' => round($roomRevenue->total ?? 0, 2),
                'fb_revenue' => round($fbRevenue->total ?? 0, 2),
                'spa_revenue' => round($spaRevenue->total ?? 0, 2),
                'total_bookings' => ($roomRevenue->booking_count ?? 0) + ($spaRevenue->booking_count ?? 0),
                'avg_daily_revenue' => round($totalRevenue / max(1, $startDate->diffInDays($endDate)), 2),
            ],
            'metrics' => [
                'adr' => round($adr, 2),
                'revpar' => round($revpar, 2),
                'occupancy_rate' => $availableRoomNights > 0
                    ? round(($soldRoomNights / $availableRoomNights) * 100, 2)
                    : 0,
            ],
            'revenue_by_source' => $revenueBySource,
            'daily_trend' => $dailyTrend,
            'revenue_by_room_type' => $revenueByRoomType,
        ];
    }

    /**
     * Generate Occupancy Analytics
     */
    public function generateOccupancyAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $totalRooms = Room::where('tenant_id', $this->tenantId)->where('is_active', true)->count();

        // Daily occupancy
        $dailyOccupancy = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $occupied = Room::where('tenant_id', $this->tenantId)
                ->where('status', 'occupied')
                ->whereDate('updated_at', '<=', $currentDate)
                ->count(); // Simplified - in production would check reservations

            $rate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0;

            $dailyOccupancy[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'occupied_rooms' => $occupied,
                'total_rooms' => $totalRooms,
                'occupancy_rate' => $rate,
            ];

            $currentDate->addDay();
        }

        // Occupancy by day of week
        $occupancyByDayOfWeek = collect($dailyOccupancy)
            ->groupBy('day_name')
            ->map(function ($day) {
                return [
                    'average_occupancy' => round($day->avg('occupancy_rate'), 2),
                    'total_days' => $day->count(),
                ];
            });

        // Occupancy by room type
        $occupancyByRoomType = Room::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->select(
                'room_types.name as room_type',
                DB::raw('COUNT(*) as total_rooms'),
                DB::raw('SUM(CASE WHEN rooms.status = "occupied" THEN 1 ELSE 0 END) as occupied_rooms')
            )
            ->groupBy('room_types.id', 'room_types.name')
            ->get()
            ->map(function ($item) {
                $item->occupancy_rate = $item->total_rooms > 0
                    ? round(($item->occupied_rooms / $item->total_rooms) * 100, 2)
                    : 0;
                return $item;
            });

        // Average metrics
        $avgOccupancy = collect($dailyOccupancy)->avg('occupancy_rate') ?? 0;
        $maxOccupancy = collect($dailyOccupancy)->max('occupancy_rate') ?? 0;
        $minOccupancy = collect($dailyOccupancy)->min('occupancy_rate') ?? 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'average_occupancy' => round($avgOccupancy, 2),
                'max_occupancy' => round($maxOccupancy, 2),
                'min_occupancy' => round($minOccupancy, 2),
                'total_rooms' => $totalRooms,
            ],
            'daily_occupancy' => $dailyOccupancy,
            'by_day_of_week' => $occupancyByDayOfWeek,
            'by_room_type' => $occupancyByRoomType,
        ];
    }

    /**
     * Generate Guest Analytics
     */
    public function generateGuestAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        // Guest demographics
        $guestDemographics = HotelGuest::where('tenant_id', $this->tenantId)
            ->whereHas('reservations', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate]);
            })
            ->select(
                DB::raw('nationality'),
                DB::raw('COUNT(*) as guest_count'),
                DB::raw('AVG(reservations.total_amount) as avg_spend')
            )
            ->join('reservations', 'hotel_guests.id', '=', 'reservations.guest_id')
            ->groupBy('nationality')
            ->orderByDesc('guest_count')
            ->limit(10)
            ->get();

        // Repeat guests
        $repeatGuests = HotelGuest::where('tenant_id', $this->tenantId)
            ->whereHas('reservations', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate]);
            })
            ->withCount([
                'reservations as total_stays' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('check_in_date', [$startDate, $endDate]);
                }
            ])
            ->having('total_stays', '>', 1)
            ->get()
            ->count();

        $totalGuests = HotelGuest::where('tenant_id', $this->tenantId)
            ->whereHas('reservations', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate]);
            })
            ->count();

        $repeatRate = $totalGuests > 0 ? round(($repeatGuests / $totalGuests) * 100, 2) : 0;

        // Average length of stay
        $avgStayDuration = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('AVG(DATEDIFF(check_out_date, check_in_date)) as avg_nights')
            ->value('avg_nights') ?? 0;

        // Booking lead time
        $avgLeadTime = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('AVG(DATEDIFF(check_in_date, created_at)) as avg_lead_days')
            ->value('avg_lead_days') ?? 0;

        // Guest satisfaction (from reviews if available)
        $avgRating = 0; // Would integrate with review system

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_guests' => $totalGuests,
                'repeat_guests' => $repeatGuests,
                'repeat_rate' => $repeatRate,
                'new_guests' => $totalGuests - $repeatGuests,
                'avg_stay_duration' => round($avgStayDuration, 2),
                'avg_booking_lead_time' => round($avgLeadTime, 2),
            ],
            'demographics' => [
                'by_nationality' => $guestDemographics,
            ],
            'behavior' => [
                'avg_spend_per_guest' => 0, // Calculate from reservations
                'preferred_room_types' => [], // Would analyze reservation data
                'peak_booking_days' => [], // Would analyze booking patterns
            ],
        ];
    }

    /**
     * Generate Staff Performance Report
     */
    public function generateStaffPerformanceReport(Carbon $startDate, Carbon $endDate): array
    {
        // Housekeeping performance
        $hkPerformance = HousekeepingTask::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('users', 'housekeeping_tasks.assigned_to', '=', 'users.id')
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(*) as total_tasks'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks'),
                DB::raw('AVG(CASE WHEN completed_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, completed_at) END) as avg_completion_hours')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_tasks')
            ->get()
            ->map(function ($item) {
                $item->completion_rate = $item->total_tasks > 0
                    ? round(($item->completed_tasks / $item->total_tasks) * 100, 2)
                    : 0;
                return $item;
            });

        // Therapist performance (Spa)
        $therapistPerformance = \App\Models\SpaTherapist::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->withCount([
                'bookings as total_treatments' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate])
                        ->where('status', 'completed');
                }
            ])
            ->withSum([
                'bookings as total_revenue' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate])
                        ->where('status', 'completed');
                }
            ], 'total_amount')
            ->orderByDesc('total_revenue')
            ->get();

        // Front desk performance (reservations handled)
        $frontDeskPerformance = Reservation::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('users', 'reservations.created_by', '=', 'users.id')
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(*) as reservations_created'),
                DB::raw('SUM(total_amount) as total_revenue_generated')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('reservations_created')
            ->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'housekeeping' => [
                'staff_performance' => $hkPerformance,
                'total_tasks' => $hkPerformance->sum('total_tasks'),
                'avg_completion_rate' => $hkPerformance->avg('completion_rate') ?? 0,
            ],
            'spa_therapists' => [
                'performance' => $therapistPerformance,
                'total_treatments' => $therapistPerformance->sum('total_treatments'),
                'total_revenue' => $therapistPerformance->sum('total_revenue'),
            ],
            'front_desk' => [
                'performance' => $frontDeskPerformance,
                'total_reservations' => $frontDeskPerformance->sum('reservations_created'),
                'total_revenue' => $frontDeskPerformance->sum('total_revenue_generated'),
            ],
        ];
    }
}
