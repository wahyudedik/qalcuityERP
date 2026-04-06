<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestDetection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'crop_cycle_id',
        'image_path',
        'pest_name',
        'disease_name',
        'confidence_score',
        'severity',
        'pest_detected',
        'disease_detected',
        'treatment_recommendations',
        'prevention_tips',
        'ai_analysis',
        'status',
        'treatment_date',
        'treatment_notes',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'pest_detected' => 'boolean',
        'disease_detected' => 'boolean',
        'treatment_recommendations' => 'array',
        'prevention_tips' => 'array',
        'treatment_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function cropCycle()
    {
        return $this->belongsTo(CropCycle::class);
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public function markAsTreated(): void
    {
        $this->update([
            'status' => 'treated',
            'treatment_date' => now(),
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update(['status' => 'resolved']);
    }
}
