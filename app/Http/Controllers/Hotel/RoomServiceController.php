<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\FbOrder;
use App\Models\MenuItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class RoomServiceController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $tenantId = $this->tenantId();

        $activeOrders = FbOrder::where('tenant_id', $tenantId)
            ->where('order_type', 'room_service')
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['guest', 'reservation.room'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hotel.fb.roomservice.index', compact('activeOrders'));
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_number' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string',
        ]);

        $validated['order_type'] = 'room_service';

        $order = $this->orderService->createOrder($validated);

        return redirect()->route('hotel.fb.roomservice.orders.show', $order->id)
            ->with('success', 'Room service order created');
    }

    public function showOrder(int $id)
    {
        $order = FbOrder::with(['items.menuItem', 'guest', 'reservation.room'])->findOrFail($id);
        return view('hotel.fb.roomservice.orders.show', compact('order'));
    }

    public function deliverOrder(int $id)
    {
        $order = $this->orderService->updateOrderStatus($id, 'served');
        return back()->with('success', 'Order marked as delivered');
    }

    public function chargeToRoom(int $id)
    {
        $order = $this->orderService->processPayment($id, 'room_charge');
        return back()->with('success', 'Charged to room successfully');
    }

    public function availableMenuItems()
    {
        $items = MenuItem::whereHas('menu', function ($q) {
            $q->where('type', 'room_service')
                ->where('is_active', true);
        })
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return response()->json(['items' => $items]);
    }
}
