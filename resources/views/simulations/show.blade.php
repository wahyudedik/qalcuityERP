<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('simulations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">←</a>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $simulation->name }}</h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @php
            $results = $simulation->results ?? [];
            $fmt = fn($n) => 'Rp ' . number_format(abs($n ?? 0), 0, ',', '.');
        @endphp

        <!-- AI Narrative -->
        @if($simulation->ai_narrative)
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">🤖</span>
                    <div>
                        <p class="font-medium text-indigo-800 dark:text-indigo-200 text-sm mb-1">Analisis AI</p>
                        <p class="text-sm text-indigo-700 dark:text-indigo-300">{{ $simulation->ai_narrative }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- KPI Cards -->
        @if(!empty($results['formatted']))
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($results['formatted'] as $label => $value)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $label) }}</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Detail Results -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Detail Hasil Simulasi</h3>

            @if($simulation->scenario_type === 'price_increase')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Pendapatan Saat Ini</span>
                        <span class="font-medium">{{ $fmt($results['current_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi (tanpa elastisitas)</span>
                        <span class="font-medium text-green-600">{{ $fmt($results['projected_revenue_no_elasticity'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi (dengan elastisitas harga)</span>
                        <span class="font-medium text-blue-600">{{ $fmt($results['projected_revenue_with_elasticity'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Estimasi Perubahan Demand</span>
                        <span class="font-medium {{ ($results['demand_change_pct'] ?? 0) < 0 ? 'text-red-500' : 'text-green-500' }}">
                            {{ $results['demand_change_pct'] ?? 0 }}%
                        </span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'new_branch')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Biaya Tetap/Bulan</span>
                        <span class="font-medium">{{ $fmt($results['fixed_cost_monthly'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Omzet/Bulan</span>
                        <span class="font-medium">{{ $fmt($results['revenue_projection'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Laba Bersih ({{ $results['months'] ?? 12 }} bulan)</span>
                        <span class="font-medium {{ ($results['net_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ ($results['net_profit'] ?? 0) >= 0 ? '+' : '-' }}{{ $fmt($results['net_profit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Break-even</span>
                        <span class="font-medium">{{ $results['break_even_months'] ?? '-' }} bulan</span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'stock_out')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Total Potensi Kehilangan Omzet</span>
                        <span class="font-medium text-red-500">{{ $fmt($results['total_lost_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Rata-rata Kehilangan/Hari</span>
                        <span class="font-medium">{{ $fmt($results['daily_lost'] ?? 0) }}</span>
                    </div>
                    @if(!empty($results['products']))
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Produk yang Terdampak:</p>
                            <table class="w-full text-xs">
                                <thead><tr class="text-gray-500 dark:text-gray-400">
                                    <th class="text-left py-1">Produk</th>
                                    <th class="text-right py-1">Qty</th>
                                    <th class="text-right py-1">Omzet</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($results['products'] as $p)
                                        <tr class="border-t border-gray-100 dark:border-gray-700">
                                            <td class="py-1">{{ $p['name'] }}</td>
                                            <td class="text-right py-1">{{ number_format($p['qty']) }}</td>
                                            <td class="text-right py-1">{{ $fmt($p['revenue']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($simulation->scenario_type === 'cost_reduction')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Total Pengeluaran</span>
                        <span class="font-medium">{{ $fmt($results['total_expense'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Penghematan Biaya</span>
                        <span class="font-medium text-green-600">{{ $fmt($results['saved_cost'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Laba Sebelum Efisiensi</span>
                        <span class="font-medium {{ ($results['current_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['current_profit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Laba Setelah Efisiensi</span>
                        <span class="font-medium {{ ($results['new_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['new_profit'] ?? 0) }}
                        </span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'demand_change')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Pendapatan Saat Ini</span>
                        <span class="font-medium">{{ $fmt($results['current_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Pendapatan</span>
                        <span class="font-medium {{ ($results['projected_revenue'] ?? 0) >= ($results['current_revenue'] ?? 0) ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['projected_revenue'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Order Saat Ini</span>
                        <span class="font-medium">{{ number_format($results['current_orders'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Proyeksi Order</span>
                        <span class="font-medium">{{ number_format($results['projected_orders'] ?? 0) }}</span>
                    </div>
                    @if(!empty($results['stock_note']))
                        <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg text-xs text-yellow-700 dark:text-yellow-300">
                            📦 {{ $results['stock_note'] }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Parameters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3 text-sm">Parameter Input</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
                @foreach($simulation->parameters as $key => $val)
                    <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
