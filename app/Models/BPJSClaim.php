<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BPJSClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_number',
        'patient_id',
        'patient_visit_id',
        'participant_number',
        'claim_type',
        'diagnosis_code',
        'diagnosis_description',
        'procedure_code',
        'procedure_description',
        'claim_amount',
        'approved_amount',
        'status',
        'submission_date',
        'adjudication_date',
        'payment_date',
        'rejection_reason',
        'notes',
        'bpjs_reference',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'adjudication_date' => 'date',
        'payment_date' => 'date',
        'claim_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }
}
