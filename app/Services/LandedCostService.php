<?php

namespace App\Services;

use App\Models\LandedCost;
use App\Models\Product;

class LandedCostService
{
    /**
     * Calculate and save allocations based on the chosen method.
     * Distributes total additional cost across product lines.
     */
    public function allocate(LandedCost $lc): array
    {
        $totalCost = (float) $lc->components()->sum('amount');
        $lc->update(['total_additional_cost' => $totalCost]);

        $allocations = $lc->allocations()->with('product')->get();
        if ($allocations->isEmpty()) {
            return ['success' => false, 'message' => 'Tidak ada item untuk dialokasikan.'];
        }
        if ($totalCost <= 0) {
            return ['success' => false, 'message' => 'Total biaya tambahan = 0.'];
        }

        $method = $lc->allocation_method;

        // Calculate allocation base per line
        $totalBase = 0;
        $lines = [];

        foreach ($allocations as $alloc) {
            $base = match ($method) {
                'by_value' => (float) $alloc->original_cost,
                'by_quantity' => (float) $alloc->quantity,
                'by_weight' => (float) ($alloc->weight ?? $alloc->quantity),
                'equal' => 1,
                default => (float) $alloc->original_cost,
            };
            $totalBase += $base;
            $lines[] = ['alloc' => $alloc, 'base' => $base];
        }

        if ($totalBase <= 0) {
            return ['success' => false, 'message' => 'Basis alokasi = 0. Pastikan data item terisi.'];
        }

        // Distribute cost
        $allocated = 0;
        foreach ($lines as $i => $line) {
            $ratio = $line['base'] / $totalBase;
            $share = round($totalCost * $ratio, 2);

            // Last item gets remainder to avoid rounding issues
            if ($i === count($lines) - 1) {
                $share = round($totalCost - $allocated, 2);
            }

            $qty = (float) $line['alloc']->quantity;
            $originalCost = (float) $line['alloc']->original_cost;
            $landedUnit = $qty > 0 ? round(($originalCost + $share) / $qty, 2) : 0;

            $line['alloc']->update([
                'allocated_cost' => $share,
                'landed_unit_cost' => $landedUnit,
            ]);

            $allocated += $share;
        }

        return [
            'success' => true,
            'total_cost' => $totalCost,
            'lines' => count($lines),
        ];
    }

    /**
     * Update product price_buy with landed unit cost (optional).
     */
    public function updateProductCosts(LandedCost $lc): int
    {
        $updated = 0;
        foreach ($lc->allocations as $alloc) {
            if ($alloc->landed_unit_cost > 0) {
                Product::where('id', $alloc->product_id)
                    ->update(['price_buy' => $alloc->landed_unit_cost]);
                $updated++;
            }
        }

        return $updated;
    }
}
