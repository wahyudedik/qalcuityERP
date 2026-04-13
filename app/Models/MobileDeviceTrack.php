<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileDeviceTrack extends Model
{
    use BelongsToTenant;

    protected $table = 'mobile_device_tracks';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'session_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'battery_level',
        'network_type',
        'route_metadata',
        'tracked_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meters' => 'integer',
        'battery_level' => 'float',
        'route_metadata' => 'array',
        'tracked_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the track.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the device that owns the track.
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
     * Scope for specific session.
     */
    public function scopeSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope for specific device.
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope ordered by tracked time.
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('tracked_at', 'asc');
    }

    /**
     * Get unique sessions for a device.
     */
    public static function getDeviceSessions(int $deviceId): array
    {
        return self::where('device_id', $deviceId)
            ->whereNotNull('session_id')
            ->distinct()
            ->pluck('session_id')
            ->toArray();
    }

    /**
     * Calculate total distance for a session.
     */
    public function getSessionDistance(): float
    {
        if (!$this->session_id) {
            return 0;
        }

        $tracks = self::where('session_id', $this->session_id)
            ->orderBy('tracked_at', 'asc')
            ->get();

        $totalDistance = 0;
        $previousTrack = null;

        foreach ($tracks as $track) {
            if ($previousTrack) {
                $totalDistance += $this->calculateDistance(
                    $previousTrack->latitude,
                    $previousTrack->longitude,
                    $track->latitude,
                    $track->longitude
                );
            }
            $previousTrack = $track;
        }

        return $totalDistance;
    }

    /**
     * Calculate distance between two points.
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        return 2 * $earthRadius * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));
    }
}
