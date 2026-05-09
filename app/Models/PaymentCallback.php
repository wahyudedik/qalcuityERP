<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCallback extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payment_transaction_id',
        'gateway_provider',
        'event_type',
        'payload',
        'signature',
        'verified',
        'processed',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'verified' => 'boolean',
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Scope: Get unprocessed callbacks
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope: Get failed callbacks
     */
    public function scopeFailed($query)
    {
        return $query->where('processed', true)
            ->whereNotNull('error_message');
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }
}
