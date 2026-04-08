<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'employee_id', 'date', 'start_time', 'end_time',
        'duration_minutes', 'reason', 'status', 'approved_by', 'approved_at',
        'rejection_reason', 'overtime_pay', 'included_in_payroll', 'payroll_period',
    ];

    protected function casts(): array
    {
        return [
            'date'                => 'date',
            'approved_at'         => 'datetime',
            'overtime_pay'        => 'float',
            'included_in_payroll' => 'boolean',
        ];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function approver(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }

    /** Hitung upah lembur berdasarkan gaji pokok karyawan (Permenaker No.102/2004) */
    public function calculatePay(): float
    {
        $hourlyRate = (float) $this->employee->salary / 173; // 173 jam/bulan standar
        $hours      = $this->duration_minutes / 60;

        // Jam pertama: 1.5x, jam berikutnya: 2x
        if ($hours <= 1) {
            return round($hourlyRate * 1.5 * $hours, 2);
        }
        return round(($hourlyRate * 1.5) + ($hourlyRate * 2 * ($hours - 1)), 2);
    }

    public function durationLabel(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}j " . ($m > 0 ? "{$m}m" : '') : "{$m}m";
    }
}
