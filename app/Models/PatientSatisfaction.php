<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSatisfaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_visit_id',
        'patient_id',
        'doctor_id',
        'overall_rating',
        'doctor_rating',
        'nurse_rating',
        'facility_rating',
        'cleanliness_rating',
        'comments',
        'would_recommend',
        'submitted_at',
    ];

    protected $casts = [
        'would_recommend' => 'boolean',
        'submitted_at' => 'datetime',
        'overall_rating' => 'integer',
        'doctor_rating' => 'integer',
        'nurse_rating' => 'integer',
        'facility_rating' => 'integer',
        'cleanliness_rating' => 'integer',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
