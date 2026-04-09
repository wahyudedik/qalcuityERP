<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSatisfactionSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'admission_id',
        'doctor_id',
        'department_id',
        'survey_number',
        'submitted_date',
        'survey_type',
        'overall_rating',
        'would_recommend',
        'admission_rating',
        'doctor_rating',
        'nurse_rating',
        'facility_rating',
        'food_rating',
        'cleanliness_rating',
        'wait_time_rating',
        'nps_score',
        'positive_feedback',
        'negative_feedback',
        'suggestions',
        'complaints',
        'feedback_categories',
        'is_anonymous',
        'is_resolved',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'submitted_date' => 'datetime',
        'overall_rating' => 'integer',
        'would_recommend' => 'integer',
        'admission_rating' => 'integer',
        'doctor_rating' => 'integer',
        'nurse_rating' => 'integer',
        'facility_rating' => 'integer',
        'food_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'wait_time_rating' => 'integer',
        'nps_score' => 'integer',
        'feedback_categories' => 'array',
        'is_anonymous' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Scope: By rating
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('overall_rating', $rating);
    }

    /**
     * Scope: High ratings (4-5)
     */
    public function scopeHighRating($query)
    {
        return $query->whereIn('overall_rating', [4, 5]);
    }

    /**
     * Scope: Low ratings (1-2)
     */
    public function scopeLowRating($query)
    {
        return $query->whereIn('overall_rating', [1, 2]);
    }

    /**
     * Scope: By survey type
     */
    public function scopeSurveyType($query, $type)
    {
        return $query->where('survey_type', $type);
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('submitted_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Unresolved surveys
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope: Search
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('survey_number', 'like', "%{$searchTerm}%")
                ->orWhere('positive_feedback', 'like', "%{$searchTerm}%")
                ->orWhere('negative_feedback', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Get rating label
     */
    public function getRatingLabelAttribute()
    {
        return match ($this->overall_rating) {
            5 => 'Excellent',
            4 => 'Good',
            3 => 'Average',
            2 => 'Poor',
            1 => 'Very Poor',
        };
    }

    /**
     * Get NPS category
     */
    public function getNpsCategoryAttribute()
    {
        if (!$this->nps_score) {
            return null;
        }

        return match (true) {
            $this->nps_score >= 9 => 'promoter',
            $this->nps_score >= 7 => 'passive',
            default => 'detractor',
        };
    }

    /**
     * Check if survey has complaints
     */
    public function hasComplaints()
    {
        return !empty($this->complaints) || !empty($this->negative_feedback);
    }

    /**
     * Get average rating for period
     */
    public static function getAverageRating($startDate, $endDate)
    {
        return static::whereBetween('submitted_date', [$startDate, $endDate])
            ->avg('overall_rating');
    }

    /**
     * Get NPS score for period
     */
    public static function calculateNPS($startDate, $endDate)
    {
        $total = static::whereBetween('submitted_date', [$startDate, $endDate])
            ->whereNotNull('nps_score')
            ->count();

        if ($total === 0) {
            return 0;
        }

        $promoters = static::whereBetween('submitted_date', [$startDate, $endDate])
            ->where('nps_score', '>=', 9)
            ->count();

        $detractors = static::whereBetween('submitted_date', [$startDate, $endDate])
            ->where('nps_score', '<=', 6)
            ->count();

        return round((($promoters - $detractors) / $total) * 100, 2);
    }
}
