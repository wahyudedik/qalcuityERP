<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">📊 Inventory Reports</h1>
                <p class="text-sm text-gray-500 mt-0.5">Medical inventory analytics and insights</p>
            </div>
            <button onclick="window.print()"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                🖨️ Print
            </button>
        </div>
    </x-slot>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_items'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Items</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-emerald-600">Rp
                {{ number_format($stats['total_value'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Value</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['low_stock'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Low Stock Items</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['expiring_soon'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Expiring Soon</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Inventory by Category --}}
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h5 class="text-sm font-semibold text-gray-900">📦 Inventory by Category</h5>
            </div>
            <div class="p-5 overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2">Items</th>
                            <th class="px-3 py-2">Value</th>
                            <th class="px-3 py-2">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categoryStats ?? [] as $cat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-900">{{ $cat['name'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $cat['items'] }}</td>
                                <td class="px-3 py-2 text-gray-700">Rp {{ number_format($cat['value'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ $cat['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600 w-8">{{ $cat['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-400">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Critical Stock Alerts --}}
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h5 class="text-sm font-semibold text-gray-900">⚠️ Critical Stock Alerts</h5>
            </div>
            <div class="p-5 overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Item</th>
                            <th class="px-3 py-2">Current</th>
                            <th class="px-3 py-2">Min. Stock</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($criticalStock ?? [] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 font-bold text-red-600">{{ $item['stock'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['min_stock'] }}</td>
                                <td class="px-3 py-2">
                                    @if ($item['stock'] == 0)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out
                                            of Stock</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Critical</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-400">All items well stocked
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Stock Movement --}}
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h5 class="text-sm font-semibold text-gray-900">📈 Stock Movement (Last 7 Days)</h5>
            </div>
            <div class="p-5 overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Received</th>
                            <th class="px-3 py-2">Used</th>
                            <th class="px-3 py-2">Net Change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stockMovement ?? [] as $day)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-700">{{ $day['date'] }}</td>
                                <td class="px-3 py-2 text-emerald-600">+{{ $day['received'] }}</td>
                                <td class="px-3 py-2 text-red-600">-{{ $day['used'] }}</td>
                                <td class="px-3 py-2">
                                    @php $net = $day['received'] - $day['used']; @endphp
                                    <span class="font-bold {{ $net >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $net >= 0 ? '+' : '' }}{{ $net }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-400">No movement data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Expiration Tracking --}}
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h5 class="text-sm font-semibold text-gray-900">📅 Expiration Tracking</h5>
            </div>
            <div class="p-5 overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Item</th>
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Expiry Date</th>
                            <th class="px-3 py-2">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($expiringItems ?? [] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-3 py-2"><code
                                        class="text-xs bg-gray-100 px-1 rounded">{{ $item['batch'] }}</code></td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['quantity'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $item['expiry_date'] }}</td>
                                <td class="px-3 py-2">
                                    @php $days = $item['days_left']; @endphp
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $days <= 7 ? 'bg-red-100 text-red-700' : ($days <= 30 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $days }} days
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-400">No items expiring soon
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
