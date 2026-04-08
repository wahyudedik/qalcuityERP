<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'name', 'code', 'type', 'calc_type',
        'default_amount', 'taxable', 'is_active', 'description',
    ];

    protected $casts = [
        'default_amount' => 'float',
        'taxable'        => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function employeeComponents()
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }
}
