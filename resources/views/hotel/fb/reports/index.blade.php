@extends('layouts.app')

@section('title', 'F&B Reports & Analytics')

@section('content')
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">F&B Reports</h1>
                <p class="mt-1 text-sm text-gray-500">Comprehensive food & beverage analytics</p>
            </div>
            <div class="flex space-x-2">
                <form method="GET" class="flex space-x-2">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="self-center text-gray-500">to</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Filter
                    </button>
                </form>
                <a href="{{ route('hotel.fb.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Total Revenue</div>
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Total Orders</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_orders']) }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Avg Order Value</div>
                <div class="text-2xl font-bold">Rp {{ number_format($stats['avg_order_value'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Gross Profit</div>
                <div class="text-2xl font-bold {{ $stats['gross_profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format($stats['gross_profit'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="text-sm text-gray-600">Profit Margin</div>
                <div
                    class="text-2xl font-bold {{ $stats['profit_margin'] >= 30 ? 'text-green-600' : ($stats['profit_margin'] >= 20 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ number_format($stats['profit_margin'], 1) }}%
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue by Order Type -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Revenue by Order Type</h3>
                <div class="space-y-3">
                    @forelse($revenueByType as $type)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-indigo-500 mr-2"></div>
                                <span
                                    class="text-sm font-medium capitalize">{{ str_replace('_', ' ', $type->order_type) }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold">Rp {{ number_format($type->total, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">{{ $type->count }} orders</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No data available</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Selling Items -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Top 10 Selling Items</h3>
                <div class="space-y-3">
                    @forelse($topItems as $index => $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span
                                    class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold mr-2">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm">{{ $item->name }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold">{{ number_format($item->total_quantity) }} sold</div>
                                <div class="text-xs text-gray-500">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No sales data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Category Performance -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Category Performance</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Items Sold</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($categoryStats as $cat)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium">{{ $cat->category_name ?? 'Uncategorized' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right">{{ $cat->order_count }}</td>
                                    <td class="px-4 py-2 text-sm text-right">{{ number_format($cat->total_items_sold) }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right font-medium">Rp
                                        {{ number_format($cat->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500 text-sm">No data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Supply Usage & Costs -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Top Supply Usage</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supply</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Used</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Wasted</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($supplyUsage as $supply)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium">{{ $supply->supply_name }}</td>
                                    <td class="px-4 py-2 text-sm text-right">{{ number_format($supply->usage_qty, 2) }}
                                        {{ $supply->unit }}</td>
                                    <td
                                        class="px-4 py-2 text-sm text-right {{ $supply->waste_qty > 0 ? 'text-red-600' : '' }}">
                                        {{ number_format($supply->waste_qty, 2) }} {{ $supply->unit }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right font-medium">Rp
                                        {{ number_format($supply->total_cost, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500 text-sm">No usage data
                                        available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daily Revenue Trend Chart (Simple Visualization) -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">Daily Revenue Trend</h3>
            @if ($dailyRevenue->count() > 0)
                <div class="relative h-64">
                    <div class="absolute inset-0 flex items-end space-x-1">
                        @php
                            $maxRevenue = $dailyRevenue->max('total');
                        @endphp
                        @foreach ($dailyRevenue as $day)
                            @php
                                $height = $maxRevenue > 0 ? ($day->total / $maxRevenue) * 100 : 0;
                            @endphp
                            <div class="flex-1 flex flex-col items-center group">
                                <div class="w-full bg-indigo-500 hover:bg-indigo-600 transition-all rounded-t"
                                    style="height: {{ max($height, 2) }}%">
                                </div>
                                <div
                                    class="text-xs text-gray-500 mt-1 transform -rotate-45 origin-top-left whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d M') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No revenue data for selected period</p>
            @endif
        </div>
    </div>
@endsection
