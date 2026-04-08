<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RabItem extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'project_id', 'tenant_id', 'parent_id', 'code', 'name', 'type',
        'category', 'volume', 'unit', 'unit_price', 'coefficient',
        'subtotal', 'actual_cost', 'actual_volume', 'sort_order', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'volume'        => 'decimal:3',
            'unit_price'    => 'decimal:2',
            'coefficient'   => 'decimal:4',
            'subtotal'      => 'decimal:2',
            'actual_cost'   => 'decimal:2',
            'actual_volume' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        // Auto-calculate subtotal on save
        static::saving(function (self $item) {
            if ($item->type === 'item') {
                $item->subtotal = round($item->volume * $item->unit_price * $item->coefficient, 2);
            }
        });

        // After save, recalculate parent group subtotal
        static::saved(function (self $item) {
            $item->recalculateParent();
        });

        static::deleted(function (self $item) {
            $item->recalculateParent();
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }

    // ─── Calculations ─────────────────────────────────────────────

    /**
     * Recalculate parent group subtotal from children.
     */
    public function recalculateParent(): void
    {
        if (!$this->parent_id) return;

        $parent = self::find($this->parent_id);
        if (!$parent || $parent->type !== 'group') return;

        $parent->timestamps = false;
        $parent->subtotal = $parent->children()->sum('subtotal');
        $parent->actual_cost = $parent->children()->sum('actual_cost');
        $parent->actual_volume = 0; // groups don't have volume
        $parent->save();

        // Recurse up
        $parent->recalculateParent();
    }

    /**
     * Variance: subtotal - actual_cost
     */
    public function variance(): float
    {
        return (float) $this->subtotal - (float) $this->actual_cost;
    }

    /**
     * Realization percentage.
     */
    public function realizationPercent(): float
    {
        return $this->subtotal > 0
            ? round(($this->actual_cost / $this->subtotal) * 100, 1)
            : 0;
    }

    /**
     * Volume progress percentage.
     */
    public function volumeProgress(): float
    {
        return $this->volume > 0
            ? round(($this->actual_volume / $this->volume) * 100, 1)
            : 0;
    }

    /**
     * Get the full hierarchical tree for a project.
     */
    public static function tree(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('project_id', $projectId)
            ->whereNull('parent_id')
            ->with('children.children.children') // 3 levels deep
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Recalculate all group subtotals for a project.
     */
    public static function recalculateProject(int $projectId): void
    {
        // Bottom-up: recalculate leaf groups first, then parents
        $groups = self::where('project_id', $projectId)
            ->where('type', 'group')
            ->orderByDesc('id') // deepest first (approximate)
            ->get();

        foreach ($groups as $group) {
            $group->timestamps = false;
            $group->subtotal = $group->children()->sum('subtotal');
            $group->actual_cost = $group->children()->sum('actual_cost');
            $group->save();
        }

        // Update project budget from RAB total
        $total = self::where('project_id', $projectId)
            ->whereNull('parent_id')
            ->sum('subtotal');

        Project::where('id', $projectId)->update(['budget' => $total]);
    }
}
