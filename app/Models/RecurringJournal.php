<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringJournal extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'name', 'description',
        'frequency', 'start_date', 'end_date',
        'next_run_date', 'last_run_date', 'is_active', 'lines',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'is_active'     => 'boolean',
        'lines'         => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Hitung next_run_date berdasarkan frequency */
    public function calculateNextRun(): \Carbon\Carbon
    {
        $base = $this->last_run_date ?? $this->start_date;
        return match($this->frequency) {
            'daily'     => $base->addDay(),
            'weekly'    => $base->addWeek(),
            'monthly'   => $base->addMonth(),
            'quarterly' => $base->addMonths(3),
            'yearly'    => $base->addYear(),
            default     => $base->addMonth(),
        };
    }
}
