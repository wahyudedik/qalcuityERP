<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\FbOrder;
use App\Models\MenuItem;
use App\Models\RestaurantMenu;
use App\Services\OrderService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $stats = [
            'today_orders' => FbOrder::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->whereIn('order_type', ['restaurant_dine_in', 'restaurant_takeaway'])
                ->count(),
            'pending_orders' => FbOrder::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->whereIn('order_type', ['restaurant_dine_in', 'restaurant_takeaway'])
                ->count(),
            'today_revenue' => FbOrder::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->whereIn('order_type', ['restaurant_dine_in', 'restaurant_takeaway'])
                ->sum('total_amount'),
        ];

        $recentOrders = FbOrder::where('tenant_id', $tenantId)
            ->whereIn('order_type', ['restaurant_dine_in', 'restaurant_takeaway'])
            ->with(['guest', 'items.menuItem'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('hotel.fb.restaurant.index', compact('stats', 'recentOrders'));
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:restaurant_dine_in,restaurant_takeaway',
            'guest_id' => 'nullable|exists:guests,id',
            'table_number' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string',
        ]);

        $order = $this->orderService->createOrder($validated);

        return redirect()->route('hotel.fb.restaurant.orders.show', $order->id)
            ->with('success', 'Order created successfully');
    }

    public function showOrder(int $id)
    {
        $order = FbOrder::with(['items.menuItem', 'guest', 'server'])->findOrFail($id);
        return view('hotel.fb.restaurant.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,served,completed,cancelled',
        ]);

        $order = $this->orderService->updateOrderStatus($id, $validated['status']);

        return back()->with('success', 'Order status updated');
    }

    public function menus()
    {
        $menus = RestaurantMenu::where('tenant_id', auth()->user()->current_tenant_id)
            ->withCount('items')
            ->orderBy('display_order')
            ->get();

        return view('hotel.fb.menus.index', compact('menus'));
    }

    public function menuItems(RestaurantMenu $menu)
    {
        $items = MenuItem::where('menu_id', $menu->id)
            ->orderBy('name')
            ->get();

        return view('hotel.fb.menus.items', compact('menu', 'items'));
    }

    /**
     * Store new menu
     */
    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,all_day,room_service,bar',
            'available_from' => 'nullable|date_format:H:i',
            'available_until' => 'nullable|date_format:H:i|after:available_from',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->current_tenant_id;

        RestaurantMenu::create($validated);

        return redirect()->route('hotel.fb.menus.index')
            ->with('success', 'Menu created successfully');
    }

    /**
     * Update menu
     */
    public function updateMenu(Request $request, RestaurantMenu $menu)
    {
        $this->authorizeAccess($menu);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,all_day,room_service,bar',
            'available_from' => 'nullable|date_format:H:i',
            'available_until' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->route('hotel.fb.menus.index')
            ->with('success', 'Menu updated successfully');
    }

    /**
     * Store menu item
     */
    public function storeMenuItem(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:restaurant_menus,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:1',
            'daily_limit' => 'nullable|integer|min:1',
            'is_available' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->current_tenant_id;
        $validated['cost'] = $validated['cost'] ?? 0;

        MenuItem::create($validated);

        return back()->with('success', 'Menu item added successfully');
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(Request $request, MenuItem $menuItem)
    {
        $this->authorizeAccess($menuItem);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:1',
            'daily_limit' => 'nullable|integer|min:1',
            'is_available' => 'boolean',
        ]);

        $validated['cost'] = $validated['cost'] ?? 0;

        $menuItem->update($validated);

        return back()->with('success', 'Menu item updated successfully');
    }

    /**
     * Delete menu item
     */
    public function destroyMenuItem(MenuItem $menuItem)
    {
        $this->authorizeAccess($menuItem);

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted');
    }

    /**
     * Authorize access to tenant resources
     */
    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== auth()->user()->current_tenant_id) {
            abort(403, 'Unauthorized access');
        }
    }
}
