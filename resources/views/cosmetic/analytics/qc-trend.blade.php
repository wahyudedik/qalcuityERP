@extends('layouts.app')

@section('title', 'QC Trend Analysis')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">QC Trend Analysis</h1>
                    <p class="mt-1 text-sm text-gray-500">Quality control test results over time</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Filter</button>
            </form>
        </div>

        <!-- QC Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Tests</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($qcStats['total_tests']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Passed</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($qcStats['pass_count']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Failed</div>
                <div class="mt-2 text-3xl font-bold text-red-600">{{ number_format($qcStats['fail_count']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Pass Rate</div>
                <div
                    class="mt-2 text-3xl font-bold {{ $qcStats['pass_rate'] >= 95 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ number_format($qcStats['pass_rate'], 1) }}%</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Pass/Fail Trend</h3>
                <canvas id="trendChart" height="100"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">By Test Category</h3>
                <canvas id="categoryChart" height="100"></canvas>
            </div>
        </div>

        <!-- Common Failures -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Top 10 Failure Points</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failure Count</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($failurePoints as $failure)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $failure->test_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">
                                {{ $failure->fail_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const trendData = @json($trendByDate);
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.date),
                    datasets: [{
                            label: 'Passed',
                            data: trendData.map(d => d.passed),
                            borderColor: 'rgb(34, 197, 94)',
                            tension: 0.4
                        },
                        {
                            label: 'Failed',
                            data: trendData.map(d => d.failed),
                            borderColor: 'rgb(239, 68, 68)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            const catData = @json($byCategory);
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: catData.map(d => d.test_category),
                    datasets: [{
                            label: 'Passed',
                            data: catData.map(d => d.passed),
                            backgroundColor: 'rgb(34, 197, 94)'
                        },
                        {
                            label: 'Failed',
                            data: catData.map(d => d.failed),
                            backgroundColor: 'rgb(239, 68, 68)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
