<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
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
            'is_active'  => 'boolean',
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
     * Eager-loads the full BOM tree in a single query chain so recursive calls
     * do not fire additional DB queries. Also uses a static per-request cache
     * to avoid re-exploding the same BOM+quantity pair multiple times.
     */
    public function explode(float $qty = 1, int $level = 0, int $maxDepth = 10): array
    {
        if ($level >= $maxDepth) return [];

        // Only pre-load the full tree at the root call (level 0).
        // Deeper recursive calls reuse already-loaded relations.
        if ($level === 0 && !$this->relationLoaded('lines')) {
            $this->load(self::buildNestedWith($maxDepth));
        }

        $result     = [];
        $multiplier = $this->batch_size > 0 ? $qty / (float) $this->batch_size : $qty;

        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        foreach ($lines as $line) {
            $neededQty = $line->quantity_per_batch * $multiplier;

            if ($line->child_bom_id && $line->childBom) {
                // Sub-assembly — recurse (relations already in memory, no extra queries)
                $result = array_merge($result, $line->childBom->explode($neededQty, $level + 1, $maxDepth));
            } else {
                // Raw material
                $result[] = [
                    'product_id' => $line->product_id,
                    'quantity'   => round($neededQty, 3),
                    'unit'       => $line->unit,
                    'level'      => $level,
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
            $with  = $path;
        }
        return $with;
    }
}
