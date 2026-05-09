<?php

namespace App\Services;

use App\Models\Product;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductQrService
{
    /**
     * Build the QR payload URL for a given certificate number.
     */
    public function buildPayload(string $certificateNumber): string
    {
        return url('/verify/'.$certificateNumber);
    }

    /**
     * Generate a QR Code PNG for the given product.
     *
     * If $force=false and the product already has a QR code file, returns the existing path.
     * Otherwise generates a new 300x300 PNG, stores it, updates the product record, and returns the path.
     *
     * @param  bool  $force  Force regeneration even if QR already exists
     * @return string Relative path stored in products.qr_code_path
     */
    public function generate(Product $product, bool $force = false): string
    {
        // Return existing path if not forcing and file already exists
        if (! $force && $product->qr_code_path !== null && Storage::disk('public')->exists($product->qr_code_path)) {
            return $product->qr_code_path;
        }

        // Determine payload: use active certificate number if available, else product URL
        $activeCert = $product->activeCertificate;
        if ($activeCert !== null) {
            $payload = $this->buildPayload($activeCert->certificate_number);
        } else {
            $payload = url('/verify/product-'.$product->id);
        }

        // Generate QR Code PNG via BaconQrCode
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new ImagickImageBackEnd('png')
            );
            $writer = new Writer($renderer);
            $pngData = $writer->writeString($payload);
        } catch (\Throwable $e) {
            Log::error('QR Code generation failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to generate QR Code: '.$e->getMessage(), 0, $e);
        }

        // Store to storage/app/public/qr-codes/{tenant_id}/{product_id}.png
        $relativePath = 'qr-codes/'.$product->tenant_id.'/'.$product->id.'.png';

        try {
            Storage::disk('public')->put($relativePath, $pngData);
        } catch (\Throwable $e) {
            Log::error('QR Code file write failed', [
                'product_id' => $product->id,
                'path' => $relativePath,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to write QR Code file: '.$e->getMessage(), 0, $e);
        }

        // Update product record
        $product->qr_code_path = $relativePath;
        $product->save();

        return $relativePath;
    }

    /**
     * Delete the QR Code file for the given product and clear the path on the record.
     */
    public function delete(Product $product): void
    {
        if ($product->qr_code_path !== null) {
            Storage::disk('public')->delete($product->qr_code_path);
            $product->qr_code_path = null;
            $product->save();
        }
    }
}
