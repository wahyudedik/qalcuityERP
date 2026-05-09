<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotspotUser extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'device_id',
        'username',
        'password_encrypted',
        'mac_address',
        'auth_type',
        'is_active',
        'activated_at',
        'expires_at',
        'rate_limit_download_kbps',
        'rate_limit_upload_kbps',
        'burst_limit_download_kbps',
        'burst_limit_upload_kbps',
        'burst_threshold_kbps',
        'burst_time_seconds',
        'quota_bytes',
        'quota_used_bytes',
        'quota_reset_at',
        'is_online',
        'current_ip_address',
        'last_login_at',
        'last_logout_at',
        'total_sessions',
        'total_uptime_seconds',
        'router_user_profile',
        'notes',
    ];

    protected $casts = [
        'router_user_profile' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'quota_reset_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'rate_limit_download_kbps' => 'integer',
        'rate_limit_upload_kbps' => 'integer',
        'burst_limit_download_kbps' => 'integer',
        'burst_limit_upload_kbps' => 'integer',
        'burst_threshold_kbps' => 'integer',
        'burst_time_seconds' => 'integer',
        'quota_bytes' => 'integer',
        'quota_used_bytes' => 'integer',
        'total_sessions' => 'integer',
        'total_uptime_seconds' => 'integer',
    ];

    protected $hidden = [
        'password_encrypted',
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
     * Get bandwidth allocations.
     */
    public function bandwidthAllocations(): HasMany
    {
        return $this->hasMany(BandwidthAllocation::class, 'hotspot_user_id');
    }

    /**
     * Get decrypted password.
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        if (! $this->password_encrypted) {
            return null;
        }

        return decrypt($this->password_encrypted);
    }

    /**
     * Set encrypted password.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password_encrypted'] = encrypt($value);
    }

    /**
     * Check if user is expired.
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if quota exceeded.
     */
    public function isQuotaExceeded(): bool
    {
        if ($this->quota_bytes == 0) {
            return false; // Unlimited
        }

        return $this->quota_used_bytes >= $this->quota_bytes;
    }

    /**
     * Get remaining quota in bytes.
     */
    public function getRemainingQuotaAttribute(): int
    {
        if ($this->quota_bytes == 0) {
            return -1; // Unlimited
        }

        return max(0, $this->quota_bytes - $this->quota_used_bytes);
    }

    /**
     * Get remaining quota in human readable format.
     */
    public function getRemainingQuotaFormattedAttribute(): string
    {
        $remaining = $this->remaining_quota;

        if ($remaining == -1) {
            return 'Unlimited';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($remaining >= 1024 && $i < count($units) - 1) {
            $remaining /= 1024;
            $i++;
        }

        return round($remaining, 2).' '.$units[$i];
    }

    /**
     * Mark user as online.
     */
    public function markAsOnline(string $ipAddress): void
    {
        $this->update([
            'is_online' => true,
            'current_ip_address' => $ipAddress,
            'last_login_at' => now(),
            'total_sessions' => $this->total_sessions + 1,
        ]);
    }

    /**
     * Mark user as offline.
     */
    public function markAsOffline(int $sessionDuration): void
    {
        $this->update([
            'is_online' => false,
            'last_logout_at' => now(),
            'total_uptime_seconds' => $this->total_uptime_seconds + $sessionDuration,
        ]);
    }

    /**
     * Update quota usage.
     */
    public function addUsage(int $bytes): void
    {
        $this->increment('quota_used_bytes', $bytes);
    }

    /**
     * Reset quota.
     */
    public function resetQuota(): void
    {
        $this->update([
            'quota_used_bytes' => 0,
            'quota_reset_at' => now(),
        ]);
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for online users.
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Scope for expired users.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }
}
