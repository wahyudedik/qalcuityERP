<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Mix Design Version Tracking
 *
 * Tracks all changes to concrete mix designs for compliance & audit
 */
class MixDesignVersion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mix_design_id',
        'version_number',
        'grade',
        'name',
        'target_strength',
        'strength_unit',
        'slump_min',
        'slump_max',
        'water_cement_ratio',
        'cement_kg',
        'water_liter',
        'fine_agg_kg',
        'coarse_agg_kg',
        'admixture_liter',
        'cement_type',
        'agg_max_size',
        'is_active',
        'notes',
        'change_reason',
        'changed_by',
        'approved_by',
        'approved_at',
        'snapshot_data',
    ];

    protected function casts(): array
    {
        return [
            'target_strength' => 'decimal:2',
            'slump_min' => 'decimal:1',
            'slump_max' => 'decimal:1',
            'water_cement_ratio' => 'decimal:2',
            'cement_kg' => 'decimal:2',
            'water_liter' => 'decimal:2',
            'fine_agg_kg' => 'decimal:2',
            'coarse_agg_kg' => 'decimal:2',
            'admixture_liter' => 'decimal:3',
            'is_active' => 'boolean',
            'snapshot_data' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function mixDesign(): BelongsTo
    {
        return $this->belongsTo(ConcreteMixDesign::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Create new version from current mix design
     */
    public static function createVersion(ConcreteMixDesign $mixDesign, string $changeReason, ?int $userId = null): self
    {
        $lastVersion = self::where('mix_design_id', $mixDesign->id)
            ->orderByDesc('version_number')
            ->first();

        $newVersionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;

        return self::create([
            'tenant_id' => $mixDesign->tenant_id,
            'mix_design_id' => $mixDesign->id,
            'version_number' => $newVersionNumber,
            'grade' => $mixDesign->grade,
            'name' => $mixDesign->name,
            'target_strength' => $mixDesign->target_strength,
            'strength_unit' => $mixDesign->strength_unit,
            'slump_min' => $mixDesign->slump_min,
            'slump_max' => $mixDesign->slump_max,
            'water_cement_ratio' => $mixDesign->water_cement_ratio,
            'cement_kg' => $mixDesign->cement_kg,
            'water_liter' => $mixDesign->water_liter,
            'fine_agg_kg' => $mixDesign->fine_agg_kg,
            'coarse_agg_kg' => $mixDesign->coarse_agg_kg,
            'admixture_liter' => $mixDesign->admixture_liter,
            'cement_type' => $mixDesign->cement_type,
            'agg_max_size' => $mixDesign->agg_max_size,
            'is_active' => $mixDesign->is_active,
            'notes' => $mixDesign->notes,
            'change_reason' => $changeReason,
            'changed_by' => $userId ?? Auth::id(),
            'snapshot_data' => $mixDesign->toArray(),
        ]);
    }

    /**
     * Compare with previous version
     */
    public function getChanges(): array
    {
        $previous = self::where('mix_design_id', $this->mix_design_id)
            ->where('version_number', '<', $this->version_number)
            ->orderByDesc('version_number')
            ->first();

        if (! $previous) {
            return ['message' => 'This is the first version'];
        }

        $changes = [];
        $trackableFields = [
            'grade',
            'name',
            'target_strength',
            'strength_unit',
            'slump_min',
            'slump_max',
            'water_cement_ratio',
            'cement_kg',
            'water_liter',
            'fine_agg_kg',
            'coarse_agg_kg',
            'admixture_liter',
            'cement_type',
            'agg_max_size',
            'notes',
        ];

        foreach ($trackableFields as $field) {
            $oldValue = $previous->$field;
            $newValue = $this->$field;

            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'diff' => is_numeric($oldValue) && is_numeric($newValue)
                        ? round($newValue - $oldValue, 3)
                        : null,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get all versions for a mix design
     */
    public static function getVersionsForMixDesign(int $mixDesignId): Collection
    {
        return self::where('mix_design_id', $mixDesignId)
            ->with(['createdBy', 'approvedBy'])
            ->orderByDesc('version_number')
            ->get();
    }

    /**
     * Approve this version
     */
    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Check if this version is approved
     */
    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }

    /**
     * Get version label
     */
    public function getVersionLabelAttribute(): string
    {
        return "v{$this->version_number}";
    }
}
