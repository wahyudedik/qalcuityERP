<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class BarcodeController extends Controller
{
    public function __construct(
        private BarcodeService $barcodeService
    ) {
    }

    /**
     * Show barcode for single product (preview)
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $barcodeValue = $product->barcode ?? $product->sku;
        $barcodeImage = $this->barcodeService->generate($barcodeValue, 'code128', 'png');

        return view('products.barcode-show', compact('product', 'barcodeImage', 'barcodeValue'));
    }

    /**
     * Print barcode labels for products
     */
    public function print(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'template' => 'in:avery,thermal,custom',
        ]);

        $productIds = $request->input('product_ids');
        $template = $request->input('template', 'thermal');

        $products = Product::whereIn('id', $productIds)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();

        if ($template === 'avery') {
            return $this->printAvery($products);
        } elseif ($template === 'custom') {
            return $this->printCustom($products);
        }

        return $this->printThermal($products);
    }

    /**
     * Print thermal labels (50mm x 25mm)
     */
    protected function printThermal($products)
    {
        $barcodes = [];

        foreach ($products as $product) {
            $barcodeValue = $product->barcode ?? $product->sku;
            $barcodeImage = $this->barcodeService->generate($barcodeValue, 'code128', 'png');

            $barcodes[] = [
                'value' => $barcodeValue,
                'image' => base64_encode($barcodeImage),
                'product' => $product,
            ];
        }

        $pdf = Pdf::loadView('products.labels.thermal', compact('barcodes'));
        $pdf->setPaper([0, 0, 141.73, 70.87]); // 50mm x 25mm in points
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);

        return $pdf->stream('barcode-labels.pdf');
    }

    /**
     * Print Avery A4 labels (21 labels per sheet)
     */
    protected function printAvery($products)
    {
        $barcodes = [];

        foreach ($products as $product) {
            $barcodeValue = $product->barcode ?? $product->sku;
            $barcodeImage = $this->barcodeService->generate($barcodeValue, 'code128', 'png');

            $barcodes[] = [
                'value' => $barcodeValue,
                'image' => base64_encode($barcodeImage),
                'product' => $product,
            ];
        }

        // Pad to multiple of 21 for proper sheet formatting
        while (count($barcodes) % 21 !== 0) {
            $barcodes[] = null;
        }

        $pdf = Pdf::loadView('products.labels.avery', compact('barcodes'));
        $pdf->setPaper('A4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        return $pdf->stream('avery-labels.pdf');
    }

    /**
     * Print custom label format
     */
    protected function printCustom($products)
    {
        // Similar to thermal but with custom dimensions
        return $this->printThermal($products);
    }

    /**
     * Auto-generate barcodes for products without barcode
     */
    public function autoGenerate(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->whereNull('barcode')
            ->orWhere('barcode', '')
            ->get();

        $updated = 0;
        foreach ($products as $product) {
            $barcode = $this->barcodeService->generateFromSKU($product->sku);
            $product->update(['barcode' => $barcode]);
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$updated} barcodes",
            'count' => $updated,
        ]);
    }
}
