<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QualityCheck extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'product_id',
        'standard_id',
        'inspector_id',
        'check_number',
        'stage',
        'sample_size',
        'sample_passed',
        'sample_failed',
        'status',
        'notes',
        'results',
        'corrective_action',
        'inspected_at',
    ];

    protected $casts = [
        'results' => 'array',
        'sample_size' => 'decimal:2',
        'sample_passed' => 'decimal:2',
        'sample_failed' => 'decimal:2',
        'inspected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($check) {
            if (!$check->check_number) {
                $check->check_number = 'QC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            if (!$check->inspector_id) {
                $check->inspector_id = Auth::id();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function standard()
    {
        return $this->belongsTo(QualityCheckStandard::class, 'standard_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function defects()
    {
        return $this->hasMany(DefectRecord::class);
    }

    public function pass()
    {
        $this->update([
            'status' => 'passed',
            'inspected_at' => now(),
        ]);

        // Update work order status if applicable
        if ($this->workOrder) {
            $this->workOrder->update([
                'quality_status' => 'passed',
                'quality_passed_at' => now(),
            ]);
        }
    }

    public function fail(string $correctiveAction = null)
    {
        $this->update([
            'status' => 'failed',
            'corrective_action' => $correctiveAction,
            'inspected_at' => now(),
        ]);

        // Update work order status
        if ($this->workOrder) {
            $this->workOrder->update([
                'quality_status' => 'failed',
                'quality_failed_at' => now(),
            ]);
        }
    }

    public function conditionalPass(string $notes = null)
    {
        $this->update([
            'status' => 'conditional_pass',
            'notes' => $notes,
            'inspected_at' => now(),
        ]);
    }

    public function getPassRateAttribute(): float
    {
        if ($this->sample_size == 0) {
            return 0;
        }
        return ($this->sample_passed / $this->sample_size) * 100;
    }

    public function getFailRateAttribute(): float
    {
        if ($this->sample_size == 0) {
            return 0;
        }
        return ($this->sample_failed / $this->sample_size) * 100;
    }
}
