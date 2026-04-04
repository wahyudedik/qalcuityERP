@extends('layouts.app')

@section('title', 'F&B Inventory Management')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">F&B Inventory</h1>
                <p class="mt-1 text-sm text-gray-500">Manage kitchen supplies and ingredients</p>
            </div>
            <button onclick="document.getElementById('addSupplyModal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Add New Supply
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Total Supplies</div>
                <div class="text-2xl font-bold">{{ $stats['total_supplies'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Low Stock Items</div>
                <div class="text-2xl font-bold {{ $stats['low_stock_count'] > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                    {{ $stats['low_stock_count'] }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Out of Stock</div>
                <div class="text-2xl font-bold {{ $stats['out_of_stock'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $stats['out_of_stock'] }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Inventory Value</div>
                <div class="text-2xl font-bold">Rp {{ number_format($stats['total_inventory_value'], 0, ',', '.') }}</div>
            </div>
        </div>

        @if ($lowStock->count() > 0)
            <!-- Low Stock Alert -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Low Stock Alert</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($lowStock as $item)
                                    <li>{{ $item->name }} - Current: {{ $item->current_stock }} {{ $item->unit }} (Min:
                                        {{ $item->minimum_stock }})</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Supplies Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supply Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost/Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($supplies as $supply)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supply->name }}</div>
                                @if ($supply->supplier_name)
                                    <div class="text-xs text-gray-500">{{ $supply->supplier_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supply->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                {{ number_format($supply->current_stock, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($supply->minimum_stock, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rp {{ number_format($supply->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($supply->stock_status == 'out_of_stock')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                @elseif($supply->stock_status == 'low_stock')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Low Stock
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="openRestockModal({{ $supply->id }}, '{{ $supply->name }}')"
                                    class="text-indigo-600 hover:text-indigo-900">Restock</button>
                                <a href="{{ route('hotel.fb.supplies.transactions', $supply) }}"
                                    class="text-blue-600 hover:text-blue-900">History</a>
                                <button onclick="openUsageModal({{ $supply->id }}, '{{ $supply->name }}')"
                                    class="text-orange-600 hover:text-orange-900">Usage</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No supplies found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-6 py-4">
                {{ $supplies->links() }}
            </div>
        </div>
    </div>

    <!-- Add Supply Modal -->
    <div id="addSupplyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium mb-4">Add New Supply</h3>
            <form action="{{ route('hotel.fb.supplies.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit</label>
                        <select name="unit" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="liter">Liter</option>
                            <option value="box">Box</option>
                            <option value="pack">Pack</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Initial Stock</label>
                        <input type="number" name="current_stock" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Minimum Stock</label>
                        <input type="number" name="minimum_stock" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost per Unit</label>
                        <input type="number" name="cost_per_unit" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier (Optional)</label>
                        <input type="text" name="supplier_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('addSupplyModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium mb-4">Restock: <span id="restockItemName"></span></h3>
            <form id="restockForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity to Add</label>
                        <input type="number" name="quantity" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Cost per Unit (Optional)</label>
                        <input type="number" name="unit_cost" step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference (PO# etc.)</label>
                        <input type="text" name="reference"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Usage Modal -->
    <div id="usageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium mb-4">Record Usage: <span id="usageItemName"></span></h3>
            <form id="usageForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="transaction_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="usage">Kitchen Usage</option>
                            <option value="waste">Waste/Spoilage</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('usageModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg">Record</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRestockModal(supplyId, supplyName) {
            document.getElementById('restockItemName').textContent = supplyName;
            document.getElementById('restockForm').action = `/hotel/fb/supplies/${supplyId}/add-stock`;
            document.getElementById('restockModal').classList.remove('hidden');
        }

        function openUsageModal(supplyId, supplyName) {
            document.getElementById('usageItemName').textContent = supplyName;
            document.getElementById('usageForm').action = `/hotel/fb/supplies/${supplyId}/usage`;
            document.getElementById('usageModal').classList.remove('hidden');
        }
    </script>
@endsection
