<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
            {{ __('Restaurant POS') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Today's Orders</h3>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['today_orders'] ?? 0 }}</p>
                </div>

                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Pending Orders</h3>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $stats['pending_orders'] ?? 0 }}</p>
                </div>

                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Today's Revenue</h3>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">Rp
                        {{ number_format($stats['today_revenue'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Recent Orders Table --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-slate-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Order #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Guest</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Time</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ str_replace('_', ' ', ucfirst($order->order_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        {{ $order->guest?->full_name ?? 'Walk-in' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->status === 'completed'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : ($order->status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        {{ $order->created_at->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('hotel.fb.restaurant.orders.show', $order->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                        No orders yet today
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-6 flex gap-4">
                <button onclick="openNewOrderModal()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Order
                </button>

                <a href="{{ route('hotel.fb.menus.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Manage Menus
                </a>
            </div>
        </div>
    </div>

    {{-- New Order Modal --}}
    <div id="modal-new-order" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Order</h3>
            </div>

            <form action="{{ route('hotel.fb.restaurant.orders.store') }}" method="POST" id="new-order-form">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Order
                            Type</label>
                        <select name="order_type" required
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="restaurant_dine_in">Dine In</option>
                            <option value="restaurant_takeaway">Takeaway</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Table Number
                            (Optional)</label>
                        <input type="number" name="table_number"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Special
                            Instructions</label>
                        <textarea name="special_instructions" rows="2"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Order
                            Items</label>
                        <div id="order-items-container" class="space-y-2">
                            <!-- Items will be added here dynamically -->
                        </div>
                        <button type="button" onclick="addOrderItem()"
                            class="mt-2 text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                            + Add Item
                        </button>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" onclick="closeNewOrderModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-md">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openNewOrderModal() {
            document.getElementById('modal-new-order').classList.remove('hidden');
        }

        function closeNewOrderModal() {
            document.getElementById('modal-new-order').classList.add('hidden');
        }

        function addOrderItem() {
            const container = document.getElementById('order-items-container');
            const itemHtml = `
                <div class="flex gap-2 items-center order-item">
                    <select name="items[][menu_item_id]" required class="flex-1 rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="">Select Item</option>
                        <!-- Options will be loaded via AJAX -->
                    </select>
                    <input type="number" name="items[][quantity]" placeholder="Qty" min="1" value="1" required 
                           class="w-20 rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white text-sm">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHtml);
        }
    </script>
</x-app-layout>
