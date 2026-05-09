<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CertificateService;
use App\Services\ProductQrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductQrController extends Controller
{
    public function __construct(
        private ProductQrService $qrService,
        private CertificateService $certificateService,
    ) {}

    /**
     * POST /products/{product}/qr/generate
     *
     * Generate (or regenerate) the QR Code for a product.
     * Requirements: 1.1, 1.6
     */
    public function generate(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        $path = $this->qrService->generate($product, force: true);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil di-generate.',
            'qr_code_path' => $path,
        ]);
    }

    /**
     * GET /products/{product}/qr/download
     *
     * Stream the QR Code PNG as a file download.
     * Requirements: 1.8
     */
    public function download(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tenantId(), 403);

        if ($product->qr_code_path === null) {
            abort(404, 'QR Code belum di-generate untuk produk ini.');
        }

        return Storage::disk('public')->download(
            $product->qr_code_path,
            'qr-'.$product->sku.'.png'
        );
    }

    /**
     * POST /products/qr/print-labels
     *
     * Generate a PDF with QR labels for the selected products.
     * Auto-issues a certificate and generates a QR Code for products that don't have one.
     * Requirements: 5.1, 5.2, 5.3, 5.4
     */
    public function printLabels(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer',
            'format' => 'in:thermal,a4',
        ]);

        $format = $request->input('format', 'a4');
        $tenantId = $this->tenantId();

        $products = Product::whereIn('id', $request->input('product_ids'))
            ->where('tenant_id', $tenantId)
            ->get();

        $user = $this->authenticatedUser();

        // Auto-issue certificate and generate QR for products that need it
        foreach ($products as $product) {
            $activeCert = $product->activeCertificate;

            if ($activeCert === null) {
                // issue() internally calls qrService->generate(), so no separate call needed
                $this->certificateService->issue($product, $user);
                $product->refresh();
            } elseif ($product->qr_code_path === null) {
                $this->qrService->generate($product, force: true);
                $product->refresh();
            }
        }

        // Build label data array
        $labels = $products->map(function (Product $product) {
            $qrBase64 = null;
            if ($product->qr_code_path) {
                $raw = Storage::disk('public')->get($product->qr_code_path);
                if ($raw) {
                    $qrBase64 = base64_encode($raw);
                }
            }

            return [
                'product' => $product,
                'qr_image' => $qrBase64,
                'certificate_number' => optional($product->activeCertificate)->certificate_number,
            ];
        })->all();

        if ($format === 'thermal') {
            $pdf = Pdf::loadView('products.labels.qr-thermal', compact('labels'));
            $pdf->setPaper([0, 0, 141.73, 70.87]); // 50mm x 25mm in points
            $pdf->setOption('margin-top', 0);
            $pdf->setOption('margin-right', 0);
            $pdf->setOption('margin-bottom', 0);
            $pdf->setOption('margin-left', 0);
        } else {
            // Pad to multiple of 21 for proper A4 sheet formatting
            while (count($labels) % 21 !== 0) {
                $labels[] = null;
            }

            $pdf = Pdf::loadView('products.labels.qr-a4', compact('labels'));
            $pdf->setPaper('A4');
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-right', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);
        }

        return $pdf->download('qr-labels.pdf');
    }
}
