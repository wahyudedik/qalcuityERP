<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingRecommendation extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'room_type_id',
        'recommendation_date',
        'current_rate',
        'recommended_rate',
        'suggested_change_percentage',
        'reasoning',
        'supporting_data',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_date' => 'date',
            'current_rate' => 'decimal:2',
            'recommended_rate' => 'decimal:2',
            'suggested_change_percentage' => 'decimal:2',
            'supporting_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Apply the recommendation
     */
    public function apply(?int $userId = null): void
    {
        $this->update([
            'status' => 'applied',
            'reviewed_by' => $userId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Here you would update the actual rate plan
        ActivityLog::record(
            'pricing_recommendation_applied',
            "Applied pricing recommendation: {$this->current_rate} → {$this->recommended_rate}",
            $this->roomType,
            ['recommendation_id' => $this->id]
        );
    }

    /**
     * Reject the recommendation
     */
    public function reject(?int $userId = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $userId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Get pending recommendations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get change direction
     */
    public function getChangeDirectionAttribute(): string
    {
        if ($this->suggested_change_percentage > 0) {
            return 'increase';
        } elseif ($this->suggested_change_percentage < 0) {
            return 'decrease';
        }
        return 'no_change';
    }
}
