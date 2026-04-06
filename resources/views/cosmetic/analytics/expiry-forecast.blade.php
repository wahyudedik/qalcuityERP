@extends('layouts.app')

@section('title', 'Expiry Forecast')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Expiry Forecast</h1>
                    <p class="mt-1 text-sm text-gray-500">Predictive analytics for product expirations</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Month Selector -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <select name="months" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="3" {{ $months == 3 ? 'selected' : '' }}>Next 3 Months</option>
                    <option value="6" {{ $months == 6 ? 'selected' : '' }}>Next 6 Months</option>
                    <option value="12" {{ $months == 12 ? 'selected' : '' }}>Next 12 Months</option>
                </select>
            </form>
        </div>

        <!-- Current Alerts -->
        @if ($currentAlerts->count() > 0)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ $currentAlerts->count() }} Active Expiry Alerts</h3>
                        <p class="text-sm text-red-700 mt-1">Immediate action required</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Forecast Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Monthly Expiry Forecast</h3>
            <canvas id="forecastChart" height="80"></canvas>
        </div>

        <!-- Products at Risk -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Products Expiring Within 3 Months</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earliest Expiry</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Left</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($atRiskProducts as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $product->formula?->formula_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->batch_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($product->total_stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $product->earliest_expiry->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $days = now()->diffInDays($product->earliest_expiry, false); @endphp
                                <span
                                    class="text-sm font-bold {{ $days < 30 ? 'text-red-600' : ($days < 60 ? 'text-orange-600' : 'text-yellow-600') }}">
                                    {{ $days }} days
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
            const forecastData = @json($monthlyBreakdown);
            new Chart(document.getElementById('forecastChart'), {
                type: 'bar',
                data: {
                    labels: forecastData.map(d => d.month_label),
                    datasets: [{
                        label: 'Batches Expiring',
                        data: forecastData.map(d => d.batch_count),
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }, {
                        label: 'Total Quantity',
                        data: forecastData.map(d => d.total_quantity),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
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
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
