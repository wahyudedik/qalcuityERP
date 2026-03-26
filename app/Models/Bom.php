<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
    protected $fillable = [
        'tenant_id', 'product_id', 'name', 'batch_size', 'batch_unit',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'batch_size' => 'decimal:3',
            'is_active'  => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function lines(): HasMany { return $this->hasMany(BomLine::class)->orderBy('sort_order'); }

    /**
     * Explode BOM secara rekursif — flatten semua raw material.
     * Return: [['product_id' => x, 'quantity' => y, 'unit' => z, 'level' => n], ...]
     */
    public function explode(float $qty = 1, int $level = 0, int $maxDepth = 10): array
    {
        if ($level >= $maxDepth) return [];

        $result = [];
        $multiplier = $this->batch_size > 0 ? $qty / $this->batch_size : $qty;

        foreach ($this->lines()->with(['childBom.lines'])->get() as $line) {
            $neededQty = $line->quantity_per_batch * $multiplier;

            if ($line->child_bom_id && $line->childBom) {
                // Sub-assembly — recurse
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
}
