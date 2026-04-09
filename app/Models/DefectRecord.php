<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DefectRecord extends Model
{
    protected $fillable = [
        'tenant_id',
        'quality_check_id',
        'product_id',
        'work_order_id',
        'defect_code',
        'defect_type',
        'severity',
        'quantity_defected',
        'description',
        'root_cause',
        'corrective_action',
        'preventive_action',
        'disposition',
        'cost_impact',
        'reported_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'quantity_defected' => 'integer',
        'cost_impact' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($defect) {
            if (!$defect->defect_code) {
                $defect->defect_code = 'DEF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            if (!$defect->reported_by) {
                $defect->reported_by = Auth::id();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function qualityCheck()
    {
        return $this->belongsTo(QualityCheck::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function resolve(string $rootCause, string $correctiveAction, string $preventiveAction = null, string $resolvedBy = null)
    {
        $this->update([
            'root_cause' => $rootCause,
            'corrective_action' => $correctiveAction,
            'preventive_action' => $preventiveAction,
            'resolved_by' => $resolvedBy ?? Auth::id(),
            'resolved_at' => now(),
        ]);
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function isMajor(): bool
    {
        return $this->severity === 'major';
    }
}
