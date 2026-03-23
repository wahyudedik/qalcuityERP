<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'company',
        'address', 'npwp', 'credit_limit', 'is_active',
    ];

    protected function casts(): array
    {
        return ['credit_limit' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function quotations(): HasMany { return $this->hasMany(Quotation::class); }
    public function salesOrders(): HasMany { return $this->hasMany(SalesOrder::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function customerBalance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CustomerBalance::class);
    }
    public function balanceTransactions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(CustomerBalanceTransaction::class, CustomerBalance::class);
    }

    public function priceLists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'customer_price_lists')
            ->withPivot('priority')
            ->withTimestamps()
            ->orderBy('customer_price_lists.priority');
    }

    /** Total piutang outstanding (unpaid + partial) */
    public function outstandingBalance(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_amount');
    }

    /** Cek apakah order baru akan melampaui credit limit */
    public function wouldExceedCreditLimit(float $orderAmount): bool
    {
        if (! $this->credit_limit || $this->credit_limit <= 0) return false;
        return ($this->outstandingBalance() + $orderAmount) > (float) $this->credit_limit;
    }

    /** Sisa kredit yang tersedia */
    public function availableCredit(): float
    {
        if (! $this->credit_limit || $this->credit_limit <= 0) return PHP_FLOAT_MAX;
        return max(0, (float) $this->credit_limit - $this->outstandingBalance());
    }
}
