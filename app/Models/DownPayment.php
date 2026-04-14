<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownPayment extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'number', 'type', 'party_id', 'party_type',
        'reference_id', 'reference_type', 'payment_date',
        'amount', 'applied_amount', 'remaining_amount', 'status',
        'payment_method', 'created_by', 'notes',
    ];

    protected $casts = [
        'payment_date'   => 'date',
        'amount'         => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo    { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function party(): MorphTo       { return $this->morphTo('party'); }
    public function applications(): HasMany { return $this->hasMany(DownPaymentApplication::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'partial'  => 'Sebagian Dipakai',
            'applied'  => 'Sudah Dipakai',
            'refunded' => 'Dikembalikan',
            default    => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
            'partial'  => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'applied'  => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'refunded' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    /** Recalculate applied_amount dan status */
    public function recalculate(): void
    {
        $applied = $this->applications()->sum('amount');
        $this->applied_amount   = $applied;
        $this->remaining_amount = $this->amount - $applied;
        $this->status = match (true) {
            $applied <= 0              => 'pending',
            $applied >= $this->amount  => 'applied',
            default                    => 'partial',
        };
        $this->save();
    }
}