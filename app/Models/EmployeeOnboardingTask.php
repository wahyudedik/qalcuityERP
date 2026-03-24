<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOnboardingTask extends Model
{
    protected $fillable = [
        'employee_onboarding_id', 'task', 'category', 'due_day',
        'required', 'is_done', 'done_at', 'done_by', 'notes', 'sort_order',
    ];

    protected $casts = ['done_at' => 'datetime', 'is_done' => 'boolean', 'required' => 'boolean'];

    public function onboarding(): BelongsTo { return $this->belongsTo(EmployeeOnboarding::class, 'employee_onboarding_id'); }
    public function doneByUser(): BelongsTo { return $this->belongsTo(User::class, 'done_by'); }
}
