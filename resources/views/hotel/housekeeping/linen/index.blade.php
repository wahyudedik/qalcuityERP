<x-app-layout title="Linen Inventory">
    <x-slot name="header">Linen Inventory</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button onclick="openMovementModal()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Record Movement
            </button>
    </div>

    <div class="space-y-6">
        {{-- Filters --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="category" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">All Categories</option>
                    <option value="Bathroom" {{ request('category') === 'Bathroom' ? 'selected' : '' }}>Bathroom
                    </option>
                    <option value="Bedroom" {{ request('category') === 'Bedroom' ? 'selected' : '' }}>Bedroom</option>
                    <option value="Dining" {{ request('category') === 'Dining' ? 'selected' : '' }}>Dining</option>
                    <option value="Pool" {{ request('category') === 'Pool' ? 'selected' : '' }}>Pool</option>
                </select>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                        onchange="this.form.submit()" class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Show Low Stock Only</span>
                </label>
            </form>
        </div>

        {{-- Inventory Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Item</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Category</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Available</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                In Use</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Soiled</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $item->item_name }}</p>
                                        <p class="text-xs text-gray-600">{{ $item->item_code }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->category }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    {{ $item->available_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $item->in_use_quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $item->soiled_quantity }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full {{ $item->stock_status === 'out_of_stock'
                                            ? 'bg-red-100 text-red-700'
                                            : ($item->stock_status === 'low_stock'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-green-100 text-green-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->stock_status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openMovementModal({{ $item->id }}, '{{ $item->item_name }}')"
                                        class="text-xs text-blue-600 hover:underline">Record
                                        Movement</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500">No linen
                                    items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Movement Modal --}}
    <div id="modal-movement" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6">
            <form action="{{ route('hotel.housekeeping.linen.movement') }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Linen Movement</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Item *</label>
                        <select name="linen_inventory_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Movement Type
                            *</label>
                        <select name="movement_type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="transfer">Transfer to Room</option>
                            <option value="laundry_out">Send to Laundry</option>
                            <option value="laundry_in">Receive from Laundry</option>
                            <option value="damage">Mark as Damaged</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Quantity
                            *</label>
                        <input type="number" name="quantity" min="1" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                        <textarea name="reason" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeMovementModal()"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Record</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openMovementModal(itemId = null, itemName = '') {
                document.getElementById('modal-movement').classList.remove('hidden');
                if (itemId) {
                    document.querySelector('select[name="linen_inventory_id"]').value = itemId;
                }
            }

            function closeMovementModal() {
                document.getElementById('modal-movement').classList.add('hidden');
            }
        </script>
    @endpush
</x-app-layout>
