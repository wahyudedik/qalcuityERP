<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'doctor_number',
        'license_number',
        'sip_number',
        'specialization',
        'sub_specialization',
        'practice_locations',
        'practice_days',
        'practice_start_time',
        'practice_end_time',
        'consultation_fee',
        'follow_up_fee',
        'home_visit_fee',
        'telemedicine_fee',
        'medical_school',
        'graduation_year',
        'certifications',
        'education_history',
        'professional_memberships',
        'years_of_experience',
        'biography',
        'languages_spoken',
        'status',
        'accepting_patients',
        'available_for_telemedicine',
        'available_for_home_visit',
        'available_for_emergency',
        'total_consultations',
        'total_patients',
        'average_rating',
        'total_reviews',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'photo_path',
        'license_document_path',
        'sip_document_path',
        'notes',
    ];

    protected $casts = [
        'practice_locations' => 'array',
        'practice_days' => 'array',
        'certifications' => 'array',
        'education_history' => 'array',
        'professional_memberships' => 'array',
        'languages_spoken' => 'array',
        'practice_start_time' => 'datetime:H:i',
        'practice_end_time' => 'datetime:H:i',
        'consultation_fee' => 'decimal:2',
        'follow_up_fee' => 'decimal:2',
        'home_visit_fee' => 'decimal:2',
        'telemedicine_fee' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'accepting_patients' => 'boolean',
        'available_for_telemedicine' => 'boolean',
        'available_for_home_visit' => 'boolean',
        'available_for_emergency' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($doctor) {
            if (empty($doctor->doctor_number)) {
                $doctor->doctor_number = static::generateDoctorNumber();
            }
        });
    }

    /**
     * Generate unique doctor number
     * Format: DR-XXXXX
     */
    public static function generateDoctorNumber()
    {
        $lastDoctor = static::orderBy('id', 'desc')->first();
        $number = $lastDoctor ? (int) substr($lastDoctor->doctor_number, 3) + 1 : 1;
        return 'DR-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get doctor's full name from user relationship
     */
    public function getFullNameAttribute()
    {
        return $this->user?->name ?? 'Unknown';
    }

    /**
     * Get doctor's email from user relationship
     */
    public function getEmailAttribute()
    {
        return $this->user?->email;
    }

    /**
     * Get doctor's phone from user relationship
     */
    public function getPhoneAttribute()
    {
        return $this->user?->phone;
    }

    /**
     * Check if doctor is currently available
     */
    public function isCurrentlyAvailable()
    {
        if ($this->status !== 'active' || !$this->accepting_patients) {
            return false;
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l'));

        // Check if today is a practice day
        if ($this->practice_days && !in_array($dayOfWeek, $this->practice_days)) {
            return false;
        }

        // Check if within practice hours
        if ($this->practice_start_time && $this->practice_end_time) {
            $currentTime = $now->format('H:i:s');
            return $currentTime >= $this->practice_start_time &&
                $currentTime <= $this->practice_end_time;
        }

        return true;
    }

    /**
     * Get practice schedule for display
     */
    public function getPracticeScheduleAttribute()
    {
        if (!$this->practice_days) {
            return 'Not set';
        }

        $days = collect($this->practice_days)->map(function ($day) {
            return ucfirst(substr($day, 0, 3));
        })->join(', ');

        $time = '';
        if ($this->practice_start_time && $this->practice_end_time) {
            $startTime = \Carbon\Carbon::parse($this->practice_start_time)->format('H:i');
            $endTime = \Carbon\Carbon::parse($this->practice_end_time)->format('H:i');
            $time = " ($startTime - $endTime)";
        }

        return $days . $time;
    }

    /**
     * Get specialization label with icon
     */
    public function getSpecializationLabelAttribute()
    {
        $icons = [
            'General Practice' => '👨‍⚕️',
            'Cardiology' => '❤️',
            'Dermatology' => '🩺',
            'Pediatrics' => '👶',
            'Orthopedics' => '🦴',
            'Neurology' => '🧠',
            'Ophthalmology' => '👁️',
            'ENT' => '👂',
            'Obstetrics' => '🤰',
            'Dentistry' => '🦷',
        ];

        $icon = $icons[$this->specialization] ?? '⚕️';
        return $icon . ' ' . $this->specialization;
    }

    /**
     * Scope: Active doctors only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Currently accepting patients
     */
    public function scopeAcceptingPatients($query)
    {
        return $query->where('accepting_patients', true);
    }

    /**
     * Scope: By specialization
     */
    public function scopeSpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    /**
     * Scope: Available for telemedicine
     */
    public function scopeTelemedicine($query)
    {
        return $query->where('available_for_telemedicine', true);
    }

    /**
     * Scope: Available for home visit
     */
    public function scopeHomeVisit($query)
    {
        return $query->where('available_for_home_visit', true);
    }

    /**
     * Scope: Search doctors
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('specialization', 'like', "%{$searchTerm}%")
                ->orWhere('sub_specialization', 'like', "%{$searchTerm}%")
                ->orWhere('license_number', 'like', "%{$searchTerm}%")
                ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%");
                });
        });
    }

    /**
     * Relation: User account
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relation: Patient visits
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class, 'doctor_id');
    }

    /**
     * Relation: Medical records
     */
    public function medicalRecords()
    {
        return $this->hasMany(PatientMedicalRecord::class, 'doctor_id');
    }

    /**
     * Relation: Schedules
     */
    public function schedules()
    {
        return $this->hasMany(MedicalStaffSchedule::class, 'doctor_id');
    }

    /**
     * Increment consultation count
     */
    public function incrementConsultations()
    {
        $this->increment('total_consultations');
    }

    /**
     * Update average rating
     */
    public function updateRating($newRating)
    {
        $this->increment('total_reviews');
        $this->refresh();

        $newAverage = ((($this->average_rating * ($this->total_reviews - 1)) + $newRating) / $this->total_reviews);
        $this->update(['average_rating' => round($newAverage, 2)]);
    }

    /**
     * Get doctor summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'doctor_number' => $this->doctor_number,
            'name' => $this->full_name,
            'specialization' => $this->specialization_label,
            'status' => $this->status,
            'accepting_patients' => $this->accepting_patients,
            'consultation_fee' => $this->consultation_fee,
            'rating' => $this->average_rating,
            'total_consultations' => $this->total_consultations,
        ];
    }
}
