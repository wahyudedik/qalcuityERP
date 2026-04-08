@extends('layouts.app')

@section('title', 'AI Predictive Analytics')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">AI Predictive Analytics</h1>
                    <p class="mt-2 text-sm text-gray-600">Sales forecasting, inventory demand & churn prediction</p>
                </div>
                <a href="{{ route('analytics.advanced') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Prediction Type Selector -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('analytics.predictive', ['type' => 'sales', 'horizon' => 30]) }}"
                    class="p-4 rounded-lg border-2 {{ $predictionType == 'sales' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300' }} transition">
                    <div class="flex items-center gap-3">
                        <i
                            class="fas fa-chart-line text-2xl {{ $predictionType == 'sales' ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                        <div>
                            <div
                                class="font-semibold {{ $predictionType == 'sales' ? 'text-indigo-900' : 'text-gray-700' }}">
                                Sales Forecasting</div>
                            <div class="text-sm {{ $predictionType == 'sales' ? 'text-indigo-600' : 'text-gray-500' }}">
                                30/60/90 day predictions</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('analytics.predictive', ['type' => 'inventory', 'horizon' => 30]) }}"
                    class="p-4 rounded-lg border-2 {{ $predictionType == 'inventory' ? 'border-green-600 bg-green-50' : 'border-gray-200 hover:border-green-300' }} transition">
                    <div class="flex items-center gap-3">
                        <i
                            class="fas fa-boxes text-2xl {{ $predictionType == 'inventory' ? 'text-green-600' : 'text-gray-400' }}"></i>
                        <div>
                            <div
                                class="font-semibold {{ $predictionType == 'inventory' ? 'text-green-900' : 'text-gray-700' }}">
                                Inventory Demand</div>
                            <div class="text-sm {{ $predictionType == 'inventory' ? 'text-green-600' : 'text-gray-500' }}>Stock predictions & reorders</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('analytics.predictive', ['type' => 'churn']) }}"
                                class="p-4 rounded-lg border-2 {{ $predictionType == 'churn' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-red-300' }} transition">
                                <div class="flex items-center gap-3">
                                    <i
                                        class="fas fa-user-slash text-2xl {{ $predictionType == 'churn' ? 'text-red-600' : 'text-gray-400' }}"></i>
                                    <div>
                                        <div
                                            class="font-semibold {{ $predictionType == 'churn' ? 'text-red-900' : 'text-gray-700' }}">
                                            Churn Prediction</div>
                                        <div
                                            class="text-sm {{ $predictionType == 'churn' ? 'text-red-600' : 'text-gray-500' }}">
                                            At-risk customer identification</div>
                                    </div>
                                </div>
                </a>
            </div>
        </div>

        @if ($predictionType == 'sales')
            <!-- Sales Forecasting -->
            <div class="space-y-6">
                <!-- Forecast Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Sales Forecast ({{ $prediction['horizon'] ?? 30 }}
                            Days)</h3>
                        @if (isset($prediction['accuracy']))
                            <div class="text-sm">
                                <span class="text-gray-500">Model Accuracy:</span>
                                <span
                                    class="font-bold text-green-600">{{ number_format($prediction['accuracy'], 1) }}%</span>
                            </div>
                        @endif
                    </div>
                    <div id="forecastChart" style="height: 400px;"></div>
                </div>

                <!-- Confidence Interval -->
                @if (isset($prediction['confidence_interval']))
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                        <h4 class="font-semibold text-gray-900 mb-4">Confidence Interval (95%)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-lg p-4">
                                <div class="text-sm text-gray-500">Lower Bound</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    Rp {{ number_format($prediction['confidence_interval']['lower'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-4">
                                <div class="text-sm text-gray-500">Mean Prediction</div>
                                <div class="text-2xl font-bold text-indigo-600">
                                    Rp {{ number_format($prediction['confidence_interval']['mean'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-4">
                                <div class="text-sm text-gray-500">Upper Bound</div>
                                <div class="text-2xl font-bold text-purple-600">
                                    Rp {{ number_format($prediction['confidence_interval']['upper'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- AI Insights -->
                @if (isset($prediction['ai_insights']))
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-robot text-3xl text-purple-600 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">AI Insights</h4>
                                <div class="text-gray-700 whitespace-pre-line">{{ $prediction['ai_insights'] }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Historical vs Forecast Table -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Forecast Details</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predicted
                                        Revenue</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Trend</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach (array_slice($prediction['forecast'] ?? [], 0, 15) as $forecast)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $forecast['date'] }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                            Rp {{ number_format($forecast['predicted_revenue'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right">
                                            <span class="text-green-600"><i class="fas fa-arrow-up"></i></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($predictionType == 'inventory')
            <!-- Inventory Demand Prediction -->
            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm text-gray-500 mb-2">Products Analyzed</div>
                        <div class="text-3xl font-bold text-gray-900">
                            {{ $prediction['total_products_analyzed'] ?? 0 }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="text-sm text-gray-500 mb-2">Need Reorder</div>
                        <div class="text-3xl font-bold text-red-600">
                            {{ $prediction['products_needing_reorder'] ?? 0 }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 mb-2">Stock Sufficient</div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ ($prediction['total_products_analyzed'] ?? 0) - ($prediction['products_needing_reorder'] ?? 0) }}
                        </div>
                    </div>
                </div>

                <!-- Predictions Table -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">30-Day Demand Predictions</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Daily
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predicted
                                        30d</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current
                                        Stock</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Order Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($prediction['predictions'] ?? [] as $pred)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ $pred['product_name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                                            {{ number_format($pred['avg_daily_demand'], 1) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                            {{ number_format($pred['predicted_demand_30d']) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-right {{ $pred['current_stock'] < $pred['predicted_demand_30d'] ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                            {{ number_format($pred['current_stock']) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if ($pred['reorder_needed'])
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                                    Reorder
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                                    Sufficient
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-600">
                                            {{ $pred['reorder_needed'] ? number_format($pred['recommended_order_qty']) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($predictionType == 'churn')
            <!-- Churn Prediction -->
            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-red-50 rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="text-sm text-red-600 mb-2">High Risk</div>
                        <div class="text-3xl font-bold text-red-700">
                            {{ $prediction['high_risk_count'] ?? 0 }}
                        </div>
                        <div class="text-xs text-red-500 mt-1">Immediate action needed</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="text-sm text-yellow-600 mb-2">Medium Risk</div>
                        <div class="text-3xl font-bold text-yellow-700">
                            {{ $prediction['medium_risk_count'] ?? 0 }}
                        </div>
                        <div class="text-xs text-yellow-500 mt-1">Monitor closely</div>
                    </div>
                    <div class="bg-green-50 rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="text-sm text-green-600 mb-2">Low Risk</div>
                        <div class="text-3xl font-bold text-green-700">
                            {{ $prediction['low_risk_count'] ?? 0 }}
                        </div>
                        <div class="text-xs text-green-500 mt-1">Healthy customers</div>
                    </div>
                </div>

                <!-- Risk Meter -->
                @php
                    $total =
                        ($prediction['high_risk_count'] ?? 0) +
                        ($prediction['medium_risk_count'] ?? 0) +
                        ($prediction['low_risk_count'] ?? 0);
                    $highPercent = $total > 0 ? (($prediction['high_risk_count'] ?? 0) / $total) * 100 : 0;
                @endphp
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Overall Churn Risk</h4>
                    <div class="w-full bg-gray-200 rounded-full h-6">
                        <div class="bg-red-600 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                            style="width: {{ $highPercent }}%">
                            {{ number_format($highPercent, 1) }}%
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ number_format($highPercent, 1) }}% of customers at high risk of churning
                    </div>
                </div>

                <!-- High Risk Customers Table -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">High Risk Customers (Action Required)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Risk
                                        Score</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days
                                        Since Order</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders
                                        (90d)</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Spent
                                        (90d)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($prediction['customers']->where('risk_level', 'high')->take(20) as $customer)
                                    <tr class="bg-red-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $customer['customer']->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $customer['customer']->email ?? 'No email' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 text-xs font-bold bg-red-600 text-white rounded-full">
                                                {{ $customer['risk_score'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-red-600 font-bold">
                                            {{ $customer['days_since_last_order'] }} days
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                                            {{ $customer['order_count_90d'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">
                                            Rp {{ number_format($customer['total_spent_90d'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                Contact Now
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        @if ($predictionType == 'sales' && isset($prediction['forecast']))
            document.addEventListener('DOMContentLoaded', function() {
                const forecastData = @json($prediction['forecast']);
                const historicalData = @json($prediction['historical'] ?? []);

                if (forecastData && forecastData.length > 0) {
                    const historicalDates = historicalData.slice(-30).map(item => item.date);
                    const historicalValues = historicalData.slice(-30).map(item => item.revenue);

                    const forecastDates = forecastData.map(item => item.date);
                    const forecastValues = forecastData.map(item => item.predicted_revenue);

                    const options = {
                        chart: {
                            type: 'line',
                            height: 400,
                            toolbar: {
                                show: true
                            },
                            zoom: {
                                enabled: true
                            }
                        },
                        series: [{
                                name: 'Historical',
                                data: historicalValues,
                                color: '#10B981'
                            },
                            {
                                name: 'Forecast',
                                data: Array(historicalValues.length).fill(null).concat(forecastValues),
                                color: '#3B82F6',
                                dashArray: 5
                            }
                        ],
                        xaxis: {
                            categories: [...historicalDates, ...forecastDates],
                            labels: {
                                rotate: -45,
                                style: {
                                    fontSize: '11px'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        },
                        stroke: {
                            width: [3, 3],
                            curve: 'smooth'
                        },
                        fill: {
                            type: 'solid'
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'left'
                        },
                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return value ? 'Rp ' + value.toLocaleString('id-ID') : '-';
                                }
                            }
                        },
                        annotations: {
                            xaxis: [{
                                x: historicalDates[historicalDates.length - 1],
                                borderColor: '#FF5722',
                                label: {
                                    text: 'Today',
                                    style: {
                                        color: '#fff',
                                        background: '#FF5722'
                                    }
                                }
                            }]
                        }
                    };

                    const chart = new ApexCharts(document.querySelector("#forecastChart"), options);
                    chart.render();
                }
            });
        @endif
    </script>
@endpush
