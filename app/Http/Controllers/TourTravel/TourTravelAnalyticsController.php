<?php

namespace App\Http\Controllers\TourTravel;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourTravelAnalyticsController extends Controller
{
    /**
     * Display tour & travel analytics dashboard
     */
    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        // Booking Analytics
        $bookingStats = [
            'total_bookings' => TourBooking::where('tenant_id', $tenantId)->count(),
            'confirmed_bookings' => TourBooking::where('tenant_id', $tenantId)->where('status', 'confirmed')->count(),
            'completed_bookings' => TourBooking::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            'cancelled_bookings' => TourBooking::where('tenant_id', $tenantId)->where('status', 'cancelled')->count(),
            'total_revenue' => TourBooking::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'pending_revenue' => TourBooking::where('tenant_id', $tenantId)
                ->whereIn('status', ['confirmed', 'paid'])
                ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as pending')
                ->value('pending') ?? 0,
        ];

        // Package Performance
        $topPackages = TourPackage::where('tenant_id', $tenantId)
            ->withCount([
                'bookings' => function ($q) {
                    $q->whereIn('status', ['confirmed', 'paid', 'completed']);
                },
            ])
            ->withSum([
                'bookings' => function ($q) {
                    $q->where('status', 'completed');
                },
            ], 'total_amount')
            ->orderByDesc('bookings_count')
            ->limit(10)
            ->get();

        // Monthly Bookings Trend
        $monthlyBookings = TourBooking::where('tenant_id', $tenantId)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Booking Status Distribution
        $statusDistribution = TourBooking::where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Popular Destinations — join through tour_packages to get destination
        $popularDestinations = TourBooking::withoutGlobalScope('tenant')
            ->where('tour_bookings.tenant_id', $tenantId)
            ->where('tour_bookings.status', 'completed')
            ->join('tour_packages', 'tour_bookings.tour_package_id', '=', 'tour_packages.id')
            ->select(
                'tour_packages.destination',
                DB::raw('COUNT(tour_bookings.id) as bookings'),
                DB::raw('SUM(tour_bookings.total_amount) as revenue')
            )
            ->groupBy('tour_packages.destination')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get();

        return view('tour-travel.analytics.index', compact(
            'bookingStats',
            'topPackages',
            'monthlyBookings',
            'statusDistribution',
            'popularDestinations'
        ));
    }
}
