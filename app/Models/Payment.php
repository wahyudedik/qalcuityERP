<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use AuditsChanges, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payable_type',
        'payable_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /** Polymorphic: bisa Invoice atau Payable */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
