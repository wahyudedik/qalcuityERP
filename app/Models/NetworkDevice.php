<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkDevice extends Model
{
    use SoftDeletes, BelongsToTenant;

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
        'location',
        'latitude',
        'longitude',
        'coverage_radius',
        'parent_device_id',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'configuration' => 'array',
        'last_seen_at' => 'datetime',
        'port' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'coverage_radius' => 'integer',
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
     * Get location history for this device.
     */
    public function locationHistory(): HasMany
    {
        return $this->hasMany(LocationHistory::class, 'device_id');
    }

    /**
     * Get geofence zones assigned to this device.
     */
    public function geofenceZones(): BelongsToMany
    {
        return $this->belongsToMany(GeofenceZone::class, 'device_geofence_assignments', 'device_id', 'zone_id')
            ->withPivot('alert_type', 'is_enabled')
            ->withTimestamps();
    }

    /**
     * Get geofence alerts for this device.
     */
    public function geofenceAlerts(): HasMany
    {
        return $this->hasMany(GeofenceAlert::class, 'device_id');
    }

    /**
     * Get mobile tracking sessions.
     */
    public function mobileTracks(): HasMany
    {
        return $this->hasMany(MobileDeviceTrack::class, 'device_id');
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

    /**
     * Check if device has GPS coordinates.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Calculate distance to another point using Haversine formula.
     * Returns distance in meters.
     */
    public function getDistanceTo(float $lat, float $lng): float
    {
        if (!$this->hasCoordinates()) {
            return -1;
        }

        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Get formatted coordinates string.
     */
    public function getCoordinatesAttribute(): ?string
    {
        if ($this->hasCoordinates()) {
            return "{$this->latitude}, {$this->longitude}";
        }
        return null;
    }

    /**
     * Get Google Maps URL for this device.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->hasCoordinates()) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }

    /**
     * Scope for devices with coordinates.
     */
    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    /**
     * Scope for devices near a location within radius (in meters).
     */
    public function scopeNearby($query, float $lat, float $lng, float $radius = 5000)
    {
        // Use simplified bounding box for performance
        // 1 degree latitude ≈ 111km
        $latRange = $radius / 111000;
        // 1 degree longitude ≈ 111km * cos(latitude)
        $lngRange = $radius / (111000 * cos(deg2rad($lat)));

        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
            ->whereBetween('longitude', [$lng - $lngRange, $lng + $lngRange]);
    }

    /**
     * Scope for devices in a location (by name search).
     */
    public function scopeInLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }
}
