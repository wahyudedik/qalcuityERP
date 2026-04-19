<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherCode extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'package_id',
        'generated_by',
        'code',
        'batch_number',
        'status',
        'valid_from',
        'valid_until',
        'validity_hours',
        'first_used_at',
        'last_used_at',
        'used_by_customer_id',
        'used_by_username',
        'usage_count',
        'max_usage',
        'download_speed_mbps',
        'upload_speed_mbps',
        'quota_bytes',
        'sale_price',
        'sold_at',
        'sold_to_customer_id',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'sold_at' => 'datetime',
        'validity_hours' => 'integer',
        'usage_count' => 'integer',
        'max_usage' => 'integer',
        'download_speed_mbps' => 'integer',
        'upload_speed_mbps' => 'integer',
        'quota_bytes' => 'integer',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Get the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the package.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(InternetPackage::class, 'package_id');
    }

    /**
     * Get the user who generated this voucher.
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the customer who used this voucher (alias for usedByCustomer).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'used_by_customer_id');
    }

    /**
     * Get the customer who used this voucher.
     */
    public function usedByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'used_by_customer_id');
    }

    /**
     * Get the customer who bought this voucher.
     */
    public function soldToCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'sold_to_customer_id');
    }

    /**
     * Check if voucher is unused.
     */
    public function isUnused(): bool
    {
        return $this->status === 'unused';
    }

    /**
     * Check if voucher is used.
     */
    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    /**
     * Check if voucher is expired.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->valid_until && now()->greaterThan($this->valid_until)) {
            return true;
        }

        return false;
    }

    /**
     * Check if voucher can still be used.
     */
    public function canBeUsed(): bool
    {
        return $this->isUnused()
            && !$this->isExpired()
            && $this->usage_count < $this->max_usage;
    }

    /**
     * Mark voucher as used.
     */
    public function markAsUsed(?Customer $customer = null, ?string $username = null): void
    {
        $updates = [
            'status' => 'used',
            'usage_count' => $this->usage_count + 1,
            'last_used_at' => now(),
        ];

        if ($customer) {
            $updates['used_by_customer_id'] = $customer->id;
        }

        if ($username) {
            $updates['used_by_username'] = $username;
        }

        if (!$this->first_used_at) {
            $updates['first_used_at'] = now();
        }

        $this->update($updates);
    }

    /**
     * Mark voucher as expired.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark voucher as revoked.
     */
    public function markAsRevoked(): void
    {
        $this->update(['status' => 'revoked']);
    }

    /**
     * Get validity status.
     */
    public function getValidityStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isUsed()) {
            return 'Used';
        }

        if ($this->canBeUsed()) {
            return 'Valid';
        }

        return 'Invalid';
    }

    /**
     * Scope for unused vouchers.
     */
    public function scopeUnused($query)
    {
        return $query->where('status', 'unused');
    }

    /**
     * Scope for valid vouchers.
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'unused')
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            });
    }

    /**
     * Scope by batch number.
     */
    public function scopeByBatch($query, string $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }
}
