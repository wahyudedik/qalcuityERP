<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Room Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Active Orders --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Active Room Service Orders</h3>
                    <button onclick="openNewOrderModal()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        New Order
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Order #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Room</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Guest</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($activeOrders as $order)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Room {{ $order->room_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->guest?->full_name ?? 'N/A' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->status === 'ready'
                                            ? 'bg-green-100 text-green-800'
                                            : ($order->status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('hotel.fb.roomservice.orders.show', $order->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">View</a>

                                        @if ($order->status === 'ready')
                                            <form
                                                action="{{ route('hotel.fb.roomservice.orders.deliver', $order->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900">Deliver</button>
                                            </form>
                                        @endif

                                        @if ($order->status === 'served')
                                            <form action="{{ route('hotel.fb.roomservice.orders.charge', $order->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-orange-600 hover:text-orange-900">Charge
                                                    to Room</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No active orders
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- New Order Modal --}}
    <div id="modal-new-order" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Create Room Service Order</h3>
            </div>

            <form action="{{ route('hotel.fb.roomservice.orders.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">Reservation</label>
                        <select name="reservation_id" required
                            class="w-full rounded-md border-gray-300">
                            <option value="">Select Reservation</option>
                            <!-- Options loaded dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room
                            Number</label>
                        <input type="number" name="room_number" required
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order
                            Items</label>
                        <div id="order-items-container" class="space-y-2">
                            <!-- Dynamic items -->
                        </div>
                        <button type="button" onclick="addOrderItem()"
                            class="mt-2 text-sm text-indigo-600 hover:text-indigo-700">
                            + Add Item
                        </button>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button" onclick="closeNewOrderModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">Create
                        Order</button>
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
            container.insertAdjacentHTML('beforeend', `
                <div class="flex gap-2 items-center">
                    <select name="items[][menu_item_id]" required class="flex-1 rounded-md border-gray-300 text-sm">
                        <option value="">Select Item</option>
                    </select>
                    <input type="number" name="items[][quantity]" placeholder="Qty" min="1" value="1" required class="w-20 rounded-md border-gray-300 text-sm">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700">×</button>
                </div>
            `);
        }
    </script>
</x-app-layout>
