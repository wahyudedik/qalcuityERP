<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'gateway_response' => 'array',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(int $tenantId): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');

        $lastTransaction = static::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? (intval(substr($lastTransaction->transaction_number, -4)) + 1) : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'waiting_payment', 'processing']);
    }

    /**
     * Check if payment has expired
     */
    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast() && !$this->isCompleted();
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get net amount after fees
     */
    public function getNetAmountAttribute(): float
    {
        return $this->amount - $this->fee;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // Update related order
        if ($this->salesOrder) {
            $this->salesOrder->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);
        }
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Scope: Get successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'waiting_payment', 'processing']);
    }

    /**
     * Scope: Get payments by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get total amount for query
     */
    public static function getTotalAmount($query): float
    {
        return $query->sum('amount');
    }

    /**
     * Get success rate
     */
    public static function getSuccessRate(int $tenantId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = static::where('tenant_id', $tenantId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = $query->count();

        if ($total === 0) {
            return 0;
        }

        $successful = (clone $query)->where('status', 'success')->count();

        return ($successful / $total) * 100;
    }
}
