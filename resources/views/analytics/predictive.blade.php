<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Predictive Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Forecast Period Selector -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex space-x-2">
                    @foreach ([1, 3, 6, 12] as $m)
                        <a href="{{ route('analytics.predictive', ['months' => $m]) }}"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $months == $m ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $m }} {{ $m == 1 ? 'Month' : 'Months' }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Forecast Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-sm font-medium opacity-90 mb-2">Predicted Revenue</h3>
                    <p class="text-4xl font-bold">
                        Rp {{ number_format($predictions['forecast']['predicted'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm opacity-90">
                        Trend: {{ ucfirst(str_replace('_', ' ', $predictions['forecast']['trend'])) }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Confidence Interval</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Lower Bound</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($predictions['confidence_interval']['lower'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Upper Bound</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($predictions['confidence_interval']['upper'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Seasonality</h3>
                    <div class="flex items-center justify-center h-full">
                        @if ($predictions['seasonality_detected'])
                            <div class="text-center">
                                <span class="text-4xl">📊</span>
                                <p class="mt-2 font-semibold text-gray-900 dark:text-white">Detected</p>
                                <p class="text-xs text-gray-500">Pattern identified in data</p>
                            </div>
                        @else
                            <div class="text-center">
                                <span class="text-4xl">➡️</span>
                                <p class="mt-2 font-semibold text-gray-900 dark:text-white">Not Detected</p>
                                <p class="text-xs text-gray-500">Insufficient data or no pattern</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Historical Data & Forecast Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📈 Revenue Trend & Forecast</h3>
                <canvas id="forecastChart" height="100"></canvas>
            </div>

            <!-- Forecast Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📋 Forecast Breakdown</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Month</th>
                                <th class="px-4 py-3 text-right">Predicted Revenue</th>
                                <th class="px-4 py-3 text-right">Lower Bound</th>
                                <th class="px-4 py-3 text-right">Upper Bound</th>
                                <th class="px-4 py-3 text-center">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @for ($i = 1; $i <= $months; $i++)
                                @php
                                    $predicted = $predictions['forecast']['predicted'] * (1 + ($i - 1) * 0.02);
                                    $lower = $predicted * 0.9;
                                    $upper = $predicted * 1.1;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ now()->addMonths($i)->format('F Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($predicted, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                        Rp {{ number_format($lower, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                        Rp {{ number_format($upper, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $predictions['forecast']['trend'] === 'upward' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ ucfirst(str_replace('_', ' ', $predictions['forecast']['trend'])) }}
                                        </span>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Forecast Chart
            const ctx = document.getElementById('forecastChart').getContext('2d');
            const historicalData = @json($predictions['historical']);

            const labels = historicalData.map(d => d.month);
            const revenues = historicalData.map(d => d.revenue);

            // Add forecast months
            for (let i = 1; i <= {{ $months }}; i++) {
                labels.push('{{ now()->format('Y-m') }}+' + i);
                revenues.push(null);
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Historical Revenue',
                        data: revenues,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
