<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'medical_record_number',
        'nik',
        'full_name',
        'short_name',
        'birth_date',
        'birth_place',
        'gender',
        'blood_type',
        'religion',
        'marital_status',
        'occupation',
        'nationality',
        'phone_primary',
        'phone_secondary',
        'email',
        'address_street',
        'address_rt',
        'address_rw',
        'address_kelurahan',
        'address_kecamatan',
        'address_city',
        'address_province',
        'address_postal_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_group_number',
        'insurance_valid_until',
        'insurance_class',
        'known_allergies',
        'chronic_diseases',
        'current_medications',
        'medical_notes',
        'status',
        'is_blacklisted',
        'blacklist_reason',
        'photo_path',
        'id_card_path',
        'insurance_card_path',
        'qr_code',
        'last_visit_date',
        'total_visits',
        'total_admissions',
        'registered_by',
        'primary_doctor_id',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'insurance_valid_until' => 'date',
        'last_visit_date' => 'datetime',
        'known_allergies' => 'array',
        'chronic_diseases' => 'array',
        'current_medications' => 'array',
        'is_blacklisted' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($patient) {
            // Auto-generate medical record number if not provided
            if (empty($patient->medical_record_number)) {
                $patient->medical_record_number = static::generateMedicalRecordNumber();
            }

            // Auto-generate QR code
            if (empty($patient->qr_code)) {
                $patient->qr_code = Str::uuid()->toString();
            }
        });
    }

    /**
     * Generate unique medical record number
     * Format: MR-YYYYMMDD-XXXX
     */
    public static function generateMedicalRecordNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'MR-' . $date;

        // Get the last number for today
        $lastPatient = static::where('medical_record_number', 'like', $prefix . '%')
            ->orderBy('medical_record_number', 'desc')
            ->first();

        if ($lastPatient) {
            $lastNumber = (int) substr($lastPatient->medical_record_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get patient's full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_street,
            'RT ' . $this->address_rt . '/RW ' . $this->address_rw,
            $this->address_kelurahan,
            $this->address_kecamatan,
            $this->address_city,
            $this->address_province,
            $this->address_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Calculate patient age
     */
    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * Get formatted blood type with emoji
     */
    public function getBloodTypeFormattedAttribute()
    {
        if (!$this->blood_type) {
            return 'Unknown';
        }

        $emojis = [
            'A' => '🅰️',
            'B' => '🅱️',
            'AB' => '🆎',
            'O' => '🅾️',
        ];

        $emoji = $emojis[$this->blood_type] ?? '';
        return $emoji . ' ' . $this->blood_type;
    }

    /**
     * Check if patient has allergies
     */
    public function hasAllergies()
    {
        return !empty($this->known_allergies) && count($this->known_allergies) > 0;
    }

    /**
     * Check if patient has chronic diseases
     */
    public function hasChronicDiseases()
    {
        return !empty($this->chronic_diseases) && count($this->chronic_diseases) > 0;
    }

    /**
     * Get patient's insurance status
     */
    public function getInsuranceStatusAttribute()
    {
        if (!$this->insurance_provider) {
            return 'uninsured';
        }

        if ($this->insurance_valid_until && $this->insurance_valid_until->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get patient category based on age
     */
    public function getPatientCategoryAttribute()
    {
        $age = $this->age;

        if ($age === null) {
            return 'unknown';
        } elseif ($age < 2) {
            return 'infant'; // Bayi
        } elseif ($age < 12) {
            return 'child'; // Anak-anak
        } elseif ($age < 18) {
            return 'adolescent'; // Remaja
        } elseif ($age < 60) {
            return 'adult'; // Dewasa
        } else {
            return 'elderly'; // Lansia
        }
    }

    /**
     * Scope: Active patients only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Search patients by name, MRN, or NIK
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('full_name', 'like', "%{$searchTerm}%")
                ->orWhere('medical_record_number', 'like', "%{$searchTerm}%")
                ->orWhere('nik', 'like', "%{$searchTerm}%")
                ->orWhere('phone_primary', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: Filter by blood type
     */
    public function scopeBloodType($query, $bloodType)
    {
        return $query->where('blood_type', $bloodType);
    }

    /**
     * Scope: Patients with allergies
     */
    public function scopeWithAllergies($query)
    {
        return $query->whereNotNull('known_allergies')
            ->whereJsonLength('known_allergies', '>', 0);
    }

    /**
     * Scope: Patients with chronic diseases
     */
    public function scopeWithChronicDiseases($query)
    {
        return $query->whereNotNull('chronic_diseases')
            ->whereJsonLength('chronic_diseases', '>', 0);
    }

    /**
     * Scope: Patients by age range
     */
    public function scopeAgeRange($query, $minAge, $maxAge)
    {
        $minDate = now()->subYears($maxAge)->format('Y-m-d');
        $maxDate = now()->subYears($minAge)->format('Y-m-d');

        return $query->whereBetween('birth_date', [$minDate, $maxDate]);
    }

    /**
     * Relation: Registered by user
     */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Relation: Primary doctor
     */
    public function primaryDoctor()
    {
        return $this->belongsTo(User::class, 'primary_doctor_id');
    }

    /**
     * Relation: Patient visits
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    /**
     * Relation: Patient appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relation: Medical records
     */
    public function medicalRecords()
    {
        return $this->hasMany(PatientMedicalRecord::class);
    }

    /**
     * Relation: Patient allergies (detailed)
     */
    public function allergyRecords()
    {
        return $this->hasMany(PatientAllergy::class);
    }

    /**
     * Relation: Patient insurance records
     */
    public function insuranceRecords()
    {
        return $this->hasMany(PatientInsurance::class);
    }

    /**
     * Relation: Prescriptions
     */
    public function prescriptions()
    {
        return $this->hasManyThrough(Prescription::class, PatientVisit::class);
    }

    /**
     * Relation: Lab orders
     */
    public function labOrders()
    {
        return $this->hasManyThrough(LabOrder::class, PatientVisit::class);
    }

    /**
     * Relation: Admissions (Inpatient)
     */
    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    /**
     * Relation: Medical bills
     */
    public function medicalBills()
    {
        return $this->hasMany(MedicalBill::class);
    }

    /**
     * Increment total visits
     */
    public function incrementVisits()
    {
        $this->increment('total_visits');
        $this->update(['last_visit_date' => now()]);
    }

    /**
     * Increment total admissions
     */
    public function incrementAdmissions()
    {
        $this->increment('total_admissions');
    }

    /**
     * Get patient summary for quick view
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'mrn' => $this->medical_record_number,
            'name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'blood_type' => $this->blood_type,
            'phone' => $this->phone_primary,
            'insurance' => $this->insurance_status,
            'allergies' => $this->hasAllergies(),
            'chronic_diseases' => $this->hasChronicDiseases(),
        ];
    }
}
