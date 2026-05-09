<?php

namespace App\Http\Controllers;

use App\Services\SalesAiService;
use Illuminate\Http\Request;

/**
 * SalesAiController — endpoint AJAX untuk AI contextual di fitur Sales & Invoice.
 *
 * GET  /sales/ai/price-suggest        — suggest harga produk untuk customer
 * GET  /sales/ai/late-payment-risk    — prediksi risiko telat bayar customer
 * GET  /sales/ai/item-description     — auto-draft deskripsi item dari produk
 */
class SalesAiController extends Controller
{
    public function __construct(protected SalesAiService $service) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    /**
     * Suggest harga berdasarkan histori transaksi customer + produk.
     * Query: ?customer_id=1&product_id=5&qty=2
     */
    public function priceSuggest(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'product_id' => 'required|integer',
            'qty' => 'nullable|numeric|min:0.001',
        ]);

        $result = $this->service->suggestPrice(
            tenantId: $this->tid(),
            customerId: (int) $request->customer_id,
            productId: (int) $request->product_id,
            qty: (float) ($request->qty ?? 1),
        );

        return response()->json($result);
    }

    /**
     * Prediksi risiko invoice telat dibayar untuk customer.
     * Query: ?customer_id=1
     */
    public function latePaymentRisk(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
        ]);

        $result = $this->service->predictLatePayment(
            tenantId: $this->tid(),
            customerId: (int) $request->customer_id,
        );

        return response()->json($result);
    }

    /**
     * Auto-draft deskripsi item dari nama produk.
     * Query: ?product_id=5
     */
    public function itemDescription(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $result = $this->service->draftItemDescription(
            tenantId: $this->tid(),
            productId: (int) $request->product_id,
        );

        return response()->json($result);
    }
}
