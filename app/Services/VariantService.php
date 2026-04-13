<?php

namespace App\Services;

use App\Models\CosmeticFormula;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariantService
{
    /**
     * TASK-2.39: Create variant matrix for formula
     */
    public function createVariantMatrix(int $tenantId, int $formulaId, array $variants): array
    {
        return DB::transaction(function () use ($tenantId, $formulaId, $variants) {
            $formula = CosmeticFormula::where('tenant_id', $tenantId)
                ->findOrFail($formulaId);

            $createdVariants = [];

            foreach ($variants as $variantData) {
                $variant = new ProductVariant();
                $variant->tenant_id = $tenantId;
                $variant->formula_id = $formulaId;
                $variant->sku = $variantData['sku'] ?? $this->generateSku($formula, $variantData);
                $variant->variant_name = $variantData['variant_name'];
                $variant->attributes = $variantData['attributes'];
                $variant->size = $variantData['size'] ?? null;
                $variant->unit = $variantData['unit'] ?? 'ml';
                $variant->price_adjustment = $variantData['price_adjustment'] ?? 0;
                $variant->cost_adjustment = $variantData['cost_adjustment'] ?? 0;
                $variant->barcode = $variantData['barcode'] ?? null;
                $variant->is_active = $variantData['is_active'] ?? true;
                $variant->sort_order = $variantData['sort_order'] ?? 0;
                $variant->save();

                // Create attribute combinations
                if (isset($variantData['attributes'])) {
                    foreach ($variantData['attributes'] as $attrName => $attrValue) {
                        VariantAttribute::updateOrCreate(
                            [
                                'tenant_id' => $tenantId,
                                'variant_id' => $variant->id,
                                'attribute_name' => $attrName,
                            ],
                            [
                                'attribute_value' => $attrValue,
                            ]
                        );
                    }
                }

                $createdVariants[] = $variant;
            }

            Log::info('Variant matrix created', [
                'formula_id' => $formulaId,
                'variants_count' => count($createdVariants),
            ]);

            return $createdVariants;
        });
    }

    /**
     * Get variant matrix for formula
     */
    public function getVariantMatrix(int $tenantId, int $formulaId): array
    {
        $variants = ProductVariant::where('tenant_id', $tenantId)
            ->where('formula_id', $formulaId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('variant_name')
            ->get();

        // Group by attribute dimensions
        $groupedVariants = $variants->groupBy(function ($variant) {
            $attrs = $variant->attributes;
            ksort($attrs);
            return json_encode($attrs);
        });

        return [
            'variants' => $variants,
            'grouped' => $groupedVariants,
            'total' => $variants->count(),
            'unique_attributes' => $this->getUniqueAttributes($variants),
        ];
    }

    /**
     * Generate SKU for variant
     */
    protected function generateSku(CosmeticFormula $formula, array $variantData): string
    {
        $formulaCode = $formula->formula_code;
        $attributes = $variantData['attributes'] ?? [];
        $size = $variantData['size'] ?? '';

        $attrSuffix = '';
        foreach ($attributes as $key => $value) {
            $attrSuffix .= '-' . strtoupper(substr($value, 0, 3));
        }

        $sizeSuffix = $size ? '-' . $size : '';

        return $formulaCode . $attrSuffix . $sizeSuffix;
    }

    /**
     * Get unique attributes across all variants
     */
    protected function getUniqueAttributes($variants): array
    {
        $uniqueAttrs = [];

        foreach ($variants as $variant) {
            if (is_array($variant->attributes)) {
                foreach ($variant->attributes as $key => $value) {
                    if (!isset($uniqueAttrs[$key])) {
                        $uniqueAttrs[$key] = [];
                    }
                    if (!in_array($value, $uniqueAttrs[$key])) {
                        $uniqueAttrs[$key][] = $value;
                    }
                }
            }
        }

        return $uniqueAttrs;
    }

    /**
     * Update variant
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        Log::info('Variant updated', [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
        ]);

        return $variant;
    }

    /**
     * Toggle variant active status
     */
    public function toggleVariant(ProductVariant $variant): ProductVariant
    {
        $variant->is_active = !$variant->is_active;
        $variant->save();

        Log::info('Variant toggled', [
            'variant_id' => $variant->id,
            'is_active' => $variant->is_active,
        ]);

        return $variant;
    }

    /**
     * Delete variant
     */
    public function deleteVariant(ProductVariant $variant): bool
    {
        $variant->delete();

        Log::info('Variant deleted', [
            'variant_id' => $variant->id,
        ]);

        return true;
    }
}
