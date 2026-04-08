<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbSupplyTransaction extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'supply_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference',
        'notes',
        'created_by',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(FbSupply::class, 'supply_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
