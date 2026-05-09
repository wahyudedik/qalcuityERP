<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Services\Telecom\TelecomReportsService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    protected TelecomReportsService $reportsService;

    public function __construct()
    {
        $this->reportsService = new TelecomReportsService;
    }

    /**
     * Display reports index page.
     */
    public function index()
    {
        return view('telecom.reports.index');
    }

    /**
     * Show Revenue by Package Report.
     */
    public function revenueByPackage(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date', now()->startOfMonth()),
            'end_date' => $request->get('end_date', now()->endOfMonth()),
        ];

        $report = $this->reportsService->revenueByPackage($filters);

        if ($request->get('export') === 'excel') {
            return $this->reportsService->exportToExcel(
                'revenue_by_package',
                $report,
                'Revenue_by_Package_'.now()->format('Ymd')
            );
        }

        return view('telecom.reports.revenue-by-package', compact('report', 'filters'));
    }

    /**
     * Show Bandwidth Utilization Report.
     */
    public function bandwidthUtilization(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date', now()->startOfMonth()),
            'end_date' => $request->get('end_date', now()->endOfMonth()),
            'group_by' => $request->get('group_by', 'daily'),
        ];

        $report = $this->reportsService->bandwidthUtilization($filters);

        if ($request->get('export') === 'excel') {
            return $this->reportsService->exportToExcel(
                'bandwidth_utilization',
                $report,
                'Bandwidth_Utilization_'.now()->format('Ymd')
            );
        }

        return view('telecom.reports.bandwidth-utilization', compact('report', 'filters'));
    }

    /**
     * Show Customer Usage Analytics Report.
     */
    public function customerUsageAnalytics(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date', now()->startOfMonth()),
            'end_date' => $request->get('end_date', now()->endOfMonth()),
            'sort_by' => $request->get('sort_by', 'usage'),
        ];

        $report = $this->reportsService->customerUsageAnalytics($filters);

        if ($request->get('export') === 'excel') {
            return $this->reportsService->exportToExcel(
                'customer_usage_analytics',
                $report,
                'Customer_Usage_Analytics_'.now()->format('Ymd')
            );
        }

        return view('telecom.reports.customer-usage-analytics', compact('report', 'filters'));
    }

    /**
     * Show Top Consumers Report.
     */
    public function topConsumers(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date', now()->startOfMonth()),
            'end_date' => $request->get('end_date', now()->endOfMonth()),
            'limit' => $request->get('limit', 20),
            'metric' => $request->get('metric', 'usage'),
        ];

        $report = $this->reportsService->topConsumers($filters);

        if ($request->get('export') === 'excel') {
            return $this->reportsService->exportToExcel(
                'top_consumers',
                $report,
                'Top_Consumers_'.now()->format('Ymd')
            );
        }

        return view('telecom.reports.top-consumers', compact('report', 'filters'));
    }
}
