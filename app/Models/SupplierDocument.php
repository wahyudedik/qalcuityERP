<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDocument extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'mime_type',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'certificate_number',
        'is_verified',
        'verified_at',
        'verified_by',
        'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date)
            return false;
        return $this->expiry_date->diffInDays(now()) <= $days && $this->expiry_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}