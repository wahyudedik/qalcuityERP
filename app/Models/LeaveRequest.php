<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'employee_id', 'type', 'start_date', 'end_date',
        'days', 'reason', 'status', 'approved_by', 'rejection_reason', 'approved_at',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function approver(): BelongsTo { return $this->belongsTo(Employee::class, 'approved_by'); }

    public function typeLabel(): string
    {
        return match($this->type) {
            'annual'    => 'Cuti Tahunan',
            'sick'      => 'Sakit',
            'maternity' => 'Cuti Melahirkan',
            'paternity' => 'Cuti Ayah',
            'unpaid'    => 'Cuti Tanpa Gaji',
            default     => 'Lainnya',
        };
    }
}
