<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\NetworkDevice;
use App\Models\TelecomSubscription;
use App\Models\HotspotUser;
use App\Models\UsageTracking;
use App\Models\NetworkAlert;
use App\Models\InternetPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the telecom monitoring dashboard.
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Overall Stats — grouped queries to reduce round-trips
        $deviceCounts = NetworkDevice::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $subscriptionCounts = TelecomSubscription::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total_devices' => $deviceCounts->sum(),
            'online_devices' => $deviceCounts->get('online', 0),
            'offline_devices' => $deviceCounts->get('offline', 0),
            'maintenance_devices' => $deviceCounts->get('maintenance', 0),

            'total_subscriptions' => $subscriptionCounts->sum(),
            'active_subscriptions' => $subscriptionCounts->get('active', 0),
            'suspended_subscriptions' => $subscriptionCounts->get('suspended', 0),

            'total_hotspot_users' => HotspotUser::where('tenant_id', $tenantId)->count(),
            'online_hotspot_users' => HotspotUser::where('tenant_id', $tenantId)->where('is_online', true)->count(),

            'total_packages' => InternetPackage::where('tenant_id', $tenantId)->count(),
            'active_packages' => InternetPackage::where('tenant_id', $tenantId)->where('is_active', true)->count(),

            'total_alerts' => NetworkAlert::where('tenant_id', $tenantId)->where('status', 'new')->count(),
            'critical_alerts' => NetworkAlert::where('tenant_id', $tenantId)->where('severity', 'critical')->where('status', 'new')->count(),
        ];

        // Bandwidth Usage Chart Data (last 24 hours)
        $bandwidthData = $this->getBandwidthChartData($tenantId);

        // Device Status Distribution — reuse $deviceCounts already fetched above
        $deviceStatusData = [
            'labels' => ['Online', 'Offline', 'Maintenance', 'Pending'],
            'data' => [
                $deviceCounts->get('online', 0),
                $deviceCounts->get('offline', 0),
                $deviceCounts->get('maintenance', 0),
                $deviceCounts->get('pending', 0),
            ]
        ];

        // Subscription Status Distribution — reuse $subscriptionCounts already fetched above
        $subscriptionStatusData = [
            'labels' => ['Active', 'Suspended', 'Cancelled', 'Expired'],
            'data' => [
                $subscriptionCounts->get('active', 0),
                $subscriptionCounts->get('suspended', 0),
                $subscriptionCounts->get('cancelled', 0),
                $subscriptionCounts->get('expired', 0),
            ]
        ];

        // Top Devices by Bandwidth Usage
        $topDevices = $this->getTopDevicesByBandwidth($tenantId, 5);

        // Recent Alerts
        $recentAlerts = NetworkAlert::where('tenant_id', $tenantId)
            ->with(['device', 'subscription.customer'])
            ->orderBy('triggered_at', 'desc')
            ->limit(10)
            ->get();

        // Network Topology Data
        $topologyData = $this->getNetworkTopologyData($tenantId);

        // Revenue Summary (this month)
        $revenueSummary = $this->getRevenueSummary($tenantId);

        return view('telecom.dashboard.index', compact(
            'stats',
            'bandwidthData',
            'deviceStatusData',
            'subscriptionStatusData',
            'topDevices',
            'recentAlerts',
            'topologyData',
            'revenueSummary'
        ));
    }

    /**
     * Get bandwidth chart data for last 24 hours.
     */
    protected function getBandwidthChartData(int $tenantId): array
    {
        $hours = collect(range(0, 23))->map(function ($hour) {
            return now()->subHours(23 - $hour)->format('H:00');
        });

        $usage = UsageTracking::where('tenant_id', $tenantId)
            ->where('period_start', '>=', now()->subDay())
            ->selectRaw('
                HOUR(period_start) as hour,
                SUM(bytes_in) as total_download,
                SUM(bytes_out) as total_upload
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $downloads = [];
        $uploads = [];

        for ($i = 0; $i < 24; $i++) {
            $record = $usage->get($i);
            $downloads[] = $record ? round($record->total_download / 1048576, 2) : 0; // Convert to MB
            $uploads[] = $record ? round($record->total_upload / 1048576, 2) : 0;
        }

        return [
            'labels' => $hours->toArray(),
            'downloads' => $downloads,
            'uploads' => $uploads,
        ];
    }

    /**
     * Get top devices by bandwidth usage.
     */
    protected function getTopDevicesByBandwidth(int $tenantId, int $limit = 5): array
    {
        return NetworkDevice::where('tenant_id', $tenantId)
            ->withCount([
                'subscriptions as active_subs' => function ($q) {
                    $q->where('status', 'active');
                },
                'hotspotUsers as hotspot_users_count'
            ])
            ->orderBy('active_subs', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'ip_address' => $device->ip_address,
                    'status' => $device->status,
                    'active_subscriptions' => $device->active_subs,
                    'hotspot_users' => $device->hotspot_users_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get network topology data.
     */
    protected function getNetworkTopologyData(int $tenantId): array
    {
        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->select('id', 'name', 'device_type', 'brand', 'ip_address', 'status', 'parent_device_id', 'location', 'latitude', 'longitude')
            ->get();

        $nodes = [];
        $edges = [];

        foreach ($devices as $device) {
            $nodes[] = [
                'id' => $device->id,
                'label' => $device->name,
                'type' => $device->device_type,
                'brand' => $device->brand,
                'ip' => $device->ip_address,
                'status' => $device->status,
                'location' => $device->location,
                'has_coordinates' => $device->latitude !== null && $device->longitude !== null,
            ];

            if ($device->parent_device_id) {
                $edges[] = [
                    'from' => $device->parent_device_id,
                    'to' => $device->id,
                ];
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    /**
     * Get revenue summary.
     */
    protected function getRevenueSummary(int $tenantId): array
    {
        $currentMonth = TelecomSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
            ->sum('internet_packages.price');

        $lastMonth = TelecomSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereMonth('started_at', now()->subMonth()->month)
            ->join('internet_packages', 'telecom_subscriptions.package_id', '=', 'internet_packages.id')
            ->sum('internet_packages.price');

        $growth = $lastMonth > 0
            ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2)
            : 0;

        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'growth_percent' => $growth,
            'formatted_current' => 'Rp ' . number_format($currentMonth, 0, ',', '.'),
            'formatted_last' => 'Rp ' . number_format($lastMonth, 0, ',', '.'),
        ];
    }

    /**
     * Get real-time device status (for AJAX).
     */
    public function getDeviceStatus(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $devices = NetworkDevice::where('tenant_id', $tenantId)
            ->select('id', 'name', 'status', 'last_seen_at', 'ip_address')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'status' => $device->status,
                    'last_seen' => $device->last_seen_at?->diffForHumans() ?? 'Never',
                    'ip_address' => $device->ip_address,
                ];
            });

        return response()->json([
            'success' => true,
            'devices' => $devices,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get real-time bandwidth data (for AJAX).
     */
    public function getBandwidthData(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $deviceId = $request->get('device_id');

        if ($deviceId) {
            $device = NetworkDevice::where('tenant_id', $tenantId)->findOrFail($deviceId);
            $monitoringService = new \App\Services\Telecom\BandwidthMonitoringService();
            $bandwidth = $monitoringService->getDeviceBandwidthUsage($device);

            return response()->json([
                'success' => true,
                'device_id' => $deviceId,
                'bandwidth' => $bandwidth,
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        // Overall bandwidth
        $bandwidthData = $this->getBandwidthChartData($tenantId);

        return response()->json([
            'success' => true,
            'chart_data' => $bandwidthData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
