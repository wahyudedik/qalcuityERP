<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class TaxRecord extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'tax_code', 'type', 'related_type', 'related_id',
        'party_name', 'npwp', 'base_amount', 'tax_amount', 'rate',
        'transaction_date', 'period', 'status', 'notes',
    ];

    protected $casts = ['transaction_date' => 'date', 'base_amount' => 'float', 'tax_amount' => 'float', 'rate' => 'float'];
}
