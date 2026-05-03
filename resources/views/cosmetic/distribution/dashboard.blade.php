<x-app-layout>
    <x-slot name="header"><i class="fas fa-chart-line mr-2 text-green-600"></i>Distribution Channel Analytics</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('cosmetic.distribution.channel.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Add Channel
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Channel Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($channelStats as $stat)
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $stat['name'] }}</h3>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($stat['type']) }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Total Sales</span>
                                    <span class="text-lg font-bold text-green-600">Rp
                                        {{ number_format($stat['total_sales'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Units Sold</span>
                                    <span
                                        class="text-md font-semibold text-gray-900">{{ number_format($stat['total_quantity']) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Transactions</span>
                                    <span
                                        class="text-md font-semibold text-gray-900">{{ $stat['transaction_count'] }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="text-sm text-gray-500">Avg Order Value</span>
                                    <span class="text-md font-bold text-blue-600">Rp
                                        {{ number_format($stat['avg_order_value'], 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('cosmetic.distribution.channel.show', $stat['id']) }}"
                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-900">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                        <i class="fas fa-store text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500 text-lg">No distribution channels yet</p>
                        <a href="{{ route('cosmetic.distribution.channel.create') }}"
                            class="inline-flex items-center mt-4 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>Create First Channel
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Sales Trend -->
            @if ($salesTrend->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Sales Trend (Last 30 Days)</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach ($salesTrend->take(10) as $sale)
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm text-gray-600 w-24">{{ $sale->date }}</span>
                                    <div class="flex-1 mx-4">
                                        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                                            <div class="bg-green-600 h-full rounded-full transition-all"
                                                style="width: {{ min(100, ($sale->total / $salesTrend->max('total')) * 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-32 text-right">
                                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-gray-500 w-20 text-right">
                                        {{ $sale->quantity }} units
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
