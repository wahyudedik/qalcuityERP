<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'payroll_run_id', 'employee_id', 'base_salary',
        'working_days', 'present_days', 'absent_days', 'late_days',
        'allowances', 'overtime_pay', 'deduction_absent', 'deduction_late',
        'deduction_other', 'gross_salary', 'tax_pph21', 'bpjs_employee',
        'net_salary', 'status', 'notes',
    ];

    protected $casts = ['base_salary' => 'float', 'gross_salary' => 'float', 'net_salary' => 'float', 'tax_pph21' => 'float', 'bpjs_employee' => 'float'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function components()
    {
        return $this->hasMany(PayrollItemComponent::class);
    }
}
