<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admission_number',
        'patient_id',
        'admitting_doctor_id',
        'ward_id',
        'bed_id',
        'referred_by_visit_id',
        'admission_type',
        'admission_category',
        'admission_date',
        'expected_discharge_date',
        'actual_discharge_date',
        'discharge_diagnosis',
        'discharge_summary',
        'discharge_status',
        'discharge_type',
        'admission_diagnosis',
        'icd10_code',
        'chief_complaint',
        'admission_notes',
        'treatment_plan',
        'special_instructions',
        'status',
        'requires_care_plan',
        'requires_surgery',
        'is_isolation',
        'estimated_cost',
        'actual_cost',
        'deposit_amount',
        'guarantor_name',
        'guarantor_phone',
        'guarantor_relationship',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'expected_discharge_date' => 'datetime',
        'actual_discharge_date' => 'datetime',
        'requires_care_plan' => 'boolean',
        'requires_surgery' => 'boolean',
        'is_isolation' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($admission) {
            if (empty($admission->admission_number)) {
                $admission->admission_number = static::generateAdmissionNumber();
            }
        });
    }

    /**
     * Generate unique admission number
     * Format: ADM-YYYYMMDD-XXXX
     */
    public static function generateAdmissionNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'ADM-' . $date;

        $lastAdmission = static::where('admission_number', 'like', $prefix . '%')
            ->orderBy('admission_number', 'desc')
            ->first();

        if ($lastAdmission) {
            $lastNumber = (int) substr($lastAdmission->admission_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get length of stay in days
     */
    public function getLengthOfStayAttribute()
    {
        $endDate = $this->actual_discharge_date ?? now();
        return $this->admission_date->diffInDays($endDate);
    }

    /**
     * Get admission type label
     */
    public function getAdmissionTypeLabelAttribute()
    {
        $labels = [
            'emergency' => 'Emergency',
            'elective' => 'Elective',
            'referral' => 'Referral',
            'maternity' => 'Maternity',
            'surgery' => 'Surgery',
            'observation' => 'Observation',
        ];

        return $labels[$this->admission_type] ?? $this->admission_type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'active' => 'Active',
            'discharged' => 'Discharged',
            'transferred' => 'Transferred',
            'ama' => 'Against Medical Advice',
            'deceased' => 'Deceased',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: Active admissions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: By admission type
     */
    public function scopeType($query, $type)
    {
        return $query->where('admission_type', $type);
    }

    /**
     * Scope: Admitted today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('admission_date', today());
    }

    /**
     * Scope: Requires surgery
     */
    public function scopeRequiresSurgery($query)
    {
        return $query->where('requires_surgery', true);
    }

    /**
     * Scope: Isolation patients
     */
    public function scopeIsolation($query)
    {
        return $query->where('is_isolation', true);
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Admitting doctor
     */
    public function admittingDoctor()
    {
        return $this->belongsTo(Doctor::class, 'admitting_doctor_id');
    }

    /**
     * Relation: Ward
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    /**
     * Relation: Bed
     */
    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    /**
     * Discharge patient
     */
    public function discharge(array $dischargeData)
    {
        return DB::transaction(function () use ($dischargeData) {
            $this->update([
                'status' => 'discharged',
                'actual_discharge_date' => now(),
                'discharge_diagnosis' => $dischargeData['discharge_diagnosis'] ?? null,
                'discharge_summary' => $dischargeData['discharge_summary'] ?? null,
                'discharge_status' => $dischargeData['discharge_status'] ?? 'recovered',
                'discharge_type' => $dischargeData['discharge_type'] ?? 'normal',
                'actual_cost' => $dischargeData['actual_cost'] ?? $this->actual_cost,
            ]);

            // Release bed
            if ($this->bed_id) {
                $bed = Bed::find($this->bed_id);
                if ($bed) {
                    $bed->markAsAvailable($dischargeData['cleaned_by'] ?? null);
                }
            }

            return $this;
        });
    }

    /**
     * Transfer to another ward/bed
     */
    public function transfer($newWardId, $newBedId)
    {
        return \DB::transaction(function () use ($newWardId, $newBedId) {
            // Release old bed
            if ($this->bed_id) {
                $oldBed = Bed::find($this->bed_id);
                if ($oldBed) {
                    $oldBed->markAsAvailable();
                }
            }

            // Update admission
            $this->update([
                'ward_id' => $newWardId,
                'bed_id' => $newBedId,
                'status' => 'transferred',
            ]);

            // Occupy new bed
            $newBed = Bed::find($newBedId);
            if ($newBed) {
                $newBed->markAsOccupied($this->patient_id, $this->id);
            }

            return $this;
        });
    }

    /**
     * Get admission summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'admission_number' => $this->admission_number,
            'patient_name' => $this->patient?->full_name,
            'admission_type' => $this->admission_type_label,
            'status' => $this->status_label,
            'ward' => $this->ward?->ward_name,
            'bed' => $this->bed?->bed_number,
            'admission_date' => $this->admission_date,
            'length_of_stay' => $this->length_of_stay . ' days',
        ];
    }
}
