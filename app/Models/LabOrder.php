<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabOrder extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'patient_id',
        'lab_test_id',
        'doctor_id',
        'patient_visit_id',
        'medical_record_id',
        'priority',
        'status',
        'clinical_notes',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_SAMPLE_COLLECTED = 'sample_collected';

    const STATUS_IN_ANALYSIS = 'in_analysis';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_ROUTINE = 'routine';

    const PRIORITY_URGENT = 'urgent';

    const PRIORITY_STAT = 'stat';

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->status)) {
                $order->status = self::STATUS_PENDING;
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'LAB-' . now()->format('Ymd');
        $last = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTestCatalog::class, 'lab_test_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LabSample::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class, 'lab_order_id');
    }
}
