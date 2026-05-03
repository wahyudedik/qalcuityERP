<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeleconsultationFeedback extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'rating',
        'video_quality',
        'audio_quality',
        'doctor_rating',
        'platform_rating',
        'feedback',
        'positive_feedback',
        'negative_feedback',
        'suggestions',
        'feedback_tags',
        'is_anonymous',
        'is_public',
        'is_responded',
        'doctor_response',
        'responded_at',
        'would_recommend',
        'would_use_again',
        'needs_followup',
        'followup_notes',
    ];

    protected $casts = [
        'consultation_id' => 'integer',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'rating' => 'integer',
        'video_quality' => 'integer',
        'audio_quality' => 'integer',
        'doctor_rating' => 'integer',
        'platform_rating' => 'integer',
        'feedback_tags' => 'array',
        'is_anonymous' => 'boolean',
        'is_public' => 'boolean',
        'is_responded' => 'boolean',
        'responded_at' => 'datetime',
        'would_recommend' => 'boolean',
        'would_use_again' => 'boolean',
        'needs_followup' => 'boolean',
    ];

    /**
     * Get the consultation that owns the feedback.
     */
    public function consultation()
    {
        return $this->belongsTo(Teleconsultation::class, 'consultation_id');
    }

    /**
     * Get the patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get rating as stars.
     */
    public function getRatingStars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Check if rating is positive.
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Check if rating is negative.
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }
}
