<?php

namespace App\Models;

use App\Models\ExportPermit;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomsDeclaration extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'declaration_number',
        'shipment_id',
        'export_permit_id',
        'hs_code',
        'country_of_origin',
        'destination_country',
        'declared_value',
        'currency',
        'total_weight',
        'package_count',
        'package_type',
        'goods_description',
        'declaration_date',
        'status',
        'customs_office',
        'rejection_reason',
        'cleared_at',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'declared_value' => 'decimal:2',
            'total_weight' => 'decimal:2',
            'declaration_date' => 'date',
            'cleared_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exportPermit(): BelongsTo
    {
        return $this->belongsTo(ExportPermit::class);
    }
}
