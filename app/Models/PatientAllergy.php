<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAllergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'allergen',
        'allergen_type',
        'severity',
        'reaction_description',
        'treatment_if_exposed',
        'diagnosed_date',
        'diagnosed_by',
        'diagnosis_method',
        'is_active',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'diagnosed_date' => 'date',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Get severity label with emoji
     */
    public function getSeverityLabelAttribute()
    {
        $labels = [
            'mild' => '🟢 Ringan',
            'moderate' => '🟡 Sedang',
            'severe' => '🟠 Parah',
            'life_threatening' => '🔴 Mengancam Jiwa',
        ];

        return $labels[$this->severity] ?? $this->severity;
    }

    /**
     * Get allergen type label
     */
    public function getAllergenTypeLabelAttribute()
    {
        $labels = [
            'medication' => 'Obat',
            'food' => 'Makanan',
            'environmental' => 'Lingkungan',
            'other' => 'Lainnya',
        ];

        return $labels[$this->allergen_type] ?? $this->allergen_type;
    }

    /**
     * Scope: Active allergies only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Severe or life-threatening allergies
     */
    public function scopeSevere($query)
    {
        return $query->whereIn('severity', ['severe', 'life_threatening']);
    }

    /**
     * Scope: Verified allergies only
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Search by allergen name
     */
    public function scopeSearchAllergen($query, $searchTerm)
    {
        return $query->where('allergen', 'like', "%{$searchTerm}%");
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Diagnosed by doctor
     */
    public function diagnosedBy()
    {
        return $this->belongsTo(User::class, 'diagnosed_by');
    }

    /**
     * Deactivate allergy
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Verify allergy
     */
    public function verify()
    {
        $this->update([
            'is_verified' => true,
            'diagnosed_date' => now(),
        ]);
    }
}
