<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * TransactionRevision — Task 36
 *
 * Menyimpan snapshot sebelum dan sesudah revisi transaksi yang sudah posted.
 * Transaksi yang sudah posted tidak bisa diedit langsung — harus buat revisi.
 */
class TransactionRevision extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'model_type', 'model_id', 'revision',
        'reason', 'snapshot_before', 'snapshot_after',
        'created_by', 'finalized_at',
    ];

    protected $casts = [
        'snapshot_before' => 'array',
        'snapshot_after' => 'array',
        'finalized_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Polymorphic ke model yang direvisi */
    public function subject(): MorphTo
    {
        return $this->morphTo('model');
    }

    /** Apakah revisi sudah selesai */
    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }
}
