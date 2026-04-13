<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeofenceZone extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'zone_type',
        'center_latitude',
        'center_longitude',
        'radius_meters',
        'polygon_coordinates',
        'is_active',
        'alert_settings',
    ];

    protected $casts = [
        'polygon_coordinates' => 'array',
        'alert_settings' => 'array',
        'is_active' => 'boolean',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'radius_meters' => 'integer',
    ];

    /**
     * Get the tenant that owns the zone.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get devices assigned to this zone.
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(NetworkDevice::class, 'device_geofence_assignments', 'zone_id', 'device_id')
            ->withPivot('alert_type', 'is_enabled')
            ->withTimestamps();
    }

    /**
     * Get geofence alerts for this zone.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(GeofenceAlert::class, 'zone_id');
    }

    /**
     * Check if a point is inside this zone.
     */
    public function containsPoint(float $latitude, float $longitude): bool
    {
        if ($this->zone_type === 'circular') {
            return $this->isPointInCircle($latitude, $longitude);
        } elseif ($this->zone_type === 'polygon') {
            return $this->isPointInPolygon($latitude, $longitude);
        }

        return false;
    }

    /**
     * Check if point is inside circular zone.
     */
    protected function isPointInCircle(float $latitude, float $longitude): bool
    {
        if (!$this->center_latitude || !$this->center_longitude || !$this->radius_meters) {
            return false;
        }

        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($this->center_latitude);
        $lonFrom = deg2rad($this->center_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $distance = 2 * $earthRadius * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));

        return $distance <= $this->radius_meters;
    }

    /**
     * Check if point is inside polygon using ray-casting algorithm.
     */
    protected function isPointInPolygon(float $latitude, float $longitude): bool
    {
        if (!$this->polygon_coordinates || empty($this->polygon_coordinates)) {
            return false;
        }

        $vertices = $this->polygon_coordinates;
        $count = count($vertices);
        $inside = false;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $vertices[$i][0];
            $yi = $vertices[$i][1];
            $xj = $vertices[$j][0];
            $yj = $vertices[$j][1];

            $intersect = (($yi > $longitude) != ($yj > $longitude))
                && ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Get distance from center to point.
     */
    public function getDistanceFromCenter(float $latitude, float $longitude): float
    {
        if (!$this->center_latitude || !$this->center_longitude) {
            return -1;
        }

        $earthRadius = 6371000;

        $latFrom = deg2rad($this->center_latitude);
        $lonFrom = deg2rad($this->center_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        return 2 * $earthRadius * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));
    }

    /**
     * Scope for active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for circular zones.
     */
    public function scopeCircular($query)
    {
        return $query->where('zone_type', 'circular');
    }

    /**
     * Scope for polygon zones.
     */
    public function scopePolygon($query)
    {
        return $query->where('zone_type', 'polygon');
    }
}
