@extends('layouts.app')

@section('title', 'Cosmetic Analytics Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Cosmetic Analytics & Reports</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comprehensive insights into manufacturing quality and compliance
                    </p>
                </div>
                <div class="flex gap-2">
                    <select id="periodSelect" onchange="updatePeriod(this.value)"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $period == 365 ? 'selected' : '' }}>Last Year</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Batch Yield</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['batch_yield_avg'], 1) }}%</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Production efficiency</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">QC Pass Rate</div>
                <div
                    class="mt-2 text-3xl font-bold {{ $stats['qc_pass_rate'] >= 95 ? 'text-green-600 dark:text-green-400' : ($stats['qc_pass_rate'] >= 85 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                    {{ number_format($stats['qc_pass_rate'], 1) }}%</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Quality control</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Registrations</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['active_registrations'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Regulatory compliance</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Recalls</div>
                <div class="mt-2 text-3xl font-bold {{ $stats['open_recalls'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ $stats['open_recalls'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Product safety</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Alerts (30d)</div>
                <div
                    class="mt-2 text-3xl font-bold {{ $stats['expiry_alerts_30d'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ $stats['expiry_alerts_30d'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upcoming expirations</div>
            </div>
        </div>

        <!-- Report Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <a href="{{ route('cosmetic.analytics.batch-performance') }}"
                class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Batch Performance</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Yield & QC analysis</p>
                    </div>
                </div>
                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.qc-trend') }}"
                class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">QC Trend Analysis</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Test results over time</p>
                    </div>
                </div>
                <div class="text-sm text-green-600 dark:text-green-400 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.regulatory') }}"
                class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-lg p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Regulatory Dashboard</h3>
                        <p class="text-sm text-gray-500">Compliance overview</p>
                    </div>
                </div>
                <div class="text-sm text-red-600 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.cost-analysis') }}"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Formula Cost Analysis</h3>
                        <p class="text-sm text-gray-500">Ingredient cost trends</p>
                    </div>
                </div>
                <div class="text-sm text-yellow-600 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.supplier-quality') }}"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Supplier Quality</h3>
                        <p class="text-sm text-gray-500">Vendor performance</p>
                    </div>
                </div>
                <div class="text-sm text-purple-600 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.product-lifecycle') }}"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Product Lifecycle</h3>
                        <p class="text-sm text-gray-500">Launch to discontinuation</p>
                    </div>
                </div>
                <div class="text-sm text-indigo-600 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.recall-report') }}"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recall Report</h3>
                        <p class="text-sm text-gray-500">Effectiveness tracking</p>
                    </div>
                </div>
                <div class="text-sm text-orange-600 font-medium">View Report →</div>
            </a>

            <a href="{{ route('cosmetic.analytics.expiry-forecast') }}"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 bg-teal-100 rounded-lg p-3">
                        <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Expiry Forecast</h3>
                        <p class="text-sm text-gray-500">Predictive analytics</p>
                    </div>
                </div>
                <div class="text-sm text-teal-600 font-medium">View Report →</div>
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            function updatePeriod(days) {
                window.location.href = `?period=${days}`;
            }
        </script>
    @endpush
@endsection
