<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BandwidthAllocation extends Model
{
    protected $fillable = [
        'tenant_id',
        'device_id',
        'subscription_id',
        'hotspot_user_id',
        'allocation_name',
        'allocation_type',
        'max_download_kbps',
        'max_upload_kbps',
        'guaranteed_download_kbps',
        'guaranteed_upload_kbps',
        'priority',
        'queue_type',
        'queue_parameters',
        'time_rules',
        'is_active',
        'active_from',
        'active_until',
        'current_usage_bytes',
        'last_updated_at',
        'notes',
    ];

    protected $casts = [
        'queue_parameters' => 'array',
        'time_rules' => 'array',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'last_updated_at' => 'datetime',
        'max_download_kbps' => 'integer',
        'max_upload_kbps' => 'integer',
        'guaranteed_download_kbps' => 'integer',
        'guaranteed_upload_kbps' => 'integer',
        'priority' => 'integer',
        'current_usage_bytes' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the device.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }

    /**
     * Get the subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TelecomSubscription::class, 'subscription_id');
    }

    /**
     * Get the hotspot user.
     */
    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }

    /**
     * Check if allocation is currently active based on time rules.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->active_from && now()->lessThan($this->active_from)) {
            return false;
        }

        if ($this->active_until && now()->greaterThan($this->active_until)) {
            return false;
        }

        // Check time-based rules if exists
        if ($this->time_rules) {
            return $this->checkTimeRules();
        }

        return true;
    }

    /**
     * Check time-based rules.
     */
    private function checkTimeRules(): bool
    {
        $now = now();
        $currentDay = strtolower($now->format('l')); // monday, tuesday, etc
        $currentTime = $now->format('H:i');

        foreach ($this->time_rules as $rule) {
            if (in_array($currentDay, $rule['days'] ?? [])) {
                if (
                    $currentTime >= ($rule['start_time'] ?? '00:00') &&
                    $currentTime <= ($rule['end_time'] ?? '23:59')
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get max bandwidth in Mbps.
     */
    public function getMaxDownloadMbpsAttribute(): float
    {
        return round($this->max_download_kbps / 1024, 2);
    }

    /**
     * Get max upload in Mbps.
     */
    public function getMaxUploadMbpsAttribute(): float
    {
        return round($this->max_upload_kbps / 1024, 2);
    }

    /**
     * Get current usage in human readable format.
     */
    public function getCurrentUsageFormattedAttribute(): string
    {
        $bytes = $this->current_usage_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope for active allocations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by allocation type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('allocation_type', $type);
    }

    /**
     * Scope ordered by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}
