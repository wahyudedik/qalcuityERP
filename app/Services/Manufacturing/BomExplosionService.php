<?php

namespace App\Services\Manufacturing;

use App\Models\Bom;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Cache;

/**
 * BOM Explosion Service
 * 
 * Provides advanced BOM explosion capabilities:
 * - Multi-level BOM explosion with optimization
 * - Material requirements calculation
 * - Stock availability checking
 * - Cost calculation at each level
 * - Circular reference detection
 * - Cache-optimized repeated explosions
 */
class BomExplosionService
{
    /**
     * Explode BOM and return flattened material requirements
     * 
     * @param Bom $bom Bill of Materials to explode
     * @param float $quantity Production quantity
     * @param int $tenantId Tenant ID for isolation
     * @param bool $useCache Enable caching for repeated calls
     * @return array Exploded materials with details
     */
    public function explodeBom(Bom $bom, float $quantity, int $tenantId, bool $useCache = true): array
    {
        // Check cache first
        $cacheKey = "bom_explosion_{$bom->id}_{$quantity}_{$tenantId}";

        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        try {
            // Explode BOM (circular reference detection built-in)
            $explodedMaterials = $bom->explode($quantity);

            // Enrich with product details and stock
            $enrichedMaterials = $this->enrichMaterials($explodedMaterials, $tenantId);

            // Calculate totals
            $summary = $this->calculateSummary($enrichedMaterials, $bom, $quantity);

            $result = [
                'success' => true,
                'bom_id' => $bom->id,
                'bom_name' => $bom->name,
                'product_id' => $bom->product_id,
                'product_name' => $bom->product?->name,
                'quantity' => $quantity,
                'batch_size' => $bom->batch_size,
                'materials' => $enrichedMaterials,
                'summary' => $summary,
                'total_materials' => count($enrichedMaterials),
                'max_level' => $this->getMaxLevel($explodedMaterials),
            ];

            // Cache for 1 hour
            if ($useCache) {
                Cache::put($cacheKey, $result, 3600);
            }

            return $result;

        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'bom_id' => $bom->id,
                'quantity' => $quantity,
            ];
        }
    }

    /**
     * Explode BOM and check stock availability
     * 
     * @param Bom $bom Bill of Materials
     * @param float $quantity Production quantity
     * @param int $tenantId Tenant ID
     * @return array Stock check results
     */
    public function checkStockAvailability(Bom $bom, float $quantity, int $tenantId): array
    {
        $explosion = $this->explodeBom($bom, $quantity, $tenantId);

        if (!$explosion['success']) {
            return $explosion;
        }

        $stockStatus = [];
        $allAvailable = true;
        $totalCost = 0;

        foreach ($explosion['materials'] as $material) {
            // Get total stock across all warehouses
            $totalStock = ProductStock::where('product_id', $material['product_id'])
                ->sum('quantity');

            $availableQty = $totalStock ?? 0;
            $requiredQty = $material['quantity'];
            $isAvailable = $availableQty >= $requiredQty;

            if (!$isAvailable) {
                $allAvailable = false;
            }

            $shortage = max(0, $requiredQty - $availableQty);
            $materialCost = $material['unit_cost'] * $requiredQty;
            $totalCost += $materialCost;

            $stockStatus[] = [
                'product_id' => $material['product_id'],
                'product_name' => $material['product_name'],
                'product_code' => $material['product_code'],
                'required_qty' => $requiredQty,
                'available_qty' => $availableQty,
                'shortage' => $shortage,
                'is_available' => $isAvailable,
                'unit' => $material['unit'],
                'unit_cost' => $material['unit_cost'],
                'total_cost' => $materialCost,
                'level' => $material['level'],
            ];
        }

        return [
            'success' => true,
            'all_available' => $allAvailable,
            'bom_id' => $bom->id,
            'quantity' => $quantity,
            'materials' => $stockStatus,
            'total_materials' => count($stockStatus),
            'materials_available' => collect($stockStatus)->where('is_available', true)->count(),
            'materials_shortage' => collect($stockStatus)->where('is_available', false)->count(),
            'total_material_cost' => $totalCost,
            'can_produce' => $allAvailable,
        ];
    }

    /**
     * Calculate detailed production cost from BOM
     * 
     * @param Bom $bom Bill of Materials
     * @param float $quantity Production quantity
     * @param int $tenantId Tenant ID
     * @return array Cost breakdown
     */
    public function calculateProductionCost(Bom $bom, float $quantity, int $tenantId): array
    {
        $explosion = $this->explodeBom($bom, $quantity, $tenantId);

        if (!$explosion['success']) {
            return $explosion;
        }

        $materialCost = 0;
        $costByLevel = [];

        foreach ($explosion['materials'] as $material) {
            $cost = $material['unit_cost'] * $material['quantity'];
            $materialCost += $cost;

            if (!isset($costByLevel[$material['level']])) {
                $costByLevel[$material['level']] = 0;
            }
            $costByLevel[$material['level']] += $cost;
        }

        $costPerUnit = $quantity > 0 ? $materialCost / $quantity : 0;

        return [
            'success' => true,
            'bom_id' => $bom->id,
            'quantity' => $quantity,
            'material_cost' => $materialCost,
            'cost_per_unit' => $costPerUnit,
            'cost_by_level' => $costByLevel,
            'materials' => $explosion['materials'],
        ];
    }

    /**
     * Compare multiple BOMs for the same product
     * 
     * @param array $bomIds Array of BOM IDs to compare
     * @param float $quantity Production quantity
     * @param int $tenantId Tenant ID
     * @return array Comparison results
     */
    public function compareBoms(array $bomIds, float $quantity, int $tenantId): array
    {
        $comparisons = [];

        foreach ($bomIds as $bomId) {
            $bom = Bom::with('product')->find($bomId);

            if (!$bom || $bom->tenant_id !== $tenantId) {
                continue;
            }

            $explosion = $this->explodeBom($bom, $quantity, $tenantId);

            if ($explosion['success']) {
                $comparisons[] = [
                    'bom_id' => $bom->id,
                    'bom_name' => $bom->name,
                    'product_name' => $bom->product?->name,
                    'batch_size' => $bom->batch_size,
                    'total_materials' => $explosion['total_materials'],
                    'max_level' => $explosion['max_level'],
                    'material_cost' => $explosion['summary']['total_cost'] ?? 0,
                    'materials' => $explosion['materials'],
                ];
            }
        }

        // Sort by cost
        usort($comparisons, function ($a, $b) {
            return $a['material_cost'] <=> $b['material_cost'];
        });

        return [
            'quantity' => $quantity,
            'boms_compared' => count($comparisons),
            'comparisons' => $comparisons,
            'cheapest' => $comparisons[0] ?? null,
            'most_expensive' => $comparisons[count($comparisons) - 1] ?? null,
        ];
    }

    /**
     * Get BOM hierarchy/tree structure for visualization
     * 
     * @param Bom $bom Bill of Materials
     * @param float $quantity Production quantity
     * @param int $level Current recursion level
     * @return array Tree structure
     */
    public function getBomTree(Bom $bom, float $quantity = 1, int $level = 0): array
    {
        $multiplier = $bom->batch_size > 0 ? $quantity / (float) $bom->batch_size : $quantity;

        $tree = [
            'bom_id' => $bom->id,
            'bom_name' => $bom->name,
            'product_name' => $bom->product?->name,
            'quantity' => $quantity,
            'level' => $level,
            'children' => [],
        ];

        $lines = $bom->lines()->with(['product', 'childBom.product'])->get();

        foreach ($lines as $line) {
            $neededQty = $line->quantity_per_batch * $multiplier;

            $child = [
                'line_id' => $line->id,
                'product_id' => $line->product_id,
                'product_name' => $line->product?->name,
                'product_code' => $line->product?->code,
                'quantity' => round($neededQty, 3),
                'unit' => $line->unit,
                'level' => $level + 1,
                'is_assembly' => (bool) $line->child_bom_id,
            ];

            if ($line->childBom) {
                $child['bom_tree'] = $this->getBomTree($line->childBom, $neededQty, $level + 1);
            }

            $tree['children'][] = $child;
        }

        return $tree;
    }

    /**
     * Clear BOM explosion cache
     * 
     * @param int|null $bomId Specific BOM ID or null for all
     */
    public function clearCache(?int $bomId = null): void
    {
        if ($bomId) {
            Cache::tags(["bom_explosion_{$bomId}"])->flush();
        } else {
            Cache::tags(['bom_explosion'])->flush();
        }
    }

    /**
     * Enrich exploded materials with product details and costs
     */
    protected function enrichMaterials(array $materials, int $tenantId): array
    {
        $productIds = collect($materials)->pluck('product_id')->unique();

        $products = Product::whereIn('id', $productIds)
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('id');

        $enriched = [];

        foreach ($materials as $material) {
            $product = $products->get($material['product_id']);

            if (!$product) {
                continue;
            }

            // Get latest cost from product or average stock cost
            // ProductStock doesn't have unit_cost, so we use product's cost_price
            $unitCost = $product->cost_price ?? 0;


            $enriched[] = [
                'product_id' => $material['product_id'],
                'product_name' => $product->name,
                'product_code' => $product->code,
                'quantity' => $material['quantity'],
                'unit' => $material['unit'],
                'level' => $material['level'],
                'unit_cost' => $unitCost,
                'total_cost' => round($unitCost * $material['quantity'], 2),
            ];
        }

        return $enriched;
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary(array $materials, Bom $bom, float $quantity): array
    {
        $totalCost = collect($materials)->sum('total_cost');
        $costPerUnit = $quantity > 0 ? $totalCost / $quantity : 0;

        $byLevel = collect($materials)
            ->groupBy('level')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('total_cost'),
                ];
            })
            ->toArray();

        return [
            'total_cost' => round($totalCost, 2),
            'cost_per_unit' => round($costPerUnit, 2),
            'by_level' => $byLevel,
        ];
    }

    /**
     * Get maximum level in exploded materials
     */
    protected function getMaxLevel(array $materials): int
    {
        if (empty($materials)) {
            return 0;
        }

        return max(array_column($materials, 'level'));
    }
}
