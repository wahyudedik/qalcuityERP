<?php

namespace App\Services;

use App\Models\Bom;

/**
 * ManufacturingService
 *
 * Provides manufacturing operations including recursive BOM explosion.
 *
 * BUG-MFG-001 FIX (Bug 1.19): explodeBom() is now fully recursive, handling
 * sub-assemblies at any depth (level 3+). Guard clause prevents infinite loops
 * from circular references.
 */
class ManufacturingService
{
    /**
     * Explode a BOM recursively, returning all components at every level.
     *
     * Bug Condition: module = 'manufacturing' AND bomDepth > 2 AND NOT recursiveExplosion(input)
     * Expected Behavior: all components at all levels (including level 3+) are present in result.
     *
     * @param  int   $productId  The product whose BOM should be exploded
     * @param  float $quantity   Production quantity
     * @param  int   $depth      Current recursion depth (internal use)
     * @return array<int, array{product_id: int, quantity: float, depth: int}>
     *
     * @throws \DomainException when BOM depth exceeds 10 (circular reference guard)
     */
    public function explodeBom(int $productId, float $quantity, int $depth = 0): array
    {
        if ($depth > 10) {
            throw new \DomainException(
                "BOM terlalu dalam (>10 level) untuk product_id {$productId}. " .
                'Kemungkinan ada circular reference pada struktur BOM.'
            );
        }

        // Find the active BOM for this product
        $bom = Bom::where('product_id', $productId)
            ->where('is_active', true)
            ->with('lines.childBom')
            ->first();

        if (!$bom) {
            return [];
        }

        $result = [];
        $multiplier = $bom->batch_size > 0 ? $quantity / (float) $bom->batch_size : $quantity;

        foreach ($bom->lines as $line) {
            $neededQty = round($line->quantity_per_batch * $multiplier, 3);

            $result[] = [
                'product_id' => $line->product_id,
                'quantity'   => $neededQty,
                'depth'      => $depth,
            ];

            // Recurse into sub-assemblies (child_bom_id indicates a sub-assembly)
            if ($line->child_bom_id) {
                $subComponents = $this->explodeBom(
                    $line->product_id,
                    $neededQty,
                    $depth + 1
                );
                $result = array_merge($result, $subComponents);
            }
        }

        return $result;
    }

    /**
     * Explode a BOM using the Bom model's built-in recursive explosion.
     *
     * Delegates to Bom::explode() which handles circular reference detection,
     * eager loading of the full tree, and multi-level sub-assembly recursion.
     *
     * @param  Bom   $bom      The BOM to explode
     * @param  float $quantity Production quantity
     * @return array           Flat list of all raw materials with level info
     *
     * @throws \RuntimeException on circular reference or depth exceeded
     */
    public function explodeBomModel(Bom $bom, float $quantity): array
    {
        return $bom->explode($quantity);
    }
}
