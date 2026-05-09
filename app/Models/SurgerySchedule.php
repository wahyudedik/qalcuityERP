<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurgerySchedule extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'surgeon_id',
        'operating_room_id',
        'admission_id',
        'surgery_number',
        'scheduled_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'estimated_duration',
        'actual_duration',
        'procedure_name',
        'procedure_code',
        'procedure_description',
        'surgery_type',
        'icd10_code',
        'pre_operative_diagnosis',
        'post_operative_diagnosis',
        'anesthesiologist_id',
        'anesthesia_type',
        'anesthesia_notes',
        'status',
        'priority',
        'preoperative_notes',
        'intraoperative_notes',
        'postoperative_notes',
        'complications',
        'surgeon_notes',
        'outcome',
        'blood_loss_ml',
        'implants_used',
        'cancellation_reason',
        'postponement_reason',
        'rescheduled_to',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'rescheduled_to' => 'datetime',
        'estimated_duration' => 'integer',
        'actual_duration' => 'integer',
        'blood_loss_ml' => 'integer',
        'implants_used' => 'array',
    ];

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Surgeon
     */
    public function surgeon()
    {
        return $this->belongsTo(Doctor::class, 'surgeon_id');
    }

    /**
     * Relation: Operating Room
     */
    public function operatingRoom()
    {
        return $this->belongsTo(OperatingRoom::class);
    }

    /**
     * Relation: Admission
     */
    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    /**
     * Relation: Anesthesiologist
     */
    public function anesthesiologist()
    {
        return $this->belongsTo(Doctor::class, 'anesthesiologist_id');
    }

    /**
     * Relation: Surgery Team
     */
    public function surgeryTeam()
    {
        return $this->hasMany(SurgeryTeam::class, 'surgery_id');
    }

    /**
     * Relation: Medical Equipment
     */
    public function equipment()
    {
        return $this->hasMany(MedicalEquipment::class, 'surgery_id');
    }
}
