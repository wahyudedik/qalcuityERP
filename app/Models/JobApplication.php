<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'tenant_id', 'job_posting_id', 'applicant_name', 'applicant_email',
        'applicant_phone', 'cover_letter', 'resume_path', 'stage', 'notes',
        'interview_date', 'interview_location', 'offered_salary',
        'expected_join_date', 'employee_id', 'reviewed_by',
    ];

    protected $casts = [
        'interview_date'     => 'date',
        'expected_join_date' => 'date',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function jobPosting(): BelongsTo { return $this->belongsTo(JobPosting::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function stageLabel(): string
    {
        return match($this->stage) {
            'applied'   => 'Lamaran Masuk',
            'screening' => 'Seleksi',
            'interview' => 'Interview',
            'offer'     => 'Penawaran',
            'hired'     => 'Diterima',
            'rejected'  => 'Ditolak',
            default     => $this->stage,
        };
    }

    public function stageBadgeClass(): string
    {
        return match($this->stage) {
            'applied'   => 'bg-blue-500/20 text-blue-400',
            'screening' => 'bg-yellow-500/20 text-yellow-400',
            'interview' => 'bg-purple-500/20 text-purple-400',
            'offer'     => 'bg-orange-500/20 text-orange-400',
            'hired'     => 'bg-green-500/20 text-green-400',
            'rejected'  => 'bg-red-500/20 text-red-400',
            default     => 'bg-gray-500/20 text-gray-400',
        };
    }
}
