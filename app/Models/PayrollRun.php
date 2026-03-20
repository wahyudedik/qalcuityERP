<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'tenant_id', 'period', 'status', 'total_gross',
        'total_deductions', 'total_net', 'processed_by', 'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime', 'total_gross' => 'float', 'total_deductions' => 'float', 'total_net' => 'float'];

    public function items() { return $this->hasMany(PayrollItem::class, 'payroll_run_id'); }
}
