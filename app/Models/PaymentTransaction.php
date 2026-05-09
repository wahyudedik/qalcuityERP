<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'sales_order_id',
        'transaction_number',
        'gateway_provider',
        'gateway_transaction_id',
        'payment_method',
        'payment_channel',
        'amount',
        'fee',
        'status',
        'gateway_response',
        'qr_string',
        'qr_image_url',
        'paid_at',
        'expired_at',
        'failure_reason',
        'metadata',
        'stock_deducted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'gateway_response' => 'string',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Check if transaction is expired
     */
    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }
}
