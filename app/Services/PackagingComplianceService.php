<?php

namespace App\Services;

class PackagingComplianceService
{
    /**
     * TASK-2.40: Validate label compliance
     */
    public function validateLabelCompliance(array $labelData, int $tenantId): array
    {
        $checks = [
            'product_name' => [
                'label' => 'Product Name',
                'passed' => !empty($labelData['product_name']),
                'required' => true,
            ],
            'bpom_number' => [
                'label' => 'BPOM Registration Number',
                'passed' => !empty($labelData['bpom_number']),
                'required' => true,
            ],
            'net_content' => [
                'label' => 'Net Content',
                'passed' => !empty($labelData['net_content']),
                'required' => true,
            ],
            'ingredients_list' => [
                'label' => 'Ingredients List (INCI)',
                'passed' => !empty($labelData['ingredients_list']),
                'required' => true,
            ],
            'manufacturer_info' => [
                'label' => 'Manufacturer Information',
                'passed' => !empty($labelData['manufacturer_name']) && !empty($labelData['manufacturer_address']),
                'required' => true,
            ],
            'manufacturing_date' => [
                'label' => 'Manufacturing Date',
                'passed' => !empty($labelData['manufacturing_date']),
                'required' => true,
            ],
            'expiry_date' => [
                'label' => 'Expiry Date / Batch Code',
                'passed' => !empty($labelData['expiry_date']) || !empty($labelData['batch_code']),
                'required' => true,
            ],
            'warnings' => [
                'label' => 'Warnings/Cautions',
                'passed' => !empty($labelData['warnings']),
                'required' => false,
            ],
            'usage_instructions' => [
                'label' => 'Usage Instructions',
                'passed' => !empty($labelData['usage_instructions']),
                'required' => false,
            ],
            'barcode' => [
                'label' => 'Barcode/GTIN',
                'passed' => !empty($labelData['barcode']),
                'required' => false,
            ],
        ];

        $passedCount = collect($checks)->where('passed', true)->count();
        $requiredCount = collect($checks)->where('required', true)->count();
        $requiredPassed = collect($checks)->where('required', true)->where('passed', true)->count();

        return [
            'checks' => $checks,
            'total_checks' => count($checks),
            'passed' => $passedCount,
            'failed' => count($checks) - $passedCount,
            'required_checks' => $requiredCount,
            'required_passed' => $requiredPassed,
            'compliant' => $requiredPassed === $requiredCount,
            'percentage' => round(($passedCount / count($checks)) * 100, 2),
        ];
    }

    /**
     * Generate barcode for product
     */
    public function generateBarcode(string $sku, string $type = 'code128'): array
    {
        return [
            'sku' => $sku,
            'barcode_type' => $type,
            'barcode_data' => $this->generateBarcodeData($sku, $type),
        ];
    }

    /**
     * Generate barcode data (simplified - in production use barcode library)
     */
    protected function generateBarcodeData(string $sku, string $type): string
    {
        // For Code128, return SKU directly
        // For EAN-13, would need 13-digit number
        return $sku;
    }

    /**
     * Get packaging requirements by product category
     */
    public function getPackagingRequirements(string $category): array
    {
        $requirements = [
            'skincare' => [
                'container_type' => ['bottle', 'jar', 'tube', 'pump'],
                'label_language' => ['id', 'en'],
                'requires_sun_protection_label' => false,
                'requires_dermatologist_tested' => false,
            ],
            'sunscreen' => [
                'container_type' => ['bottle', 'tube', 'spray'],
                'label_language' => ['id', 'en'],
                'requires_sun_protection_label' => true,
                'spf_label_required' => true,
                'pa_rating_required' => true,
            ],
            'makeup' => [
                'container_type' => ['compact', 'tube', 'bottle', 'pencil'],
                'label_language' => ['id', 'en'],
                'requires_color_shade_label' => true,
                'requires_hypoallergenic_label' => false,
            ],
        ];

        return $requirements[$category] ?? $requirements['skincare'];
    }

    /**
     * Validate batch numbering format
     */
    public function validateBatchNumber(string $batchNumber): array
    {
        // Format: YYYYMMDD-XXX (Date + sequence)
        $pattern = '/^\d{8}-\d{3}$/';
        $isValid = preg_match($pattern, $batchNumber);

        return [
            'valid' => (bool) $isValid,
            'batch_number' => $batchNumber,
            'format' => 'YYYYMMDD-XXX',
            'message' => $isValid ? 'Valid batch number format' : 'Invalid format. Expected: YYYYMMDD-XXX',
        ];
    }
}
