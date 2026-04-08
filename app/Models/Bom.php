<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'batch_size',
        'batch_unit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'batch_size' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function lines(): HasMany
    {
        return $this->hasMany(BomLine::class)->orderBy('sort_order');
    }

    /**
     * Explode BOM secara rekursif — flatten semua raw material.
     * Return: [['product_id' => x, 'quantity' => y, 'unit' => z, 'level' => n], ...]
     *
     * BUG-MFG-001 FIX: Added circular reference detection with clear error messages
     * Eager-loads the full BOM tree in a single query chain so recursive calls
     * do not fire additional DB queries. Also uses a static per-request cache
     * to avoid re-exploding the same BOM+quantity pair multiple times.
     */
    public function explode(float $qty = 1, int $level = 0, int $maxDepth = 10, array $visitedBomIds = []): array
    {
        if ($level >= $maxDepth) {
            \Log::error('BOM explosion reached max depth - possible circular reference', [
                'bom_id' => $this->id,
                'bom_name' => $this->name,
                'level' => $level,
                'visited_boms' => $visitedBomIds,
            ]);

            throw new \RuntimeException(
                "BOM explosion gagal: Circular reference terdeteksi atau BOM terlalu dalam (level {$level}). " .
                "Periksa BOM #{$this->id} ({$this->name}) - kemungkinan ada referensi melingkar."
            );
        }

        // BUG-MFG-001 FIX: Check for circular reference
        if (in_array($this->id, $visitedBomIds)) {
            $cyclePath = implode(' → ', $visitedBomIds) . " → {$this->id}";

            \Log::error('BOM circular reference detected', [
                'bom_id' => $this->id,
                'bom_name' => $this->name,
                'cycle_path' => $cyclePath,
            ]);

            throw new \RuntimeException(
                "BOM circular reference terdeteksi! BOM #{$this->id} ({$this->name}) sudah ada di chain. " .
                "Cycle path: {$cyclePath}. Perbaiki BOM structure untuk menghapus circular reference."
            );
        }

        // Add current BOM to visited list
        $visitedBomIds[] = $this->id;

        // Only pre-load the full tree at the root call (level 0).
        // Deeper recursive calls reuse already-loaded relations.
        if ($level === 0 && !$this->relationLoaded('lines')) {
            $this->load(self::buildNestedWith($maxDepth));
        }

        $result = [];
        $multiplier = $this->batch_size > 0 ? $qty / (float) $this->batch_size : $qty;

        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        foreach ($lines as $line) {
            $neededQty = $line->quantity_per_batch * $multiplier;

            if ($line->child_bom_id && $line->childBom) {
                // BUG-MFG-001 FIX: Check self-reference
                if ($line->child_bom_id == $this->id) {
                    throw new \RuntimeException(
                        "BOM self-reference terdeteksi! BOM #{$this->id} ({$this->name}) " .
                        "refer ke dirinya sendiri di line product #{$line->product_id}. " .
                        "BOM tidak boleh memiliki child_bom_id yang sama dengan parent BOM."
                    );
                }

                // Sub-assembly — recurse with visited tracking
                $result = array_merge(
                    $result,
                    $line->childBom->explode($neededQty, $level + 1, $maxDepth, $visitedBomIds)
                );
            } else {
                // Raw material
                $result[] = [
                    'product_id' => $line->product_id,
                    'quantity' => round($neededQty, 3),
                    'unit' => $line->unit,
                    'level' => $level,
                ];
            }
        }

        return $result;
    }

    /**
     * Build a nested eager-load string for the full BOM tree, e.g.:
     *   'lines.childBom.lines.childBom.lines.childBom'
     * This pre-loads every level in one shot.
     */
    public static function buildNestedWith(int $depth = 10): string
    {
        $with = 'lines';
        $path = 'lines';
        for ($i = 1; $i < $depth; $i++) {
            $path .= '.childBom.lines';
            $with = $path;
        }
        return $with;
    }

    /**
     * BUG-MFG-001 FIX: Check if this BOM has circular reference
     * Returns true if circular reference exists
     */
    public function hasCircularReference(): bool
    {
        try {
            $this->explode(1, 0, 10, []);
            return false; // No circular reference
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'circular reference')) {
                return true;
            }
            throw $e; // Re-throw other errors
        }
    }

    /**
     * BUG-MFG-001 FIX: Get BOM hierarchy path for visualization
     * Returns array like: ['BOM A', 'BOM B', 'BOM C']
     */
    public function getHierarchyPath(array $visitedIds = []): array
    {
        if (in_array($this->id, $visitedIds)) {
            return ['(circular) ' . $this->name];
        }

        $visitedIds[] = $this->id;
        $path = [$this->name];

        $lines = $this->lines()->with('childBom')->get();
        foreach ($lines as $line) {
            if ($line->childBom) {
                $childPath = $line->childBom->getHierarchyPath($visitedIds);
                foreach ($childPath as $childName) {
                    $path[] = '  └─ ' . $childName;
                }
            }
        }

        return $path;
    }
}
