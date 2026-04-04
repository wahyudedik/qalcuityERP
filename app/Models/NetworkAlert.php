<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkAlert extends Model
{
    protected $fillable = [
        'tenant_id',
        'device_id',
        'subscription_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'status',
        'threshold_data',
        'current_metrics',
        'notification_sent',
        'notification_sent_at',
        'notified_users',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'threshold_data' => 'array',
        'current_metrics' => 'array',
        'notified_users' => 'array',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
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
     * Get the user who acknowledged.
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get the user who resolved.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Check if alert is new.
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Check if alert is acknowledged.
     */
    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    /**
     * Check if alert is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Acknowledge the alert.
     */
    public function acknowledge(User $user): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Resolve the alert.
     */
    public function resolve(User $user, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Ignore the alert.
     */
    public function ignore(): void
    {
        $this->update(['status' => 'ignored']);
    }

    /**
     * Mark notification as sent.
     */
    public function markNotificationSent(array $userIds = []): void
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
            'notified_users' => $userIds,
        ]);
    }

    /**
     * Get severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'low' => 'blue',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'red',
            'acknowledged' => 'yellow',
            'resolved' => 'green',
            'ignored' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get age of alert in human readable format.
     */
    public function getAgeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope for unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['new', 'acknowledged']);
    }

    /**
     * Scope for critical alerts.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for specific device.
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope recent alerts.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
