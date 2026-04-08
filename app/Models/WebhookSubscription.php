<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'endpoint_url',
        'secret_key',
        'events',
        'is_active',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class, 'subscription_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->whereJsonContains('events', $event);
    }

    /**
     * Check if subscribed to event
     */
    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events);
    }

    /**
     * Generate HMAC signature for payload
     */
    public function generateSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret_key);
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($payload);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Mark as triggered
     */
    public function markAsTriggered(): void
    {
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Toggle active status
     */
    public function toggle(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Activate subscription
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate subscription
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Add event to subscription
     */
    public function addEvent(string $event): void
    {
        $events = $this->events;

        if (!in_array($event, $events)) {
            $events[] = $event;
            $this->update(['events' => $events]);
        }
    }

    /**
     * Remove event from subscription
     */
    public function removeEvent(string $event): void
    {
        $events = array_filter($this->events, fn($e) => $e !== $event);
        $this->update(['events' => array_values($events)]);
    }

    /**
     * Get delivery statistics
     */
    public function getDeliveryStats(): array
    {
        return [
            'total' => $this->deliveries()->count(),
            'delivered' => $this->deliveries()->where('status', 'delivered')->count(),
            'failed' => $this->deliveries()->where('status', 'failed')->count(),
            'pending' => $this->deliveries()->where('status', 'pending')->count(),
            'success_rate' => $this->deliveries()->count() > 0
                ? ($this->deliveries()->where('status', 'delivered')->count() / $this->deliveries()->count()) * 100
                : 0,
        ];
    }
}
