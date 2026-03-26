<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBillingConfig extends Model
{
    protected $fillable = [
        'project_id', 'tenant_id', 'billing_type', 'hourly_rate',
        'retainer_amount', 'retainer_cycle', 'fixed_price',
        'next_billing_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate'      => 'decimal:2',
            'retainer_amount'  => 'decimal:2',
            'fixed_price'      => 'decimal:2',
            'next_billing_date'=> 'date',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
