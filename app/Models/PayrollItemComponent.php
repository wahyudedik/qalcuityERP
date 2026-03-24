<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItemComponent extends Model
{
    protected $fillable = [
        'payroll_item_id', 'salary_component_id', 'name', 'type', 'amount',
    ];

    protected $casts = ['amount' => 'float'];

    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
