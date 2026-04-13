<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'lab_order_id',
        'test_id',
        'patient_id',
        'patient_visit_id',
        'equipment_id',
        'test_code',
        'test_name',
        'result_value',
        'result_unit',
        'reference_range_min',
        'reference_range_max',
        'is_critical',
        'is_verified',
        'verified_by',
        'verified_at',
        'result_data',
        'notes',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'result_data' => 'array',
        'reference_range_min' => 'decimal:2',
        'reference_range_max' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTestCatalog::class, 'test_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(LabEquipment::class, 'equipment_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
