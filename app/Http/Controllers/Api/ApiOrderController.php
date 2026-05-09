<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiOrderController extends ApiBaseController
{
    /**
     * BUG-SALES-001 FIX: Valid status transitions for Sales Orders
     *
     * Valid flow: pending → confirmed → processing → shipped → delivered
     * cancelled dan delivered adalah terminal states
     */
    private const VALID_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ['completed', 'cancelled'],
        'completed' => [], // Terminal state
        'cancelled' => [], // Terminal state
    ];

    /**
     * BUG-SALES-004 FIX: Validate customer credit limit before order creation
     *
     * @return array|null Error response if credit limit exceeded, null if OK
     */
    protected function validateCreditLimit(?int $customerId, float $orderAmount): ?array
    {
        if (! $customerId) {
            return null; // No customer, no credit check needed
        }

        $customer = Customer::find($customerId);
        if (! $customer) {
            return null; // Customer not found, will be caught by validation
        }

        // Check if customer would exceed credit limit
        if ($customer->wouldExceedCreditLimit($orderAmount)) {
            $available = number_format($customer->availableCredit(), 0, ',', '.');

            return [
                'status' => 'error',
                'code' => 'CREDIT_LIMIT_EXCEEDED',
                'message' => "Batas kredit pelanggan terlampaui. Kredit tersedia: Rp {$available}.",
                'data' => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'credit_limit' => $customer->credit_limit,
                    'outstanding_balance' => $customer->outstandingBalance(),
                    'available_credit' => $customer->availableCredit(),
                    'order_amount' => $orderAmount,
                ],
            ];
        }

        return null; // OK
    }

    public function index(Request $request)
    {
        $orders = SalesOrder::where('tenant_id', $this->tenantId())
            ->with(['customer', 'items.product'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q) => $q->where('date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('date', '<=', $request->to))
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
            'customer_id' => 'nullable|integer',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $tenantId = $this->tenantId();

        // BUG-SALES-004 FIX: Calculate order total for credit limit check
        $orderTotal = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

        // BUG-SALES-004 FIX: Validate credit limit before creating order
        $creditError = $this->validateCreditLimit($validated['customer_id'] ?? null, $orderTotal);
        if ($creditError) {
            return $this->error($creditError['message'], 422, $creditError['data'] ?? []);
        }

        DB::beginTransaction();
        try {
            $total = $orderTotal;

            $order = SalesOrder::create([
                'tenant_id' => $tenantId,
                'customer_id' => $validated['customer_id'] ?? null,
                'number' => 'SO-API-'.strtoupper(substr(uniqid(), -6)),
                'date' => $validated['date'],
                'status' => 'pending',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            // Fire webhook
            app(WebhookService::class)->dispatch($tenantId, 'order.created', $order->load('items')->toArray());

            return $this->created($order->load('items', 'customer'));

        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error('Gagal membuat order: '.$e->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = SalesOrder::where('tenant_id', $this->tenantId())->findOrFail($id);

        $request->validate(['status' => 'required|in:pending,confirmed,processing,shipped,delivered,completed,cancelled']);

        // BUG-SALES-001 FIX: Validate status transition
        $this->validateStatusTransition($order, $request->status);

        $order->update(['status' => $request->status]);

        app(WebhookService::class)->dispatch($this->tenantId(), 'order.status_changed', [
            'order_id' => $order->id,
            'number' => $order->number,
            'status' => $order->status,
        ]);

        return $this->ok($order);
    }

    /**
     * BUG-SALES-001 FIX: Validate Sales Order status transition for API
     */
    protected function validateStatusTransition(SalesOrder $order, string $newStatus): void
    {
        $currentStatus = $order->status;

        // Check if current status is known
        if (! isset(self::VALID_TRANSITIONS[$currentStatus])) {
            throw new \RuntimeException("Invalid current status: {$currentStatus}");
        }

        // Check if transition is allowed
        $allowedTransitions = self::VALID_TRANSITIONS[$currentStatus];

        if (empty($allowedTransitions)) {
            throw new \RuntimeException(
                "Status '{$currentStatus}' is a terminal state. Cannot transition to '{$newStatus}'."
            );
        }

        if (! in_array($newStatus, $allowedTransitions)) {
            $allowedList = implode(', ', $allowedTransitions);
            throw new \RuntimeException(
                "Invalid status transition from '{$currentStatus}' to '{$newStatus}'. ".
                "Allowed transitions: {$allowedList}"
            );
        }

        // Additional validation for cancelled status
        if ($newStatus === 'cancelled') {
            // Check if already has invoice
            if ($order->invoices()->where('status', '!=', 'cancelled')->exists()) {
                throw new \RuntimeException(
                    'Cannot cancel order with active invoices.'
                );
            }

            // Check if already delivered
            if ($order->status === 'delivered') {
                throw new \RuntimeException(
                    'Cannot cancel delivered order.'
                );
            }
        }
    }
}
