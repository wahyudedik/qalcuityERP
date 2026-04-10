<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportPermit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'permit_number',
        'permit_type',
        'destination_country',
        'destination_address',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'authorized_quantity',
        'authorized_species',
        'status',
        'conditions',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'authorized_quantity' => 'decimal:2',
            'authorized_species' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
