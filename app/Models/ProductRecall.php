<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRecall extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'recall_number',
        'product_id',
        'batch_ids',
        'recall_type',
        'severity',
        'reason',
        'description',
        'affected_units',
        'action_required',
        'contact_person',
        'contact_email',
        'contact_phone',
        'start_date',
        'end_date',
        'completion_date',
        'status',
        'resolution_notes',
        'initiated_by',
    ];

    protected $casts = [
        'batch_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'completion_date' => 'date',
        'affected_units' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'product_id');
    }

    public function batches(): \Illuminate\Database\Eloquent\Collection
    {
        return CosmeticBatchRecord::whereIn('id', $this->batch_ids ?? [])->get();
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function isVoluntary(): bool
    {
        return $this->recall_type === 'voluntary';
    }

    public function isMandatory(): bool
    {
        return $this->recall_type === 'mandatory';
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['initiated', 'in_progress']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'in_progress']);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}
