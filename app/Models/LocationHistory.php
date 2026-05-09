<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'location_history';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'altitude_meters',
        'speed_kmh',
        'heading_degrees',
        'source',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meters' => 'integer',
        'altitude_meters' => 'integer',
        'speed_kmh' => 'float',
        'heading_degrees' => 'float',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the location history.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the device that owns the location history.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }

    /**
     * Get formatted coordinates.
     */
    public function getCoordinatesAttribute(): string
    {
        return "{$this->latitude}, {$this->longitude}";
    }

    /**
     * Get Google Maps URL.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Scope for specific device.
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Scope for specific source.
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope ordered by recorded time.
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('recorded_at', 'asc');
    }

    /**
     * Calculate distance from previous location.
     */
    public function getDistanceFromPrevious(): ?float
    {
        $previous = self::where('device_id', $this->device_id)
            ->where('recorded_at', '<', $this->recorded_at)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (! $previous) {
            return null;
        }

        $earthRadius = 6371000;

        $latFrom = deg2rad($previous->latitude);
        $lonFrom = deg2rad($previous->longitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        return 2 * $earthRadius * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));
    }

    /**
     * Get movement speed in km/h (calculated from distance/time).
     */
    public function getCalculatedSpeedKmh(): ?float
    {
        $previous = self::where('device_id', $this->device_id)
            ->where('recorded_at', '<', $this->recorded_at)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (! $previous) {
            return null;
        }

        $distance = $this->getDistanceFromPrevious();
        $timeDiffHours = $previous->recorded_at->diffInMinutes($this->recorded_at) / 60;

        if ($timeDiffHours == 0) {
            return 0;
        }

        return ($distance / 1000) / $timeDiffHours;
    }
}
