<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'reviewer_id', 'period', 'period_type',
        'score_work_quality', 'score_productivity', 'score_teamwork',
        'score_initiative', 'score_attendance', 'overall_score',
        'strengths', 'improvements', 'goals_next_period',
        'recommendation', 'status', 'submitted_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'submitted_at'  => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(Employee::class, 'reviewer_id'); }

    public function computeOverall(): float
    {
        return round((
            $this->score_work_quality +
            $this->score_productivity +
            $this->score_teamwork +
            $this->score_initiative +
            $this->score_attendance
        ) / 5, 2);
    }

    public function overallLabel(): string
    {
        return match(true) {
            $this->overall_score >= 4.5 => 'Luar Biasa',
            $this->overall_score >= 3.5 => 'Baik',
            $this->overall_score >= 2.5 => 'Cukup',
            $this->overall_score >= 1.5 => 'Perlu Perbaikan',
            default                     => 'Tidak Memuaskan',
        };
    }

    public function recommendationLabel(): string
    {
        return match($this->recommendation) {
            'promote'   => 'Promosi',
            'retain'    => 'Pertahankan',
            'pip'       => 'PIP (Rencana Perbaikan)',
            'terminate' => 'Pertimbangkan PHK',
            default     => '-',
        };
    }
}
