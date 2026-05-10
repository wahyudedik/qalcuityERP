<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RadiologyOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'exam_id',
        'ordered_by',
        'radiologist_id',
        'technologist_id',
        'order_number',
        'order_date',
        'scheduled_date',
        'started_at',
        'completed_at',
        'reported_at',
        'clinical_indication',
        'clinical_history',
        'icd10_code',
        'priority',
        'status',
        'contrast_required',
        'contrast_type',
        'contrast_volume',
        'contrast_notes',
        'is_authorized',
        'authorized_by',
        'authorized_at',
        'authorization_number',
        'room_number',
        'equipment_id',
        'special_instructions',
        'notes',
        'reported_by',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reported_at' => 'datetime',
        'authorized_at' => 'datetime',
        'contrast_required' => 'boolean',
        'contrast_volume' => 'decimal:2',
        'is_authorized' => 'boolean',
    ];

    const STATUS_ORDERED = 'ordered';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REPORTED = 'reported';
    const STATUS_CANCELLED = 'cancelled';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(RadiologyExam::class, 'exam_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'ordered_by');
    }

    public function radiologist(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'radiologist_id');
    }

    public function technologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technologist_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RadiologyImage::class, 'radiology_exam_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(RadiologyResult::class, 'order_id');
    }
}
