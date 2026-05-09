<?php

namespace App\Services\Telecom;

use App\Models\BandwidthAllocation;
use App\Models\NetworkDevice;
use App\Models\TelecomSubscription;
use App\Models\UsageTracking;
use Illuminate\Support\Facades\Cache;

/**
 * Service for monitoring bandwidth usage across all devices.
 */
class BandwidthMonitoringService
{
    protected RouterIntegrationService $integrationService;

    public function __construct()
    {
        $this->integrationService = new RouterIntegrationService;
    }

    /**
     * Get real-time bandwidth usage for a device.
     *
     * @return array Current bandwidth stats
     */
    public function getDeviceBandwidthUsage(NetworkDevice $device): array
    {
        $cacheKey = "device_bandwidth_{$device->id}";

        return Cache::remember($cacheKey, 30, function () use ($device) {
            try {
                $adapter = RouterAdapterFactory::create($device);
                $interfaces = $adapter->getInterfaceStats();

                $totalDownload = 0;
                $totalUpload = 0;

                foreach ($interfaces as $interface) {
                    $totalDownload += (int) ($interface['rx-byte'] ?? 0);
                    $totalUpload += (int) ($interface['tx-byte'] ?? 0);
                }

                return [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'total_download_bytes' => $totalDownload,
                    'total_upload_bytes' => $totalUpload,
                    'total_download_formatted' => $this->formatBytes($totalDownload),
                    'total_upload_formatted' => $this->formatBytes($totalUpload),
                    'interfaces' => collect($interfaces)->map(function ($iface) {
                        return [
                            'name' => $iface['name'] ?? 'Unknown',
                            'download' => $iface['rx-byte'] ?? 0,
                            'upload' => $iface['tx-byte'] ?? 0,
                            'download_formatted' => $this->formatBytes($iface['rx-byte'] ?? 0),
                            'upload_formatted' => $this->formatBytes($iface['tx-byte'] ?? 0),
                        ];
                    })->toArray(),
                    'timestamp' => now()->toIso8601String(),
                ];

            } catch (\Exception $e) {
                return [
                    'device_id' => $device->id,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ];
            }
        });
    }

    /**
     * Get bandwidth usage trend for a device (last 24 hours).
     *
     * @return array Hourly usage data
     */
    public function getBandwidthTrend(NetworkDevice $device, int $hours = 24): array
    {
        $startTime = now()->subHours($hours);

        $usageRecords = UsageTracking::where('device_id', $device->id)
            ->where('period_start', '>=', $startTime)
            ->orderBy('period_start')
            ->get();

        $trend = [];

        foreach ($usageRecords as $record) {
            $trend[] = [
                'timestamp' => $record->period_start->toIso8601String(),
                'bytes_in' => $record->bytes_in,
                'bytes_out' => $record->bytes_out,
                'bytes_total' => $record->bytes_total,
                'bytes_in_formatted' => $record->bytes_in_formatted,
                'bytes_out_formatted' => $record->bytes_out_formatted,
                'peak_bandwidth_kbps' => $record->peak_bandwidth_kbps,
            ];
        }

        return $trend;
    }

    /**
     * Get top bandwidth consumers.
     *
     * @return array Top users
     */
    public function getTopConsumers(int $tenantId, int $limit = 10): array
    {
        $topUsers = UsageTracking::where('tenant_id', $tenantId)
            ->selectRaw('subscription_id, SUM(bytes_total) as total_bytes, COUNT(*) as record_count')
            ->where('period_start', '>=', now()->startOfMonth())
            ->groupBy('subscription_id')
            ->orderByDesc('total_bytes')
            ->limit($limit)
            ->get();

        return $topUsers->map(function ($item) {
            $subscription = TelecomSubscription::with('customer')->find($item->subscription_id);

            return [
                'subscription_id' => $item->subscription_id,
                'customer_name' => $subscription?->customer?->name ?? 'Unknown',
                'total_bytes' => $item->total_bytes,
                'total_formatted' => $this->formatBytes($item->total_bytes),
                'record_count' => $item->record_count,
            ];
        })->toArray();
    }

    /**
     * Monitor bandwidth allocations and detect overuse.
     *
     * @return array Allocations with usage status
     */
    public function monitorAllocations(NetworkDevice $device): array
    {
        $allocations = BandwidthAllocation::where('device_id', $device->id)
            ->where('is_active', true)
            ->with(['subscription.customer', 'hotspotUser'])
            ->get();

        $monitoring = [];

        foreach ($allocations as $allocation) {
            // Get current usage
            $currentUsage = $this->getCurrentAllocationUsage($allocation);

            $usagePercent = 0;
            if ($allocation->max_download_kbps > 0) {
                $usagePercent = ($currentUsage['current_download_kbps'] / $allocation->max_download_kbps) * 100;
            }

            $monitoring[] = [
                'allocation_id' => $allocation->id,
                'allocation_name' => $allocation->allocation_name,
                'type' => $allocation->allocation_type,
                'customer' => $allocation->subscription?->customer?->name ??
                    $allocation->hotspotUser?->username ?? 'N/A',
                'max_download_kbps' => $allocation->max_download_kbps,
                'max_upload_kbps' => $allocation->max_upload_kbps,
                'current_download_kbps' => $currentUsage['current_download_kbps'],
                'current_upload_kbps' => $currentUsage['current_upload_kbps'],
                'usage_percent' => round($usagePercent, 2),
                'status' => $this->getUsageStatus($usagePercent),
                'priority' => $allocation->priority,
            ];
        }

        return $monitoring;
    }

    /**
     * Get current usage for an allocation.
     *
     * @return array Current usage stats
     */
    protected function getCurrentAllocationUsage(BandwidthAllocation $allocation): array
    {
        // Try to get from active sessions
        if ($allocation->hotspot_user_id) {
            $hotspotUser = $allocation->hotspotUser;

            if ($hotspotUser && $hotspotUser->is_online) {
                try {
                    $adapter = RouterAdapterFactory::create($allocation->device);
                    $usage = $adapter->getUserUsage($hotspotUser->username);

                    return [
                        'current_download_kbps' => $this->calculateKbpsFromBytes(
                            $usage['bytes_in'] ?? 0,
                            $usage['uptime_seconds'] ?? 3600
                        ),
                        'current_upload_kbps' => $this->calculateKbpsFromBytes(
                            $usage['bytes_out'] ?? 0,
                            $usage['uptime_seconds'] ?? 3600
                        ),
                    ];
                } catch (\Exception $e) {
                    // Fallback to last known usage
                }
            }
        }

        // Fallback: use last tracking record
        $lastRecord = UsageTracking::where(function ($q) use ($allocation) {
            if ($allocation->subscription_id) {
                $q->where('subscription_id', $allocation->subscription_id);
            } elseif ($allocation->hotspot_user_id) {
                $q->whereHas('subscription', function ($sq) use ($allocation) {
                    $sq->whereHas('hotspotUsers', function ($hq) use ($allocation) {
                        $hq->where('id', $allocation->hotspot_user_id);
                    });
                });
            }
        })
            ->latest('period_start')
            ->first();

        if ($lastRecord) {
            return [
                'current_download_kbps' => $this->calculateKbpsFromBytes(
                    $lastRecord->bytes_in,
                    3600 // Assume 1 hour period
                ),
                'current_upload_kbps' => $this->calculateKbpsFromBytes(
                    $lastRecord->bytes_out,
                    3600
                ),
            ];
        }

        return [
            'current_download_kbps' => 0,
            'current_upload_kbps' => 0,
        ];
    }

    /**
     * Calculate Kbps from bytes and time.
     */
    protected function calculateKbpsFromBytes(int $bytes, int $seconds): float
    {
        if ($seconds == 0) {
            return 0;
        }

        $bits = $bytes * 8;
        $kbps = ($bits / $seconds) / 1024;

        return round($kbps, 2);
    }

    /**
     * Get usage status based on percentage.
     */
    protected function getUsageStatus(float $percent): string
    {
        if ($percent >= 90) {
            return 'critical';
        } elseif ($percent >= 75) {
            return 'warning';
        } elseif ($percent >= 50) {
            return 'moderate';
        }

        return 'normal';
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Clear bandwidth cache for a device.
     */
    public function clearCache(NetworkDevice $device): void
    {
        Cache::forget("device_bandwidth_{$device->id}");
    }
}
