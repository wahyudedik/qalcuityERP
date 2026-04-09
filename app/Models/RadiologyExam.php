<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiologyExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_code',
        'exam_name',
        'modality',
        'body_part',
        'body_region',
        'description',
        'price',
        'cost',
        'duration_minutes',
        'preparation_time',
        'requires_contrast',
        'contrast_type',
        'requires_fasting',
        'preparation_instructions',
        'contraindications',
        'protocols',
        'technical_notes',
        'is_active',
        'requires_authorization',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'duration_minutes' => 'integer',
        'preparation_time' => 'integer',
        'requires_contrast' => 'boolean',
        'requires_fasting' => 'boolean',
        'protocols' => 'array',
        'is_active' => 'boolean',
        'requires_authorization' => 'boolean',
    ];

    /**
     * Scope: Active exams only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By modality
     */
    public function scopeModality($query, $modality)
    {
        return $query->where('modality', $modality);
    }

    /**
     * Scope: By body part
     */
    public function scopeBodyPart($query, $bodyPart)
    {
        return $query->where('body_part', $bodyPart);
    }

    /**
     * Scope: Search by code or name
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('exam_code', 'like', "%{$searchTerm}%")
                ->orWhere('exam_name', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope: Requires contrast
     */
    public function scopeRequiresContrast($query)
    {
        return $query->where('requires_contrast', true);
    }

    /**
     * Get full exam name
     */
    public function getFullExamNameAttribute()
    {
        return "{$this->exam_code} - {$this->exam_name}";
    }

    /**
     * Get total duration (exam + preparation)
     */
    public function getTotalDurationAttribute()
    {
        return $this->duration_minutes + $this->preparation_time;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->price > 0) {
            return (($this->price - $this->cost) / $this->price) * 100;
        }
        return 0;
    }
}
