<?php

namespace App\Services\Telecom;

use App\Models\TelecomSubscription;
use App\Models\HotspotUser;
use App\Models\UsageTracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing usage tracking and quota.
 */
class UsageTrackingService
{
    /**
     * Record usage for a subscription.
     * 
     * @param TelecomSubscription $subscription
     * @param int $bytesIn Download bytes
     * @param int $bytesOut Upload bytes
     * @param array $metadata Additional data
     * @return UsageTracking
     */
    public function recordUsage(TelecomSubscription $subscription, int $bytesIn, int $bytesOut, array $metadata = []): UsageTracking
    {
        return DB::transaction(function () use ($subscription, $bytesIn, $bytesOut, $metadata) {
            $bytesTotal = $bytesIn + $bytesOut;

            // Update subscription quota
            $subscription->increment('quota_used_bytes', $bytesTotal);

            // Check if quota exceeded
            if ($subscription->package && !$subscription->package->isUnlimited()) {
                $quotaExceeded = $subscription->quota_used_bytes >= $subscription->package->quota_bytes;

                if ($quotaExceeded && !$subscription->quota_exceeded) {
                    $subscription->update(['quota_exceeded' => true]);

                    // Trigger webhook or notification here
                    $this->handleQuotaExceeded($subscription);
                }
            }

            // Create usage tracking record
            $usageRecord = UsageTracking::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'device_id' => $subscription->device_id,
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'bytes_total' => $bytesTotal,
                'packets_in' => $metadata['packets_in'] ?? 0,
                'packets_out' => $metadata['packets_out'] ?? 0,
                'session_duration_seconds' => $metadata['duration_seconds'] ?? 0,
                'period_type' => $metadata['period_type'] ?? 'daily',
                'period_start' => $metadata['period_start'] ?? now()->startOfDay(),
                'period_end' => $metadata['period_end'] ?? now()->endOfDay(),
                'peak_bandwidth_kbps' => $metadata['peak_bandwidth_kbps'] ?? 0,
                'ip_address' => $metadata['ip_address'] ?? null,
                'mac_address' => $metadata['mac_address'] ?? null,
                'additional_data' => $metadata,
            ]);

            return $usageRecord;
        });
    }

    /**
     * Get usage summary for a subscription.
     * 
     * @param TelecomSubscription $subscription
     * @param string $period daily|weekly|monthly
     * @return array
     */
    public function getUsageSummary(TelecomSubscription $subscription, string $period = 'monthly'): array
    {
        $dateRange = $this->getDateRangeForPeriod($period);

        $usage = UsageTracking::where('subscription_id', $subscription->id)
            ->whereBetween('period_start', $dateRange)
            ->selectRaw('
                SUM(bytes_in) as total_download,
                SUM(bytes_out) as total_upload,
                SUM(bytes_total) as total_usage,
                AVG(peak_bandwidth_kbps) as avg_peak_bandwidth,
                COUNT(*) as record_count
            ')
            ->first();

        $quotaRemaining = 0;
        $quotaUsedPercent = 0;

        if ($subscription->package && !$subscription->package->isUnlimited()) {
            $quotaRemaining = max(0, $subscription->package->quota_bytes - $subscription->quota_used_bytes);
            $quotaUsedPercent = ($subscription->quota_used_bytes / $subscription->package->quota_bytes) * 100;
        }

        return [
            'subscription_id' => $subscription->id,
            'customer_name' => $subscription->customer?->name ?? 'Unknown',
            'package_name' => $subscription->package?->name ?? 'N/A',
            'period' => $period,
            'total_download' => (int) ($usage->total_download ?? 0),
            'total_upload' => (int) ($usage->total_upload ?? 0),
            'total_usage' => (int) ($usage->total_usage ?? 0),
            'total_download_formatted' => $this->formatBytes((int) ($usage->total_download ?? 0)),
            'total_upload_formatted' => $this->formatBytes((int) ($usage->total_upload ?? 0)),
            'total_usage_formatted' => $this->formatBytes((int) ($usage->total_usage ?? 0)),
            'avg_peak_bandwidth_kbps' => round((float) ($usage->avg_peak_bandwidth ?? 0), 2),
            'record_count' => (int) ($usage->record_count ?? 0),
            'quota_used_bytes' => $subscription->quota_used_bytes,
            'quota_limit_bytes' => $subscription->package?->quota_bytes ?? 0,
            'quota_remaining_bytes' => $quotaRemaining,
            'quota_used_percent' => round($quotaUsedPercent, 2),
            'quota_exceeded' => $subscription->quota_exceeded,
        ];
    }

    /**
     * Handle quota exceeded event.
     */
    protected function handleQuotaExceeded(TelecomSubscription $subscription): void
    {
        // Create alert
        \App\Models\NetworkAlert::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'alert_type' => 'quota_exceeded',
            'severity' => 'high',
            'title' => "Kuota Habis: {$subscription->customer?->name}",
            'message' => "Customer telah melebihi batas kuota paket {$subscription->package?->name}.",
        ]);

        // Optionally disconnect user from router
        if ($subscription->hotspot_username) {
            try {
                $adapter = RouterAdapterFactory::create($subscription->device);
                $adapter->disconnectUser($subscription->hotspot_username);
            } catch (\Exception $e) {
                Log::error("Failed to disconnect user after quota exceeded", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get date range for period.
     */
    protected function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
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

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
