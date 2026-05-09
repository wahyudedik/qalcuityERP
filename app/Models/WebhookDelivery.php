<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'event_type',
        'payload',
        'response_code',
        'response_body',
        'attempt_count',
        'max_attempts',
        'status',
        'next_retry_at',
        'delivered_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'next_retry_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function subscription()
    {
        return $this->belongsTo(WebhookSubscription::class, 'subscription_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDueForRetry($query)
    {
        return $query->where('status', 'pending')
            ->where('next_retry_at', '<=', now());
    }

    public function scopeByEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return $this->attempt_count < $this->max_attempts && $this->status !== 'delivered';
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Schedule retry with exponential backoff
     * Delays: 1m, 5m, 15m, 1h, 4h
     */
    public function scheduleRetry(int $attempt): void
    {
        $delays = [60, 300, 900, 3600, 14400]; // seconds
        $delay = $delays[min($attempt, count($delays) - 1)];

        $this->update([
            'attempt_count' => $attempt + 1,
            'next_retry_at' => now()->addSeconds($delay),
        ]);
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempt(): void
    {
        $this->increment('attempt_count');
    }

    /**
     * Update response
     */
    public function updateResponse(int $code, string $body): void
    {
        $this->update([
            'response_code' => $code,
            'response_body' => $body,
        ]);
    }

    /**
     * Check if is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'delivered' && $this->response_code >= 200 && $this->response_code < 300;
    }

    /**
     * Check if needs retry
     */
    public function needsRetry(): bool
    {
        return $this->status === 'pending' && $this->canRetry();
    }

    /**
     * Get delay for next retry
     */
    public function getRetryDelay(): int
    {
        $delays = [60, 300, 900, 3600, 14400];

        return $delays[min($this->attempt_count, count($delays) - 1)];
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
            default => 'Unknown',
        };
    }

    /**
     * Get retry count remaining
     */
    public function getRetriesRemaining(): int
    {
        return max(0, $this->max_attempts - $this->attempt_count);
    }
}
