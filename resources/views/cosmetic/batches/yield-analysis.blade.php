<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <i class="fas fa-chart-line mr-2 text-green-600"></i>Yield Analysis
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $batch->batch_number }} - {{ $batch->formula->formula_name }}
                </p>
            </div>
            <a href="{{ route('cosmetic.batches.show', $batch) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Batch
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Current Yield Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-percentage mr-2 text-blue-600"></i>Current Batch Yield
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-sm text-blue-600 dark:text-blue-400">Current Yield</div>
                        <div class="mt-2 text-3xl font-bold text-blue-900 dark:text-blue-300">
                            {{ number_format($yieldAnalysis['current_yield'], 1) }}%
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            Status: {{ ucfirst(str_replace('_', ' ', $yieldAnalysis['yield_status'])) }}
                        </div>
                    </div>

                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-sm text-green-600 dark:text-green-400">Planned vs Actual</div>
                        <div class="mt-2 text-lg font-bold text-green-900 dark:text-green-300">
                            {{ number_format($yieldAnalysis['planned_quantity'], 2) }} →
                            {{ number_format($yieldAnalysis['actual_quantity'], 2) }}
                        </div>
                    </div>

                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">Loss</div>
                        <div class="mt-2 text-lg font-bold text-yellow-900 dark:text-yellow-300">
                            {{ number_format($yieldAnalysis['loss_quantity'], 2) }}
                            ({{ $yieldAnalysis['loss_percentage'] }}%)
                        </div>
                    </div>

                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="text-sm text-purple-600 dark:text-purple-400">Rework Losses</div>
                        <div class="mt-2 text-lg font-bold text-purple-900 dark:text-purple-300">
                            {{ number_format($yieldAnalysis['rework_losses'], 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Comparison -->
            @if ($yieldAnalysis['historical_average'])
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-history mr-2 text-purple-600"></i>Historical Comparison
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Average Yield</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $yieldAnalysis['historical_average'] }}%
                            </div>
                        </div>

                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                            <div class="text-sm text-green-600 dark:text-green-400">Best Yield</div>
                            <div class="mt-2 text-2xl font-bold text-green-900 dark:text-green-300">
                                {{ $yieldAnalysis['historical_best'] }}%
                            </div>
                        </div>

                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
                            <div class="text-sm text-red-600 dark:text-red-400">Worst Yield</div>
                            <div class="mt-2 text-2xl font-bold text-red-900 dark:text-red-300">
                                {{ $yieldAnalysis['historical_worst'] }}%
                            </div>
                        </div>

                        <div
                            class="p-4 rounded-lg text-center
                        @if ($yieldAnalysis['vs_average'] >= 0) bg-green-50 dark:bg-green-900/20
                        @else bg-red-50 dark:bg-red-900/20 @endif">
                            <div class="text-sm">Vs Average</div>
                            <div
                                class="mt-2 text-2xl font-bold
                            @if ($yieldAnalysis['vs_average'] >= 0) text-green-900 dark:text-green-300
                            @else text-red-900 dark:text-red-300 @endif">
                                {{ $yieldAnalysis['vs_average'] >= 0 ? '+' : '' }}{{ $yieldAnalysis['vs_average'] }}%
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        Based on {{ $yieldAnalysis['total_batches_analyzed'] }} released batches
                    </div>
                </div>
            @endif

            <!-- Yield Trends Chart -->
            @if (count($yieldTrends['trends']) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-chart-area mr-2 text-green-600"></i>Yield Trends (Last 6 Months)
                        </h3>
                        <a href="{{ route('cosmetic.batches.yield-report', ['formulaId' => $batch->formula_id, 'months' => 6]) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-file-pdf mr-2"></i>Export Report
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Date</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Batch</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Yield</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Actual</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Planned</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($yieldTrends['trends'] as $trend)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $trend['date'] }}</td>
                                        <td class="px-4 py-3 text-sm text-blue-600 dark:text-blue-400">
                                            {{ $trend['batch_number'] }}</td>
                                        <td
                                            class="px-4 py-3 text-sm font-bold
                                    @if ($trend['yield'] >= 95) text-green-600 dark:text-green-400
                                    @elseif($trend['yield'] >= 90) text-yellow-600 dark:text-yellow-400
                                    @else text-red-600 dark:text-red-400 @endif">
                                            {{ $trend['yield'] }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ number_format($trend['actual'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ number_format($trend['planned'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded text-center">
                            <div class="text-xs text-gray-600 dark:text-gray-400">Average</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $yieldTrends['average'] }}%
                            </div>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded text-center">
                            <div class="text-xs text-green-600 dark:text-green-400">Maximum</div>
                            <div class="text-lg font-bold text-green-900 dark:text-green-300">
                                {{ $yieldTrends['max'] }}%</div>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded text-center">
                            <div class="text-xs text-red-600 dark:text-red-400">Minimum</div>
                            <div class="text-lg font-bold text-red-900 dark:text-red-300">{{ $yieldTrends['min'] }}%
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
