<?php

namespace App\Services\Telecom;

use App\Models\TelecomSubscription;
use App\Models\InternetPackage;
use App\Models\UsageTracking;
use App\Models\Customer;
use App\Models\VoucherCode;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelecomReportsService
{
    /**
     * Generate Revenue by Package Report.
     */
    public function revenueByPackage(array $filters = []): array
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();

        $query = TelecomSubscription::where('tenant_id', $tenantId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
            ->selectRaw('
                internet_packages.id as package_id,
                internet_packages.name as package_name,
                internet_packages.download_speed_mbps,
                internet_packages.upload_speed_mbps,
                internet_packages.quota_bytes,
                internet_packages.price as unit_price,
                COUNT(telecom_subscriptions.id) as total_subscriptions,
                SUM(CASE WHEN telecom_subscriptions.status = "active" THEN 1 ELSE 0 END) as active_subscriptions,
                SUM(internet_packages.price) as total_revenue,
                AVG(internet_packages.price) as avg_revenue_per_subscription
            ')
            ->groupBy(
                'internet_packages.id',
                'internet_packages.name',
                'internet_packages.download_speed_mbps',
                'internet_packages.upload_speed_mbps',
                'internet_packages.quota_bytes',
                'internet_packages.price'
            )
            ->orderBy('total_revenue', 'desc');

        $packages = $query->get()->map(function ($package) {
            return [
                'package_id' => $package->package_id,
                'package_name' => $package->package_name,
                'speed' => "{$package->download_speed_mbps}/{$package->upload_speed_mbps} Mbps",
                'quota' => $package->quota_bytes ? round($package->quota_bytes / 1073741824, 2) . ' GB' : 'Unlimited',
                'unit_price' => $package->unit_price,
                'total_subscriptions' => $package->total_subscriptions,
                'active_subscriptions' => $package->active_subscriptions,
                'total_revenue' => $package->total_revenue,
                'avg_revenue' => $package->avg_revenue_per_subscription,
                'revenue_percentage' => 0, // Will calculate after
            ];
        });

        // Calculate revenue percentages
        $totalRevenue = $packages->sum('total_revenue');
        if ($totalRevenue > 0) {
            $packages = $packages->map(function ($package) use ($totalRevenue) {
                $package['revenue_percentage'] = round(($package['total_revenue'] / $totalRevenue) * 100, 2);
                return $package;
            });
        }

        // Summary
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_subscriptions' => $packages->sum('total_subscriptions'),
            'total_active' => $packages->sum('active_subscriptions'),
            'avg_revenue_per_package' => $totalRevenue / max($packages->count(), 1),
            'top_package' => $packages->first()?->package_name ?? '-',
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return [
            'packages' => $packages,
            'summary' => $summary,
        ];
    }

    /**
     * Generate Bandwidth Utilization Report.
     */
    public function bandwidthUtilization(array $filters = []): array
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $groupBy = $filters['group_by'] ?? 'daily'; // daily, weekly, monthly

        // Determine group format
        $format = match ($groupBy) {
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $usage = UsageTracking::where('tenant_id', $tenantId)
            ->whereBetween('period_start', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(period_start, '{$format}') as period,
                SUM(bytes_in) as total_download,
                SUM(bytes_out) as total_upload,
                SUM(bytes_in + bytes_out) as total_usage,
                COUNT(DISTINCT subscription_id) as active_subscriptions,
                AVG(bytes_in + bytes_out) as avg_usage_per_subscription
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($record) {
                return [
                    'period' => $record->period,
                    'total_download_gb' => round($record->total_download / 1073741824, 2),
                    'total_upload_gb' => round($record->total_upload / 1073741824, 2),
                    'total_usage_gb' => round($record->total_usage / 1073741824, 2),
                    'active_subscriptions' => $record->active_subscriptions,
                    'avg_usage_gb' => round($record->avg_usage_per_subscription / 1073741824, 2),
                ];
            });

        // Device-level breakdown
        $deviceUsage = DB::table('usage_tracking')
            ->join('telecom_subscriptions', 'usage_tracking.subscription_id', '=', 'telecom_subscriptions.id')
            ->join('network_devices', 'telecom_subscriptions.device_id', '=', 'network_devices.id')
            ->where('usage_tracking.tenant_id', $tenantId)
            ->whereBetween('usage_tracking.period_start', [$startDate, $endDate])
            ->selectRaw('
                network_devices.id as device_id,
                network_devices.name as device_name,
                network_devices.ip_address,
                SUM(usage_tracking.bytes_in) as total_download,
                SUM(usage_tracking.bytes_out) as total_upload,
                SUM(usage_tracking.bytes_in + usage_tracking.bytes_out) as total_usage,
                COUNT(DISTINCT usage_tracking.subscription_id) as subscription_count
            ')
            ->groupBy(
                'network_devices.id',
                'network_devices.name',
                'network_devices.ip_address'
            )
            ->orderBy('total_usage', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($device) {
                return [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'ip_address' => $device->ip_address,
                    'total_download_gb' => round($device->total_download / 1073741824, 2),
                    'total_upload_gb' => round($device->total_upload / 1073741824, 2),
                    'total_usage_gb' => round($device->total_usage / 1073741824, 2),
                    'subscription_count' => $device->subscription_count,
                ];
            });

        // Summary
        $totalDownload = $usage->sum('total_download_gb');
        $totalUpload = $usage->sum('total_upload_gb');
        $totalUsage = $usage->sum('total_usage_gb');

        $summary = [
            'total_download_gb' => $totalDownload,
            'total_upload_gb' => $totalUpload,
            'total_usage_gb' => $totalUsage,
            'download_upload_ratio' => $totalUpload > 0 ? round($totalDownload / $totalUpload, 2) : 0,
            'avg_daily_usage_gb' => round($totalUsage / max($usage->count(), 1), 2),
            'peak_usage_period' => $usage->sortByDesc('total_usage_gb')->first()?->period ?? '-',
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'group_by' => $groupBy,
            ],
        ];

        return [
            'usage_trend' => $usage,
            'device_breakdown' => $deviceUsage,
            'summary' => $summary,
        ];
    }

    /**
     * Generate Customer Usage Analytics Report.
     */
    public function customerUsageAnalytics(array $filters = []): array
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $sortBy = $filters['sort_by'] ?? 'usage'; // usage, revenue, subscriptions

        // Customer-level analytics
        $customers = Customer::where('tenant_id', $tenantId)
            ->whereHas('telecomSubscriptions', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('started_at', [$startDate, $endDate]);
            })
            ->withCount([
                'telecomSubscriptions as subscription_count' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('started_at', [$startDate, $endDate]);
                }
            ])
            ->withSum([
                'telecomSubscriptions as total_revenue' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('started_at', [$startDate, $endDate])
                        ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
                        ->selectRaw('SUM(internet_packages.price)');
                }
            ], 'total_revenue')
            ->orderBy(match ($sortBy) {
                'revenue' => 'total_revenue',
                'subscriptions' => 'subscription_count',
                default => 'subscription_count',
            }, 'desc')
            ->paginate(50);

        // Usage distribution
        $usageDistribution = UsageTracking::where('tenant_id', $tenantId)
            ->whereBetween('period_start', [$startDate, $endDate])
            ->join('telecom_subscriptions', 'usage_tracking.subscription_id', '=', 'telecom_subscriptions.id')
            ->selectRaw('
                CASE
                    WHEN (bytes_in + bytes_out) < 1073741824 THEN "< 1 GB"
                    WHEN (bytes_in + bytes_out) < 5368709120 THEN "1-5 GB"
                    WHEN (bytes_in + bytes_out) < 10737418240 THEN "5-10 GB"
                    WHEN (bytes_in + bytes_out) < 53687091200 THEN "10-50 GB"
                    ELSE "> 50 GB"
                END as usage_range,
                COUNT(*) as count
            ')
            ->groupBy('usage_range')
            ->orderByRaw('MIN(bytes_in + bytes_out)')
            ->get();

        // Status distribution
        $statusDistribution = TelecomSubscription::where('tenant_id', $tenantId)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Summary
        $summary = [
            'total_customers' => $customers->total(),
            'total_subscriptions' => TelecomSubscription::where('tenant_id', $tenantId)
                ->whereBetween('started_at', [$startDate, $endDate])
                ->count(),
            'total_revenue' => TelecomSubscription::where('tenant_id', $tenantId)
                ->whereBetween('started_at', [$startDate, $endDate])
                ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
                ->sum('internet_packages.price'),
            'avg_subscriptions_per_customer' => round($customers->sum('subscription_count') / max($customers->count(), 1), 2),
            'avg_revenue_per_customer' => round($customers->sum('total_revenue') / max($customers->count(), 1), 2),
            'active_rate' => $statusDistribution->get('active', 0) > 0
                ? round(($statusDistribution->get('active', 0) / $statusDistribution->sum()) * 100, 2)
                : 0,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return [
            'customers' => $customers,
            'usage_distribution' => $usageDistribution,
            'status_distribution' => $statusDistribution,
            'summary' => $summary,
        ];
    }

    /**
     * Generate Top Consumers Report.
     */
    public function topConsumers(array $filters = []): array
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = $filters['start_date'] ?? now()->startOfMonth();
        $endDate = $filters['end_date'] ?? now()->endOfMonth();
        $limit = $filters['limit'] ?? 20;
        $metric = $filters['metric'] ?? 'usage'; // usage, download, upload

        // Top consumers by usage
        $consumers = UsageTracking::where('tenant_id', $tenantId)
            ->whereBetween('period_start', [$startDate, $endDate])
            ->join('telecom_subscriptions', 'usage_tracking.subscription_id', '=', 'telecom_subscriptions.id')
            ->join('customers', 'telecom_subscriptions.customer_id', '=', 'customers.id')
            ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
            ->selectRaw('
                customers.id as customer_id,
                customers.name as customer_name,
                customers.email,
                internet_packages.name as package_name,
                internet_packages.download_speed_mbps,
                internet_packages.upload_speed_mbps,
                SUM(usage_tracking.bytes_in) as total_download,
                SUM(usage_tracking.bytes_out) as total_upload,
                SUM(usage_tracking.bytes_in + usage_tracking.bytes_out) as total_usage,
                COUNT(DISTINCT usage_tracking.id) as tracking_records,
                MAX(usage_tracking.period_end) as last_activity
            ')
            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.email',
                'internet_packages.name',
                'internet_packages.download_speed_mbps',
                'internet_packages.upload_speed_mbps'
            )
            ->orderBy(match ($metric) {
                'download' => 'total_download',
                'upload' => 'total_upload',
                default => 'total_usage',
            }, 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($consumer) {
                return [
                    'customer_id' => $consumer->customer_id,
                    'customer_name' => $consumer->customer_name,
                    'email' => $consumer->email,
                    'package_name' => $consumer->package_name,
                    'speed' => "{$consumer->download_speed_mbps}/{$consumer->upload_speed_mbps} Mbps",
                    'total_download_gb' => round($consumer->total_download / 1073741824, 2),
                    'total_upload_gb' => round($consumer->total_upload / 1073741824, 2),
                    'total_usage_gb' => round($consumer->total_usage / 1073741824, 2),
                    'tracking_records' => $consumer->tracking_records,
                    'last_activity' => $consumer->last_activity,
                ];
            });

        // Voucher usage stats
        $voucherStats = VoucherCode::where('tenant_id', $tenantId)
            ->where('status', 'used')
            ->whereBetween('used_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_vouchers_used,
                SUM(sale_price) as total_voucher_revenue,
                AVG(sale_price) as avg_voucher_price
            ')
            ->first();

        // Summary
        $totalUsage = $consumers->sum('total_usage_gb');
        $summary = [
            'total_top_consumers' => $consumers->count(),
            'total_usage_gb' => $totalUsage,
            'avg_usage_per_consumer_gb' => round($totalUsage / max($consumers->count(), 1), 2),
            'top_consumer' => $consumers->first()?->customer_name ?? '-',
            'highest_usage_gb' => $consumers->first()?->total_usage_gb ?? 0,
            'vouchers_used' => $voucherStats->total_vouchers_used ?? 0,
            'voucher_revenue' => $voucherStats->total_voucher_revenue ?? 0,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'metric' => $metric,
            ],
        ];

        return [
            'consumers' => $consumers,
            'summary' => $summary,
        ];
    }

    /**
     * Export report to Excel.
     */
    public function exportToExcel(string $reportType, array $data, string $filename): \Maatwebsite\Excel\BinaryFileResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', ucfirst(str_replace('_', ' ', $reportType)) . ' Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Add data based on report type
        $row = 3;

        switch ($reportType) {
            case 'revenue_by_package':
                $this->exportRevenueReport($sheet, $data, $row);
                break;
            case 'bandwidth_utilization':
                $this->exportBandwidthReport($sheet, $data, $row);
                break;
            case 'customer_usage_analytics':
                $this->exportCustomerAnalyticsReport($sheet, $data, $row);
                break;
            case 'top_consumers':
                $this->exportTopConsumersReport($sheet, $data, $row);
                break;
        }

        // Auto-size columns
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Export Revenue Report to Excel.
     */
    protected function exportRevenueReport($sheet, $data, &$row)
    {
        // Headers
        $headers = ['Package', 'Speed', 'Quota', 'Price', 'Total Subs', 'Active Subs', 'Revenue', '% of Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        foreach ($data['packages'] as $package) {
            $sheet->fromArray([
                $package['package_name'],
                $package['speed'],
                $package['quota'],
                'Rp ' . number_format($package['unit_price']),
                $package['total_subscriptions'],
                $package['active_subscriptions'],
                'Rp ' . number_format($package['total_revenue']),
                $package['revenue_percentage'] . '%',
            ], null, 'A' . $row);
            $row++;
        }

        // Summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->fromArray([
            'Total Revenue',
            'Rp ' . number_format($data['summary']['total_revenue']),
        ], null, 'A' . $row);
        $row++;
        $sheet->fromArray([
            'Total Subscriptions',
            $data['summary']['total_subscriptions'],
        ], null, 'A' . $row);
    }

    /**
     * Export Bandwidth Report to Excel.
     */
    protected function exportBandwidthReport($sheet, $data, &$row)
    {
        // Trend headers
        $headers = ['Period', 'Download (GB)', 'Upload (GB)', 'Total (GB)', 'Active Subs', 'Avg Usage (GB)'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Trend data
        foreach ($data['usage_trend'] as $trend) {
            $sheet->fromArray([
                $trend['period'],
                $trend['total_download_gb'],
                $trend['total_upload_gb'],
                $trend['total_usage_gb'],
                $trend['active_subscriptions'],
                $trend['avg_usage_gb'],
            ], null, 'A' . $row);
            $row++;
        }

        // Device breakdown
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Device Breakdown (Top 10):');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $deviceHeaders = ['Device', 'IP Address', 'Download (GB)', 'Upload (GB)', 'Total (GB)', 'Subscriptions'];
        $col = 'A';
        foreach ($deviceHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        foreach ($data['device_breakdown'] as $device) {
            $sheet->fromArray([
                $device['device_name'],
                $device['ip_address'],
                $device['total_download_gb'],
                $device['total_upload_gb'],
                $device['total_usage_gb'],
                $device['subscription_count'],
            ], null, 'A' . $row);
            $row++;
        }
    }

    /**
     * Export Customer Analytics Report to Excel.
     */
    protected function exportCustomerAnalyticsReport($sheet, $data, &$row)
    {
        // Summary
        $sheet->setCellValue('A' . $row, 'Summary:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->fromArray([
            'Total Customers',
            $data['summary']['total_customers'],
        ], null, 'A' . $row);
        $row++;
        $sheet->fromArray([
            'Total Subscriptions',
            $data['summary']['total_subscriptions'],
        ], null, 'A' . $row);
        $row++;
        $sheet->fromArray([
            'Total Revenue',
            'Rp ' . number_format($data['summary']['total_revenue']),
        ], null, 'A' . $row);
        $row += 2;

        // Customer list
        $headers = ['Customer', 'Email', 'Subscriptions', 'Revenue'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        foreach ($data['customers'] as $customer) {
            $sheet->fromArray([
                $customer->name,
                $customer->email ?? '-',
                $customer->subscription_count,
                'Rp ' . number_format($customer->total_revenue ?? 0),
            ], null, 'A' . $row);
            $row++;
        }
    }

    /**
     * Export Top Consumers Report to Excel.
     */
    protected function exportTopConsumersReport($sheet, $data, &$row)
    {
        // Headers
        $headers = ['#', 'Customer', 'Email', 'Package', 'Speed', 'Download (GB)', 'Upload (GB)', 'Total (GB)', 'Last Activity'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        $rank = 1;
        foreach ($data['consumers'] as $consumer) {
            $sheet->fromArray([
                $rank++,
                $consumer['customer_name'],
                $consumer['email'] ?? '-',
                $consumer['package_name'],
                $consumer['speed'],
                $consumer['total_download_gb'],
                $consumer['total_upload_gb'],
                $consumer['total_usage_gb'],
                $consumer['last_activity']?->format('d M Y H:i') ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        // Summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Summary:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->fromArray([
            'Total Usage (All Top Consumers)',
            $data['summary']['total_usage_gb'] . ' GB',
        ], null, 'A' . $row);
    }
}
