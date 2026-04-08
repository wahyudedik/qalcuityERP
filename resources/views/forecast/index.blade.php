<x-app-layout>
    <x-slot name="header">AI Forecasting Dashboard</x-slot>

    {{-- Period selector --}}
    <div class="flex items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-500 dark:text-slate-400">Proyeksi:</label>
            <select name="months" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                @foreach ([3 => '3 Bulan', 6 => '6 Bulan', 12 => '12 Bulan'] as $v => $l)
                    <option value="{{ $v }}" @selected($months == $v)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
        <p class="text-xs text-gray-400 dark:text-slate-500">Data historis 6 bulan terakhir + proyeksi
            {{ $months }} bulan ke depan</p>
    </div>

    {{-- Revenue Forecast Chart --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Proyeksi Revenue</h2>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    {{-- Cash Flow Forecast Chart --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Proyeksi Cash Flow</h2>
        <canvas id="cashFlowChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- AR Forecast --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🏦 Piutang & Collection</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Piutang</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Rp
                        {{ number_format($receivables['aging']->total ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Collection Rate (3bln)</p>
                    <p
                        class="text-lg font-bold {{ ($receivables['collection_rate'] ?? 0) >= 80 ? 'text-green-500' : 'text-amber-500' }}">
                        {{ $receivables['collection_rate'] }}%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Est. Tertagih</p>
                    <p class="text-lg font-bold text-blue-500">Rp
                        {{ number_format($receivables['estimated_collection'], 0, ',', '.') }}</p>
                </div>
            </div>
            <canvas id="agingChart" height="120"></canvas>
        </div>

        {{-- Demand Forecast --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">📦 Demand Forecast (Top 10)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400">
                        <tr>
                            <th class="text-left py-2">Produk</th>
                            <th class="text-right py-2">Avg/bln</th>
                            <th class="text-right py-2">Stok</th>
                            <th class="text-right py-2">Bulan</th>
                            <th class="text-center py-2">Tren</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($demand as $d)
                            <tr>
                                <td class="py-2 text-gray-900 dark:text-white">{{ Str::limit($d['product_name'], 20) }}
                                </td>
                                <td class="py-2 text-right text-gray-700 dark:text-slate-300">
                                    {{ number_format($d['monthly_avg']) }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-slate-300">
                                    {{ number_format($d['current_stock']) }}</td>
                                <td
                                    class="py-2 text-right font-medium {{ ($d['months_of_stock'] ?? 0) < 1 ? 'text-red-500' : (($d['months_of_stock'] ?? 0) < 2 ? 'text-amber-500' : 'text-green-500') }}">
                                    {{ $d['months_of_stock'] !== null ? $d['months_of_stock'] . ' bln' : '-' }}
                                </td>
                                <td class="py-2 text-center">
                                    @if ($d['trend'] > 5)
                                        <span class="text-green-500">↑{{ $d['trend'] }}%</span>
                                    @elseif($d['trend'] < -5)
                                        <span class="text-red-500">↓{{ abs($d['trend']) }}%</span>
                                    @else
                                        <span class="text-gray-400">→</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#94a3b8' : '#6b7280';

            // Revenue Chart
            (() => {
                const hist = @json($revenue['historical']);
                const proj = @json($revenue['projected']);
                const all = [...hist, ...proj];
                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: all.map(d => d.label),
                        datasets: [{
                            label: 'Aktual',
                            data: all.map(d => d.type === 'actual' ? d.amount : null),
                            backgroundColor: 'rgba(59,130,246,0.7)',
                            borderRadius: 6,
                        }, {
                            label: 'Proyeksi',
                            data: all.map(d => d.type === 'forecast' ? d.amount : null),
                            backgroundColor: 'rgba(59,130,246,0.25)',
                            borderColor: 'rgba(59,130,246,0.5)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                ticks: {
                                    color: textColor,
                                    callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt'
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            })();

            // Cash Flow Chart
            (() => {
                const data = @json($cashFlow);
                new Chart(document.getElementById('cashFlowChart'), {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Inflow',
                            data: data.map(d => d.inflow),
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34,197,94,0.1)',
                            fill: true,
                            tension: 0.3,
                            borderDash: data.map(d => d.type === 'forecast' ? 5 : 0),
                        }, {
                            label: 'Outflow',
                            data: data.map(d => d.outflow),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.1)',
                            fill: true,
                            tension: 0.3,
                        }, {
                            label: 'Net',
                            data: data.map(d => d.net),
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                ticks: {
                                    color: textColor,
                                    callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt'
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            })();

            // Aging Chart
            (() => {
                const aging = @json($receivables['aging']);
                new Chart(document.getElementById('agingChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Current', '1-30 hari', '31-60 hari', '61-90 hari', '90+ hari'],
                        datasets: [{
                            data: [aging.current_amount, aging.days_1_30, aging.days_31_60, aging
                                .days_61_90, aging.days_90_plus
                            ],
                            backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    boxWidth: 12
                                }
                            }
                        }
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
