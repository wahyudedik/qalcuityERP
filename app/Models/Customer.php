<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
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
}
