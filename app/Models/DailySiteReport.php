<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daily Site Report untuk Konstruksi
 */
class DailySiteReport extends Model
{
    protected $fillable = [
        'tenant_id',
        'project_id',
        'report_date',
        'reported_by',
        'weather_condition', // sunny, rainy, cloudy, windy
        'temperature',
        'work_performed',
        'manpower_count',
        'equipment_used',
        'materials_received',
        'issues_encountered',
        'safety_incidents',
        'progress_percentage',
        'photos', // JSON array of photo paths
        'status', // draft, submitted, approved
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'temperature' => 'decimal:1',
            'manpower_count' => 'integer',
            'progress_percentage' => 'decimal:2',
            'photos' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function laborLogs(): HasMany
    {
        return $this->hasMany(SiteLaborLog::class);
    }

    /**
     * Get photo URLs
     */
    public function getPhotoUrls(): array
    {
        if (empty($this->photos)) {
            return [];
        }

        return array_map(fn($photo) => storage_path("app/public/{$photo}"), $this->photos);
    }

    /**
     * Check if report is complete
     */
    public function isComplete(): bool
    {
        return !empty($this->work_performed)
            && $this->manpower_count > 0
            && !empty($this->progress_percentage);
    }
}
