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
        'shift_id', // BUG-HRM-002: Link to work shift
        'date',
        'check_in',
        'check_out',
        'status',
        'work_minutes',
        'overtime_minutes', // BUG-HRM-002: Track overtime
        'notes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
