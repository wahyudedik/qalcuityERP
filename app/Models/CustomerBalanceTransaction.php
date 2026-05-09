<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerBalanceTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_balance_id', 'type', 'amount',
        'ref_type', 'ref_id', 'reference', 'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customerBalance(): BelongsTo
    {
        return $this->belongsTo(CustomerBalance::class);
    }
}
