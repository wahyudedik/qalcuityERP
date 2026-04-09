<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'patient_id',
        'patient_visit_id',
        'department_id',
        'doctor_id',
        'status',
        'priority',
        'issued_at',
        'called_at',
        'served_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
