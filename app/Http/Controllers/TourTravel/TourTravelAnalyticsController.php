<?php

namespace App\Http\Controllers\TourTravel;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TourTravelAnalyticsController extends Controller
{
    /**
     * Get authenticated user's tenant ID
     */
    // tenantId() inherited from parent Controller
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
                ->sum('remaining_balance'),
        ];

        // Package Performance
        $topPackages = TourPackage::where('tenant_id', $tenantId)
            ->withCount([
                'bookings' => function ($q) {
                    $q->whereIn('status', ['confirmed', 'paid', 'completed']);
                }
            ])
            ->withSum([
                'bookings' => function ($q) {
                    $q->where('status', 'completed');
                }
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

        // Popular Destinations (if available in bookings)
        $popularDestinations = TourBooking::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->select('destination', DB::raw('COUNT(*) as bookings'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('destination')
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
