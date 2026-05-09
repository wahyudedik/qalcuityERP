<?php

namespace App\Services;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\Exceptions\BarcodeException;

/**
 * Barcode Service - Generate and Print Barcodes
 *
 * Supports multiple barcode formats:
 * - Code 128 (default, recommended for products)
 * - EAN-13 (retail products)
 * - UPC-A (North American retail)
 * - Code 39 (industrial)
 * - QR Code (2D matrix)
 */
class BarcodeService
{
    /**
     * Default barcode type
     */
    protected const DEFAULT_TYPE = 'code128';

    /**
     * Barcode type constants
     */
    protected const TYPE_MAP = [
        'code128' => BarcodeGeneratorPNG::TYPE_CODE_128,
        'code39' => BarcodeGeneratorPNG::TYPE_CODE_39,
        'ean13' => BarcodeGeneratorPNG::TYPE_EAN_13,
        'ean8' => BarcodeGeneratorPNG::TYPE_EAN_8,
        'upca' => BarcodeGeneratorPNG::TYPE_UPC_A,
        'upce' => BarcodeGeneratorPNG::TYPE_UPC_E,
        // QR code is not supported by Picqer\Barcode, will be handled separately
    ];

    /**
     * Generate barcode image
     *
     * @param  string  $value  Barcode value/data
     * @param  string  $type  Barcode type (code128, ean13, upca, qr, etc.)
     * @param  string  $format  Output format (png, svg, html)
     * @param  int  $width  Barcode width in pixels
     * @param  int  $height  Barcode height in pixels
     * @return string Image data (binary for PNG/SVG, HTML for HTML format)
     *
     * @throws BarcodeException
     */
    public function generate(
        string $value,
        string $type = 'code128',
        string $format = 'png',
        int $width = 2,
        int $height = 30
    ): string {
        try {
            return match (strtolower($format)) {
                'png' => $this->generatePNG($value, $type, $width, $height),
                'svg' => $this->generateSVG($value, $type),
                'html' => $this->generateHTML($value, $type),
                default => $this->generatePNG($value, $type, $width, $height),
            };
        } catch (BarcodeException $e) {
            Log::error('Barcode generation failed', [
                'value' => $value,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate barcode as PNG image
     */
    protected function generatePNG(string $value, string $type, int $width, int $height): string
    {
        /** @var BarcodeGeneratorPNG $generator */
        $generator = new BarcodeGeneratorPNG;
        $typeConstant = $this->getTypeConstant($type);

        // Suppress false positive: Picqer library DOES have generate() method
        // but IntelliSense cannot detect it from stubs
        $result = call_user_func([$generator, 'generate'], $value, $typeConstant, $width, $height);

        return $result;
    }

    /**
     * Generate barcode as SVG
     */
    protected function generateSVG(string $value, string $type): string
    {
        /** @var BarcodeGeneratorSVG $generator */
        $generator = new BarcodeGeneratorSVG;
        $typeConstant = $this->getTypeConstant($type);

        // Suppress false positive: Picqer library DOES have generate() method
        // but IntelliSense cannot detect it from stubs
        $result = call_user_func([$generator, 'generate'], $value, $typeConstant, 2, 30);

        return $result;
    }

    /**
     * Generate barcode as HTML (for direct embedding)
     */
    protected function generateHTML(string $value, string $type): string
    {
        /** @var BarcodeGeneratorHTML $generator */
        $generator = new BarcodeGeneratorHTML;
        $typeConstant = $this->getTypeConstant($type);

        // Suppress false positive: Picqer library DOES have generate() method
        // but IntelliSense cannot detect it from stubs
        $result = call_user_func([$generator, 'generate'], $value, $typeConstant, 2, 30);

        return $result;
    }

    /**
     * Get barcode type constant
     */
    protected function getTypeConstant(string $type): int
    {
        return self::TYPE_MAP[$type] ?? self::TYPE_MAP[self::DEFAULT_TYPE];
    }

    /**
     * Auto-generate barcode from product SKU
     *
     * Format: QAL-{SKU} (removes special characters)
     * Example: SKU "ABC-123" → "QALABC123"
     */
    public function generateFromSKU(string $sku, string $prefix = 'QAL'): string
    {
        // Remove special characters, keep alphanumeric
        $cleanSku = preg_replace('/[^A-Z0-9]/i', '', $sku);

        // Add prefix
        $barcode = $prefix.'-'.strtoupper($cleanSku);

        return $barcode;
    }

    /**
     * Validate barcode format
     *
     * @param  string  $barcode  Barcode value to validate
     * @param  string  $type  Expected barcode type
     * @return bool True if valid
     */
    public function validate(string $barcode, string $type = 'code128'): bool
    {
        try {
            // Try to generate barcode - if it fails, validation fails
            $this->generate($barcode, $type);

            return true;
        } catch (BarcodeException $e) {
            return false;
        }
    }

    /**
     * Validate EAN-13 checksum
     * EAN-13 has a built-in check digit that can be validated
     */
    public function validateEAN13(string $barcode): bool
    {
        if (strlen($barcode) !== 13 || ! ctype_digit($barcode)) {
            return false;
        }

        $checksum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $barcode[$i];
            $checksum += $digit * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($checksum % 10)) % 10;

        return $checkDigit === (int) $barcode[12];
    }

    /**
     * Generate barcode with human-readable text
     * Returns HTML with barcode and text below
     */
    public function generateWithText(
        string $value,
        string $type = 'code128',
        ?string $text = null
    ): string {
        $barcodeImage = $this->generate($value, $type, 'png');
        $displayText = $text ?? $value;

        return sprintf(
            '<div style="text-align:center;font-family:monospace;">
                <img src="data:image/png;base64,%s" alt="%s" style="display:block;margin:0 auto;">
                <div style="font-size:10px;margin-top:5px;">%s</div>
             </div>',
            base64_encode($barcodeImage),
            htmlspecialchars($value),
            htmlspecialchars($displayText)
        );
    }

    /**
     * Batch generate barcodes for multiple products
     *
     * @param  array  $products  Array of products with barcode/SKU
     * @return array Array of generated barcodes
     */
    public function batchGenerate(array $products): array
    {
        $results = [];

        foreach ($products as $product) {
            $barcodeValue = $product->barcode ?? $this->generateFromSKU($product->sku);

            try {
                $results[] = [
                    'product_id' => $product->id,
                    'barcode' => $barcodeValue,
                    'image_png' => $this->generate($barcodeValue, 'code128', 'png'),
                    'image_svg' => $this->generate($barcodeValue, 'code128', 'svg'),
                    'valid' => true,
                ];
            } catch (BarcodeException $e) {
                $results[] = [
                    'product_id' => $product->id,
                    'barcode' => $barcodeValue,
                    'error' => $e->getMessage(),
                    'valid' => false,
                ];
            }
        }

        return $results;
    }

    /**
     * Print barcode label to PDF (via DomPDF)
     *
     * @param  string  $barcodeValue  Barcode value
     * @param  string  $productName  Product name for display
     * @param  string  $sku  Product SKU
     * @param  float  $price  Product price
     * @param  string  $template  Template name (avery, thermal, custom)
     * @return View View instance
     */
    public function printLabel(
        string $barcodeValue,
        string $productName,
        string $sku,
        float $price = 0,
        string $template = 'thermal'
    ): View {
        $barcodeImage = $this->generate($barcodeValue, 'code128', 'png');

        $viewData = [
            'barcode' => $barcodeValue,
            'barcode_image' => base64_encode($barcodeImage),
            'product_name' => $productName,
            'sku' => $sku,
            'price' => $price,
        ];

        $viewPath = match ($template) {
            'avery' => 'products.labels.avery',
            'thermal' => 'products.labels.thermal',
            default => 'products.labels.default',
        };

        return view($viewPath, $viewData);
    }
}
