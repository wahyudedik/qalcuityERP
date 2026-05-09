<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryAiService;
use Illuminate\Http\Request;

class InventoryAiController extends Controller
{
    public function __construct(private InventoryAiService $ai) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    /**
     * GET /inventory/ai/stockout/{product}
     * Prediksi kapan stok produk akan habis.
     */
    public function stockoutPrediction(Product $product)
    {
        abort_unless($product->tenant_id === $this->tid(), 403);

        $prediction = $this->ai->predictStockout($this->tid(), $product->id);

        return response()->json(['prediction' => $prediction]);
    }

    /**
     * GET /inventory/ai/reorder/{product}?lead_time=7
     * Suggest jumlah reorder berdasarkan pola penjualan.
     */
    public function reorderSuggest(Request $request, Product $product)
    {
        abort_unless($product->tenant_id === $this->tid(), 403);

        $leadTime = (int) $request->input('lead_time', 7);
        $leadTime = max(1, min(60, $leadTime));
        $suggestion = $this->ai->suggestReorderQty($this->tid(), $product->id, $leadTime);

        return response()->json(['suggestion' => $suggestion]);
    }

    /**
     * GET /inventory/ai/analyze-all
     * Analisis batch semua produk aktif — untuk kolom AI di tabel inventory.
     */
    public function analyzeAll()
    {
        $products = Product::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->with('productStocks')
            ->get();

        $analysis = $this->ai->analyzeAllProducts($this->tid(), $products);

        return response()->json(['analysis' => $analysis]);
    }
}
