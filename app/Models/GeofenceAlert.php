<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeofenceAlert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'device_id',
        'zone_id',
        'event_type',
        'latitude',
        'longitude',
        'distance_from_center_meters',
        'message',
        'is_notified',
        'triggered_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_from_center_meters' => 'integer',
        'is_notified' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the alert.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the device that triggered the alert.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }

    /**
     * Get the geofence zone.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(GeofenceZone::class, 'zone_id');
    }

    /**
     * Mark alert as notified.
     */
    public function markAsNotified(): void
    {
        $this->update(['is_notified' => true]);
    }

    /**
     * Scope for unnotified alerts.
     */
    public function scopeUnnotified($query)
    {
        return $query->where('is_notified', false);
    }

    /**
     * Scope for enter events.
     */
    public function scopeEnterEvents($query)
    {
        return $query->where('event_type', 'enter');
    }

    /**
     * Scope for exit events.
     */
    public function scopeExitEvents($query)
    {
        return $query->where('event_type', 'exit');
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('triggered_at', [$startDate, $endDate]);
    }

    /**
     * Scope for specific device.
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Get formatted alert message.
     */
    public function getFormattedMessageAttribute(): string
    {
        $action = $this->event_type === 'enter' ? 'entered' : 'exited';
        $zoneName = $this->zone?->name ?? 'Unknown Zone';
        $deviceName = $this->device?->name ?? 'Unknown Device';

        return "Device '{$deviceName}' {$action} zone '{$zoneName}'";
    }
}
