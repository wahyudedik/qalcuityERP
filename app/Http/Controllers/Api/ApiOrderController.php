<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrderItem;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiOrderController extends ApiBaseController
{
    public function index(Request $request)
    {
        $orders = SalesOrder::where('tenant_id', $this->tenantId())
            ->with(['customer', 'items.product'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from,   fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to,     fn($q) => $q->where('date', '<=', $request->to))
            ->latest()
            ->paginate(50);

        return $this->ok($orders);
    }

    public function show(int $id)
    {
        $order = SalesOrder::where('tenant_id', $this->tenantId())
            ->with(['customer', 'items.product'])
            ->findOrFail($id);

        return $this->ok($order);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'nullable|integer',
            'date'           => 'required|date',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $tenantId = $this->tenantId();

        DB::beginTransaction();
        try {
            $total = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['price']);

            $order = SalesOrder::create([
                'tenant_id'   => $tenantId,
                'customer_id' => $validated['customer_id'] ?? null,
                'number'      => 'SO-API-' . strtoupper(substr(uniqid(), -6)),
                'date'        => $validated['date'],
                'status'      => 'pending',
                'total'       => $total,
                'notes'       => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price'          => $item['price'],
                    'subtotal'       => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            // Fire webhook
            app(WebhookService::class)->dispatch($tenantId, 'order.created', $order->load('items')->toArray());

            return $this->created($order->load('items', 'customer'));

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal membuat order: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = SalesOrder::where('tenant_id', $this->tenantId())->findOrFail($id);

        $request->validate(['status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);

        app(WebhookService::class)->dispatch($this->tenantId(), 'order.status_changed', [
            'order_id' => $order->id,
            'number'   => $order->number,
            'status'   => $order->status,
        ]);

        return $this->ok($order);
    }
}
