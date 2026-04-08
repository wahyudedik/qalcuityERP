<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'fingerprint_uid',
        'fingerprint_registered',
        'name',
        'email',
        'phone',
        'position',
        'department',
        'join_date',
        'resign_date',
        'status',
        'salary',
        'bank_name',
        'bank_account',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'resign_date' => 'date',
            'salary' => 'decimal:2',
            'fingerprint_registered' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
    public function fingerprintLogs(): HasMany
    {
        return $this->hasMany(FingerprintAttendanceLog::class);
    }
    public function reports(): HasMany
    {
        return $this->hasMany(EmployeeReport::class);
    }
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }
    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    /** Sisa cuti tahunan tahun ini */
    public function remainingAnnualLeave(int $quota = 12): int
    {
        $used = $this->leaveRequests()
            ->where('type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('days');
        return max(0, $quota - $used);
    }
}
