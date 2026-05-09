<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\TransactionChainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionChainController extends Controller
{
    /**
     * Mapping type string → model class.
     */
    private array $typeMap = [
        'quotation' => Quotation::class,
        'sales_order' => SalesOrder::class,
        'delivery_order' => DeliveryOrder::class,
        'invoice' => Invoice::class,
        'payment' => Payment::class,
        'journal_entry' => JournalEntry::class,
        'purchase_order' => PurchaseOrder::class,
        'goods_receipt' => GoodsReceipt::class,
        'payable' => Payable::class,
    ];

    /**
     * Tampilkan rantai transaksi untuk sebuah dokumen.
     *
     * GET /transaction-chain/{type}/{id}
     */
    public function show(Request $request, string $type, int $id): JsonResponse
    {
        $modelClass = $this->typeMap[$type] ?? null;
        abort_if($modelClass === null, 404);

        $tenantId = auth()->user()->tenant_id;

        // Filter by tenant_id BEFORE loading the record — prevents data exposure
        $model = $modelClass::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $chain = app(TransactionChainService::class)->buildChain($modelClass, $id);

        return response()->json($chain);
    }

    /**
     * AJAX: ambil timeline JSON untuk sebuah transaksi (legacy support).
     */
    public function timeline(string $type, int $id): JsonResponse
    {
        $modelClass = $this->typeMap[$type] ?? null;
        abort_if($modelClass === null, 404);

        $tenantId = auth()->user()->tenant_id;

        // Filter by tenant_id BEFORE loading the record — prevents data exposure
        $model = $modelClass::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $chain = app(TransactionChainService::class)->buildChain($modelClass, $id);

        return response()->json([
            'model' => [
                'type' => $type,
                'id' => $model->id,
                'number' => $model->number ?? ('#'.$model->id),
            ],
            'timeline' => $chain->all,
        ]);
    }
}
