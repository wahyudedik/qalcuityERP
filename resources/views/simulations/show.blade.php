<x-app-layout>
    <x-slot name="header">{{ $simulation->name }}</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @php
            $results = $simulation->results ?? [];
            $fmt = fn($n) => 'Rp ' . number_format(abs($n ?? 0), 0, ',', '.');
            $labels = [
                'price_increase' => '📈 Kenaikan Harga',
                'new_branch'     => '🏪 Cabang Baru',
                'stock_out'      => '📦 Stok Habis',
                'cost_reduction' => '✂️ Efisiensi Biaya',
                'demand_change'  => '📊 Perubahan Demand',
            ];
        @endphp

        {{-- Scenario badge --}}
        <div class="flex items-center gap-3 flex-wrap">
            <span class="px-3 py-1.5 bg-indigo-500/10 text-indigo-400 text-sm rounded-full border border-indigo-500/20 font-medium">
                {{ $labels[$simulation->scenario_type] ?? $simulation->scenario_type }}
            </span>
            <span class="text-xs text-gray-400">{{ $simulation->created_at->translatedFormat('d M Y H:i') }}</span>
        </div>

        <!-- AI Narrative -->
        @if($simulation->ai_narrative)
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 flex items-center justify-center text-lg shrink-0">🤖</div>
                    <div>
                        <p class="font-semibold text-indigo-800 text-sm mb-1">Analisis AI</p>
                        <p class="text-sm text-indigo-700 leading-relaxed whitespace-pre-line">{{ $simulation->ai_narrative }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══ BEFORE / AFTER COMPARISON CHART ══════════════════════ --}}
        @php
            $chartBefore = [];
            $chartAfter  = [];
            $chartLabels = [];

            if ($simulation->scenario_type === 'price_increase') {
                $chartLabels = ['Pendapatan', 'Demand (unit)'];
                $chartBefore = [$results['current_revenue'] ?? 0, $results['current_orders'] ?? 0];
                $chartAfter  = [$results['projected_revenue_with_elasticity'] ?? 0, round(($results['current_orders'] ?? 0) * (1 + ($results['demand_change_pct'] ?? 0) / 100))];
            } elseif ($simulation->scenario_type === 'cost_reduction') {
                $chartLabels = ['Pengeluaran', 'Laba Bersih'];
                $chartBefore = [$results['total_expense'] ?? 0, $results['current_profit'] ?? 0];
                $chartAfter  = [($results['total_expense'] ?? 0) - ($results['saved_cost'] ?? 0), $results['new_profit'] ?? 0];
            } elseif ($simulation->scenario_type === 'demand_change') {
                $chartLabels = ['Pendapatan', 'Jumlah Order'];
                $chartBefore = [$results['current_revenue'] ?? 0, $results['current_orders'] ?? 0];
                $chartAfter  = [$results['projected_revenue'] ?? 0, $results['projected_orders'] ?? 0];
            } elseif ($simulation->scenario_type === 'new_branch') {
                $months = $results['months'] ?? 12;
                $chartLabels = ['Biaya Total', 'Omzet Total', 'Laba Bersih'];
                $chartBefore = [0, 0, 0];
                $chartAfter  = [($results['fixed_cost_monthly'] ?? 0) * $months, ($results['revenue_projection'] ?? 0) * $months, $results['net_profit'] ?? 0];
            } elseif ($simulation->scenario_type === 'stock_out') {
                $chartLabels = ['Omzet Normal', 'Kehilangan Omzet'];
                $chartBefore = [($results['total_lost_revenue'] ?? 0) + ($results['daily_lost'] ?? 0) * ($simulation->parameters['days'] ?? 30), 0];
                $chartAfter  = [($results['daily_lost'] ?? 0) * ($simulation->parameters['days'] ?? 30), $results['total_lost_revenue'] ?? 0];
            }
        @endphp

        @if(!empty($chartLabels))
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 text-sm mb-4">Perbandingan Sebelum vs Sesudah</h3>
            <div class="h-64">
                <canvas id="comparison-chart"></canvas>
            </div>
        </div>
        @endif

        <!-- Detail Results -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Detail Hasil Simulasi</h3>

            @if($simulation->scenario_type === 'price_increase')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Pendapatan Saat Ini</span>
                        <span class="font-medium">{{ $fmt($results['current_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Proyeksi (tanpa elastisitas)</span>
                        <span class="font-medium text-green-600">{{ $fmt($results['projected_revenue_no_elasticity'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Proyeksi (dengan elastisitas harga)</span>
                        <span class="font-medium text-blue-600">{{ $fmt($results['projected_revenue_with_elasticity'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Estimasi Perubahan Demand</span>
                        <span class="font-medium {{ ($results['demand_change_pct'] ?? 0) < 0 ? 'text-red-500' : 'text-green-500' }}">
                            {{ $results['demand_change_pct'] ?? 0 }}%
                        </span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'new_branch')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Biaya Tetap/Bulan</span>
                        <span class="font-medium">{{ $fmt($results['fixed_cost_monthly'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Proyeksi Omzet/Bulan</span>
                        <span class="font-medium">{{ $fmt($results['revenue_projection'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Laba Bersih ({{ $results['months'] ?? 12 }} bulan)</span>
                        <span class="font-medium {{ ($results['net_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ ($results['net_profit'] ?? 0) >= 0 ? '+' : '-' }}{{ $fmt($results['net_profit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Break-even</span>
                        <span class="font-medium">{{ $results['break_even_months'] ?? '-' }} bulan</span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'stock_out')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Total Potensi Kehilangan Omzet</span>
                        <span class="font-medium text-red-500">{{ $fmt($results['total_lost_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Rata-rata Kehilangan/Hari</span>
                        <span class="font-medium">{{ $fmt($results['daily_lost'] ?? 0) }}</span>
                    </div>
                    @if(!empty($results['products']))
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-500 mb-2">Produk yang Terdampak:</p>
                            <table class="w-full text-xs">
                                <thead><tr class="text-gray-500">
                                    <th class="text-left py-1">Produk</th>
                                    <th class="text-right py-1">Qty</th>
                                    <th class="text-right py-1">Omzet</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($results['products'] as $p)
                                        <tr class="border-t border-gray-100">
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
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Total Pengeluaran</span>
                        <span class="font-medium">{{ $fmt($results['total_expense'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Penghematan Biaya</span>
                        <span class="font-medium text-green-600">{{ $fmt($results['saved_cost'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Laba Sebelum Efisiensi</span>
                        <span class="font-medium {{ ($results['current_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['current_profit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Laba Setelah Efisiensi</span>
                        <span class="font-medium {{ ($results['new_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['new_profit'] ?? 0) }}
                        </span>
                    </div>
                </div>

            @elseif($simulation->scenario_type === 'demand_change')
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Pendapatan Saat Ini</span>
                        <span class="font-medium">{{ $fmt($results['current_revenue'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Proyeksi Pendapatan</span>
                        <span class="font-medium {{ ($results['projected_revenue'] ?? 0) >= ($results['current_revenue'] ?? 0) ? 'text-green-600' : 'text-red-500' }}">
                            {{ $fmt($results['projected_revenue'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Order Saat Ini</span>
                        <span class="font-medium">{{ number_format($results['current_orders'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Proyeksi Order</span>
                        <span class="font-medium">{{ number_format($results['projected_orders'] ?? 0) }}</span>
                    </div>
                    @if(!empty($results['stock_note']))
                        <div class="mt-2 p-3 bg-yellow-50 rounded-lg text-xs text-yellow-700">
                            📦 {{ $results['stock_note'] }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Parameters -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Parameter Input</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
                @foreach($simulation->parameters as $key => $val)
                    <div class="flex justify-between py-1.5 border-b border-gray-100">
                        <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="font-medium text-gray-900">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('simulations.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali ke daftar</a>
            <form method="POST" action="{{ route('simulations.destroy', $simulation) }}" onsubmit="return confirm('Hapus simulasi ini?')">
                @csrf @method('DELETE')
                <button class="text-sm text-red-400 hover:text-red-600">Hapus simulasi</button>
            </form>
        </div>
    </div>

    @if(!empty($chartLabels))
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('comparison-chart');
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains('dark');
        const labels = @json($chartLabels);
        const before = @json($chartBefore);
        const after  = @json($chartAfter);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sebelum',
                        data: before,
                        backgroundColor: isDark ? 'rgba(148,163,184,0.4)' : 'rgba(148,163,184,0.6)',
                        borderColor: 'rgba(148,163,184,0.8)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Sesudah (Proyeksi)',
                        data: after,
                        backgroundColor: isDark ? 'rgba(99,102,241,0.5)' : 'rgba(99,102,241,0.7)',
                        borderColor: 'rgba(99,102,241,0.9)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const v = ctx.raw;
                                if (Math.abs(v) >= 1000) return ctx.dataset.label + ': Rp ' + Math.round(v).toLocaleString('id-ID');
                                return ctx.dataset.label + ': ' + v;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDark ? '#475569' : '#94a3b8',
                            callback: function(v) {
                                if (Math.abs(v) >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'jt';
                                if (Math.abs(v) >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'rb';
                                return v;
                            }
                        },
                        grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
    </script>
    @endpush
    @endif
</x-app-layout>
