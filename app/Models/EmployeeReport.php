<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReport extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'employee_id', 'title', 'type',
        'period_start', 'period_end', 'content', 'status', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end'   => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
