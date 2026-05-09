<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeferredItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'type', 'number', 'description',
        'total_amount', 'recognized_amount', 'remaining_amount',
        'start_date', 'end_date', 'total_periods', 'recognized_periods',
        'status', 'deferred_account_id', 'recognition_account_id',
        'reference_type', 'reference_id', 'reference_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
        'recognized_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'total_periods' => 'integer',
        'recognized_periods' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deferredAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'deferred_account_id');
    }

    public function recognitionAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'recognition_account_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DeferredItemSchedule::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'deferred_revenue' => 'Pendapatan Diterima di Muka',
            'prepaid_expense' => 'Biaya Dibayar di Muka',
            default => $this->type,
        };
    }

    public function progressPercent(): float
    {
        if ($this->total_periods === 0) {
            return 0;
        }

        return round(($this->recognized_periods / $this->total_periods) * 100, 1);
    }
}
