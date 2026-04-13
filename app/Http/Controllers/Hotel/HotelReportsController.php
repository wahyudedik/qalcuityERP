<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Services\HotelReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class HotelReportsController extends Controller
{
    // tenantId() inherited from parent Controller

    /**
     * Reports Dashboard
     */
    public function dashboard()
    {
        return view('hotel.reports.dashboard');
    }

    /**
     * Daily Operations Report
     */
    public function dailyOperations(Request $request)
    {
        $date = Carbon::parse($request->input('date', today()));

        $service = new HotelReportsService($this->tenantId());
        $report = $service->generateDailyOperationsReport($date);

        if ($request->input('export') === 'pdf') {
            return $this->exportDailyOperationsPDF($report, $date);
        }

        return view('hotel.reports.daily-operations', compact('report', 'date'));
    }

    /**
     * Revenue Report
     */
    public function revenue(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()));
        $groupBy = $request->input('group_by', 'day');

        $service = new HotelReportsService($this->tenantId());
        $report = $service->generateRevenueReport($startDate, $endDate, $groupBy);

        if ($request->input('export') === 'pdf') {
            return $this->exportRevenuePDF($report, $startDate, $endDate);
        } elseif ($request->input('export') === 'excel') {
            return $this->exportRevenueExcel($report, $startDate, $endDate);
        }

        return view('hotel.reports.revenue', compact('report', 'startDate', 'endDate'));
    }

    /**
     * Occupancy Analytics
     */
    public function occupancy(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()));

        $service = new HotelReportsService($this->tenantId());
        $analytics = $service->generateOccupancyAnalytics($startDate, $endDate);

        if ($request->input('export') === 'pdf') {
            return $this->exportOccupancyPDF($analytics, $startDate, $endDate);
        }

        return view('hotel.reports.occupancy', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Guest Analytics
     */
    public function guestAnalytics(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()));

        $service = new HotelReportsService($this->tenantId());
        $analytics = $service->generateGuestAnalytics($startDate, $endDate);

        if ($request->input('export') === 'pdf') {
            return $this->exportGuestAnalyticsPDF($analytics, $startDate, $endDate);
        }

        return view('hotel.reports.guest-analytics', compact('analytics', 'startDate', 'endDate'));
    }

    /**
     * Staff Performance Report
     */
    public function staffPerformance(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()));

        $service = new HotelReportsService($this->tenantId());
        $report = $service->generateStaffPerformanceReport($startDate, $endDate);

        if ($request->input('export') === 'pdf') {
            return $this->exportStaffPerformancePDF($report, $startDate, $endDate);
        }

        return view('hotel.reports.staff-performance', compact('report', 'startDate', 'endDate'));
    }

    /**
     * Export Daily Operations Report to PDF
     */
    private function exportDailyOperationsPDF(array $report, Carbon $date)
    {
        $pdf = Pdf::loadView('hotel.reports.exports.daily-operations-pdf', compact('report', 'date'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('daily-operations-' . $date->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Revenue Report to PDF
     */
    private function exportRevenuePDF(array $report, Carbon $startDate, Carbon $endDate)
    {
        $pdf = Pdf::loadView('hotel.reports.exports.revenue-pdf', compact('report', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('revenue-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Revenue Report to Excel
     */
    private function exportRevenueExcel(array $report, Carbon $startDate, Carbon $endDate)
    {
        // Implementation would use Laravel Excel package
        return response()->json(['message' => 'Excel export coming soon']);
    }

    /**
     * Export Occupancy Analytics to PDF
     */
    private function exportOccupancyPDF(array $analytics, Carbon $startDate, Carbon $endDate)
    {
        $pdf = Pdf::loadView('hotel.reports.exports.occupancy-pdf', compact('analytics', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('occupancy-analytics-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Guest Analytics to PDF
     */
    private function exportGuestAnalyticsPDF(array $analytics, Carbon $startDate, Carbon $endDate)
    {
        $pdf = Pdf::loadView('hotel.reports.exports.guest-analytics-pdf', compact('analytics', 'startDate', 'endDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('guest-analytics-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Staff Performance to PDF
     */
    private function exportStaffPerformancePDF(array $report, Carbon $startDate, Carbon $endDate)
    {
        $pdf = Pdf::loadView('hotel.reports.exports.staff-performance-pdf', compact('report', 'startDate', 'endDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('staff-performance-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
    }
}
