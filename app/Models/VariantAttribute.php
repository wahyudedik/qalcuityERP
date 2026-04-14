<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttribute extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'attribute_name',
        'attribute_type',
        'attribute_values',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'attribute_values' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('attribute_name');
    }
}
