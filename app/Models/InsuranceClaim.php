<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'claim_number',
        'patient_id',
        'patient_visit_id',
        'insurance_id',
        'policy_number',
        'claim_type',
        'claim_amount',
        'approved_amount',
        'status',
        'submission_date',
        'adjudication_date',
        'payment_date',
        'rejection_reason',
        'adjudication_notes',
        'documents',
        'notes',
    ];

    protected $casts = [
        'documents' => 'array',
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

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(PatientInsurance::class, 'insurance_id');
    }
}
