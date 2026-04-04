<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'device_type',
        'brand',
        'model',
        'ip_address',
        'port',
        'username',
        'password_encrypted',
        'api_token',
        'mac_address',
        'serial_number',
        'firmware_version',
        'status',
        'last_seen_at',
        'capabilities',
        'configuration',
        'notes',
        'parent_device_id',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'configuration' => 'array',
        'last_seen_at' => 'datetime',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password_encrypted',
        'api_token',
    ];

    /**
     * Get the tenant that owns the device.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent device (for hierarchical networks).
     */
    public function parentDevice(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'parent_device_id');
    }

    /**
     * Get child devices.
     */
    public function childDevices(): HasMany
    {
        return $this->hasMany(NetworkDevice::class, 'parent_device_id');
    }

    /**
     * Get subscriptions assigned to this device.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TelecomSubscription::class, 'device_id');
    }

    /**
     * Get hotspot users managed by this device.
     */
    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'device_id');
    }

    /**
     * Get bandwidth allocations for this device.
     */
    public function bandwidthAllocations(): HasMany
    {
        return $this->hasMany(BandwidthAllocation::class, 'device_id');
    }

    /**
     * Get usage tracking records.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageTracking::class, 'device_id');
    }

    /**
     * Get alerts for this device.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(NetworkAlert::class, 'device_id');
    }

    /**
     * Check if device is online.
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Mark device as online.
     */
    public function markAsOnline(): void
    {
        $this->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Mark device as offline.
     */
    public function markAsOffline(): void
    {
        $this->update([
            'status' => 'offline',
            'last_seen_at' => $this->last_seen_at ?? now(),
        ]);
    }

    /**
     * Get decrypted password.
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        if (!$this->password_encrypted) {
            return null;
        }

        return decrypt($this->password_encrypted);
    }

    /**
     * Set encrypted password.
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['password_encrypted'] = encrypt($value);
        }
    }

    /**
     * Scope for active devices.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['online', 'maintenance']);
    }

    /**
     * Scope for online devices.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope for specific brand.
     */
    public function scopeByBrand($query, string $brand)
    {
        return $query->where('brand', strtolower($brand));
    }
}
