<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'shift_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'work_minutes',
        'overtime_minutes',
        'late_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'work_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'late_minutes' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }

    /** Label status kehadiran dalam Bahasa Indonesia */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'leave' => 'Cuti',
            'sick' => 'Sakit',
            default => ucfirst($this->status),
        };
    }
}
