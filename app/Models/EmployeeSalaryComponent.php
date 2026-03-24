<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryComponent extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'salary_component_id',
        'amount', 'is_active', 'effective_from', 'effective_to',
    ];

    protected $casts = [
        'amount'         => 'float',
        'is_active'      => 'boolean',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
