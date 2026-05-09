<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboarding extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'employee_id', 'job_application_id',
        'status', 'start_date', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }

    public function progressPercent(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $done = $this->tasks()->where('is_done', true)->count();

        return (int) round($done / $total * 100);
    }

    public function requiredPendingCount(): int
    {
        return $this->tasks()->where('required', true)->where('is_done', false)->count();
    }
}
