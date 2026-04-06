<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EliminationEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'consolidated_report_id',
        'entry_type',
        'from_tenant_id',
        'to_tenant_id',
        'amount',
        'description',
        'original_transactions',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_transactions' => 'array',
    ];

    public function consolidatedReport()
    {
        return $this->belongsTo(ConsolidatedReport::class);
    }
    public function fromTenant()
    {
        return $this->belongsTo(Tenant::class, 'from_tenant_id');
    }
    public function toTenant()
    {
        return $this->belongsTo(Tenant::class, 'to_tenant_id');
    }
}
