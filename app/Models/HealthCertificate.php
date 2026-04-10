<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCertificate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'certificate_number',
        'product_batch_id',
        'catch_log_id',
        'certificate_type',
        'inspection_date',
        'issue_date',
        'expiry_date',
        'issued_by',
        'issuing_authority',
        'inspection_results',
        'certifications',
        'status',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function catchLog(): BelongsTo
    {
        return $this->belongsTo(CatchLog::class);
    }
}
