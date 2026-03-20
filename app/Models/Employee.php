<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'employee_id', 'name', 'email', 'phone',
        'position', 'department', 'join_date', 'resign_date', 'status',
        'salary', 'bank_name', 'bank_account', 'address',
    ];

    protected function casts(): array
    {
        return [
            'join_date'   => 'date',
            'resign_date' => 'date',
            'salary'      => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function reports(): HasMany { return $this->hasMany(EmployeeReport::class); }
}
