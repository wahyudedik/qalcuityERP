<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCertificate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'certificate_number',
        'patient_id',
        'doctor_id',
        'certificate_type',
        'diagnosis',
        'description',
        'issue_date',
        'valid_from',
        'valid_until',
        'status',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
