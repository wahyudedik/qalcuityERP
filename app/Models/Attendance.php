<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['tenant_id', 'employee_id', 'date', 'check_in', 'check_out', 'status', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
