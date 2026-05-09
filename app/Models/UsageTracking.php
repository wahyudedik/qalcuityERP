<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageTracking extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'device_id',
        'bytes_in',
        'bytes_out',
        'bytes_total',
        'packets_in',
        'packets_out',
        'sessions_count',
        'session_duration_seconds',
        'first_seen_at',
        'last_seen_at',
        'period_type',
        'period_start',
        'period_end',
        'peak_bandwidth_kbps',
        'peak_usage_time',
        'ip_address',
        'mac_address',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'peak_usage_time' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'bytes_total' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
        'sessions_count' => 'integer',
        'session_duration_seconds' => 'integer',
        'peak_bandwidth_kbps' => 'integer',
    ];

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TelecomSubscription::class, 'subscription_id');
    }

    /**
     * Get the device.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }

    /**
     * Get bytes in human readable format.
     */
    public function getBytesInFormattedAttribute(): string
    {
        return $this->formatBytes($this->bytes_in);
    }

    /**
     * Get bytes out in human readable format.
     */
    public function getBytesOutFormattedAttribute(): string
    {
        return $this->formatBytes($this->bytes_out);
    }

    /**
     * Get total bytes in human readable format.
     */
    public function getBytesTotalFormattedAttribute(): string
    {
        return $this->formatBytes($this->bytes_total);
    }

    /**
     * Get session duration in human readable format.
     */
    public function getSessionDurationFormattedAttribute(): string
    {
        $seconds = $this->session_duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        }

        return sprintf('%ds', $secs);
    }

    /**
     * Format bytes to human readable.
     */
    private function formatBytes(int $bytes): string
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
     * Scope for specific period type.
     */
    public function scopeByPeriodType($query, string $type)
    {
        return $query->where('period_type', $type);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate]);
    }

    /**
     * Scope for high usage (top consumers).
     */
    public function scopeHighUsage($query, int $limit = 10)
    {
        return $query->orderBy('bytes_total', 'desc')->limit($limit);
    }
}
