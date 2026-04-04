<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaPackageItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'package_id',
        'treatment_id',
        'sequence_order',
        'duration_override',
    ];

    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'duration_override' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SpaPackage::class, 'package_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(SpaTreatment::class, 'treatment_id');
    }
}
