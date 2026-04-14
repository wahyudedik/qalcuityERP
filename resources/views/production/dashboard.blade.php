<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Production Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Real-time production monitoring & analytics</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('production.gantt.index') }}"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-chart-gantt mr-2"></i>Gantt Chart
                </a>
                <a href="{{ route('production.index') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-list mr-2"></i>Work Orders
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Work Orders</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ $stats['total_work_orders'] }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-check mr-1"></i>{{ $stats['this_month_completed'] }} this month
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-industry text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['in_progress'] }}</p>
                            <p class="text-xs text-orange-600 mt-1">
                                <i class="fas fa-clock mr-1"></i>{{ $stats['pending'] }} pending
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cog fa-spin text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['completed'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                Yield: {{ $performance['avg_yield_rate'] }}%
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Overdue</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['overdue'] }}</p>
                            <p class="text-xs text-red-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Needs attention
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Yield Rate</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-3xl font-bold text-green-600">{{ $performance['avg_yield_rate'] }}%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-green-600">
                                    {{ $performance['avg_yield_rate'] >= 95 ? '<i class="fas fa-check mr-1"></i>Excellent' : ($performance['avg_yield_rate'] >= 85 ? '<i class="fas fa-exclamation-circle mr-1"></i>Good' : '<i class="fas fa-times mr-1"></i>Needs Improvement') }}
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-gray-700">
                            <div style="width:{{ $performance['avg_yield_rate'] }}%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Efficiency Rate</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-3xl font-bold text-blue-600">{{ $performance['avg_efficiency'] }}%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600">
                                    Planned vs Actual
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-gray-700">
                            <div style="width:{{ $performance['avg_efficiency'] }}%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Waste Cost</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Scrap</span>
                            <span class="font-semibold text-red-600">Rp
                                {{ number_format($performance['total_scrap_cost'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 dark:text-slate-400">Rework</span>
                            <span class="font-semibold text-orange-600">Rp
                                {{ number_format($performance['total_rework_cost'], 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="border-t border-gray-200 dark:border-slate-700 pt-2 flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">Total</span>
                            <span class="text-lg font-bold text-red-600">Rp
                                {{ number_format($performance['total_waste_cost'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Priority Distribution --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Priority Distribution</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-red-600"></i></div>
                        <p class="text-2xl font-bold text-red-600">{{ $priorityDist['urgent'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Urgent</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-orange-600"></i></div>
                        <p class="text-2xl font-bold text-orange-600">{{ $priorityDist['high'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">High</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-blue-600"></i></div>
                        <p class="text-2xl font-bold text-blue-600">{{ $priorityDist['normal'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Normal</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-gray-600"></i></div>
                        <p class="text-2xl font-bold text-gray-600">{{ $priorityDist['low'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Low</p>
                    </div>
                </div>
            </div>

            {{-- Overdue Work Orders --}}
            @if ($overdueOrders->isNotEmpty())
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-6 border-2 border-red-200 dark:border-red-800">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-4">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Overdue Work Orders
                        ({{ $overdueOrders->count() }})
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="text-xs text-red-700 dark:text-red-300 uppercase bg-red-100 dark:bg-red-900/40">
                                <tr>
                                    <th class="px-4 py-2 text-left">WO Number</th>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left">Planned End</th>
                                    <th class="px-4 py-2 text-left">Days Overdue</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-200 dark:divide-red-800">
                                @foreach ($overdueOrders as $wo)
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $wo->number }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                            {{ $wo->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-red-600">
                                            {{ $wo->planned_end_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-600 text-white rounded">
                                                {{ now()->diffInDays($wo->planned_end_date) }} days
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 rounded">
                                                {{ ucfirst(str_replace('_', ' ', $wo->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Recent Work Orders & Top Products --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Recent Work Orders --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Work Orders</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentOrders as $wo)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $wo->number }}</span>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded 
                            {{ $wo->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                            {{ $wo->status === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                            {{ $wo->status === 'pending' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $wo->status)) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $wo->product?->name ?? '-' }}
                                </p>
                                <div
                                    class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Target: {{ number_format($wo->target_quantity, 0) }}
                                        {{ $wo->unit }}</span>
                                    <span>Progress: {{ $wo->progress_percent }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                No work orders yet
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Top Products --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products by Volume</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @forelse($topProducts as $index => $product)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $index + 1 }}. {{ $product->product?->name ?? 'Unknown' }}
                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ number_format($product->total_quantity, 0) }} units
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: {{ $topProducts->first()->total_quantity > 0 ? ($product->total_quantity / $topProducts->first()->total_quantity) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $product->order_count }}
                                    orders
                                </p>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                No production data yet
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
