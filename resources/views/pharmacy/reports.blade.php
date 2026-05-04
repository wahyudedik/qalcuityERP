<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-chart-line text-blue-600"></i> Pharmacy Reports
            </h1>
            <p class="text-gray-500">Pharmacy analytics and performance reports</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                        <div class="w-full md:w-1/4">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="w-full md:w-1/4">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="w-full md:w-1/6">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-400">
                <div class="p-5 text-center">
                    <h3 class="text-blue-600">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-gray-500">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['total_prescriptions'] ?? 0 }}</h3>
                    <small class="text-gray-500">Prescriptions</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['low_stock_items'] ?? 0 }}</h3>
                    <small class="text-gray-500">Low Stock Items</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['expired_items'] ?? 0 }}</h3>
                    <small class="text-gray-500">Expired Items</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-pills"></i> Top 10 Medications
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Medication</th>
                                    <th>Quantity</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topMedications ?? [] as $index => $med)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $med['name'] }}</td>
                                        <td>{{ $med['quantity'] }}</td>
                                        <td>Rp {{ number_format($med['revenue'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current Stock</th>
                                    <th>Min. Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><strong class="text-red-600">{{ $item['stock'] }}</strong></td>
                                        <td>{{ $item['min_stock'] }}</td>
                                        <td>
                                            @if ($item['stock'] == 0)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Out of Stock</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Low Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">All items well stocked</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-times"></i> Expiring Soon (30 days)
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Batch #</th>
                                    <th>Stock</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringItems ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><code>{{ $item['batch_number'] }}</code></td>
                                        <td>{{ $item['stock'] }}</td>
                                        <td>{{ $item['expiry_date'] }}</td>
                                        <td>
                                            @php
                                                $days = $item['days_left'];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $days <= 7 ? 'red-500' : ($days <= 14 ? 'amber-500' : 'sky-500')  }}">
                                                {{ $days }} days
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-400">No items expiring soon</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
