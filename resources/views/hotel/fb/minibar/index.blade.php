<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Mini-bar Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Low Stock Alert --}}
            @if (count($lowStockRooms) > 0)
                <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-orange-700">
                                <strong>{{ count($lowStockRooms) }}</strong> rooms have low minibar stock that needs
                                attention.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Recent Transactions --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Consumption</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Room</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Guest</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Item</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Qty</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Charge</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->consumption_date->format('d/m/Y H:i') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Room {{ $transaction->room_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->reservation?->guest?->full_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->menuItem->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->quantity_consumed }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($transaction->total_charge, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $transaction->billing_status === 'billed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($transaction->billing_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No transactions yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex gap-4">
                <button onclick="openRestockModal()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Restock Minibar
                </button>

                <button onclick="openConsumptionModal()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    Record Consumption
                </button>
            </div>
        </div>
    </div>

    {{-- Restock Modal --}}
    <div id="modal-restock" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Restock Minibar</h3>
            </div>

            <form action="{{ route('hotel.fb.minibar.restock') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room
                            Number</label>
                        <input type="number" name="room_number" required
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                        <select name="menu_item_id" required
                            class="w-full rounded-md border-gray-300">
                            <option value="">Select Item</option>
                            <!-- Options loaded dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" min="1" required
                            class="w-full rounded-md border-gray-300">
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button" onclick="closeRestockModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">Restock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRestockModal() {
            document.getElementById('modal-restock').classList.remove('hidden');
        }

        function closeRestockModal() {
            document.getElementById('modal-restock').classList.add('hidden');
        }

        function openConsumptionModal() {
            // Similar implementation
            alert('Record consumption modal - implement similarly');
        }
    </script>
</x-app-layout>
