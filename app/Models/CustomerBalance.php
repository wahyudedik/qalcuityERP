<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerBalance extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function tenant(): BelongsTo       { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo     { return $this->belongsTo(Customer::class); }
    public function transactions(): HasMany   { return $this->hasMany(CustomerBalanceTransaction::class); }

    /** Tambah saldo (kredit/overpayment) */
    public function credit(float $amount, string $type, string $reference, ?int $refId = null): CustomerBalanceTransaction
    {
        $this->increment('balance', $amount);
        return $this->transactions()->create([
            'tenant_id' => $this->tenant_id,
            'type'      => 'credit',
            'amount'    => $amount,
            'ref_type'  => $type,
            'ref_id'    => $refId,
            'reference' => $reference,
            'balance_after' => $this->fresh()->balance,
        ]);
    }

    /** Kurangi saldo (debit/dipakai bayar invoice) */
    public function debit(float $amount, string $type, string $reference, ?int $refId = null): CustomerBalanceTransaction
    {
        $this->decrement('balance', $amount);
        return $this->transactions()->create([
            'tenant_id' => $this->tenant_id,
            'type'      => 'debit',
            'amount'    => $amount,
            'ref_type'  => $type,
            'ref_id'    => $refId,
            'reference' => $reference,
            'balance_after' => $this->fresh()->balance,
        ]);
    }
}
