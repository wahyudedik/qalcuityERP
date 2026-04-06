@extends('layouts.app')

@section('title', 'Batch Performance Report')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Batch Performance Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Analyze batch yield and quality metrics</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                    ← Back to Analytics
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Batches</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($metrics['total_batches']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Average Yield</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($metrics['avg_yield'], 1) }}%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">QC Pass Rate</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($metrics['qc_pass_rate'], 1) }}%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Avg Rework Count</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">{{ number_format($metrics['avg_rework_count'], 1) }}
                </div>
            </div>
        </div>

        <!-- Yield Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Yield Trend Over Time</h3>
            <canvas id="yieldTrendChart" height="80"></canvas>
        </div>

        <!-- Performance by Formula -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Performance by Formula</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formula</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Yield</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Yield</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Yield</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consistency</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($byFormula as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item->formula?->formula_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->batch_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                {{ number_format($item->avg_yield, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ number_format($item->min_yield, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                {{ number_format($item->max_yield, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $variance = $item->max_yield - $item->min_yield;
                                    $consistency = $variance < 5 ? 'Excellent' : ($variance < 10 ? 'Good' : 'Variable');
                                @endphp
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $consistency === 'Excellent' ? 'bg-green-100 text-green-800' : ($consistency === 'Good' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $consistency }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('yieldTrendChart').getContext('2d');
            const trendData = @json($trendData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.date),
                    datasets: [{
                        label: 'Average Yield (%)',
                        data: trendData.map(d => parseFloat(d.avg_yield)),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 80,
                            max: 100
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
