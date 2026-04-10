<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Executive Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period Selector -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex space-x-2">
                    @foreach (['today', 'this_week', 'this_month', 'this_quarter', 'this_year'] as $p)
                        <a href="{{ route('analytics.executive-dashboard', ['period' => $p]) }}"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $period === $p ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ ucwords(str_replace('_', ' ', $p)) }}
                        </a>
                    @endforeach
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Last updated: {{ now()->format('H:i:s') }}
                </div>
            </div>

            <!-- Alerts -->
            @if (!empty($dashboard['alerts']))
                <div class="mb-6 space-y-3">
                    @foreach ($dashboard['alerts'] as $alert)
                        <div
                            class="p-4 rounded-lg border-l-4 {{ $alert['type'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20 border-red-500' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500' }}">
                            <div class="flex items-start">
                                <span class="text-2xl mr-3">{{ $alert['icon'] }}</span>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $alert['title'] }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Financial KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue</h3>
                        <span class="text-2xl">💰</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($dashboard['financial_kpis']['revenue']['current'], 0, ',', '.') }}
                    </p>
                    <div
                        class="mt-2 flex items-center text-sm {{ $dashboard['financial_kpis']['revenue']['trend'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                        <span>{{ $dashboard['financial_kpis']['revenue']['trend'] === 'up' ? '↑' : '↓' }}</span>
                        <span class="ml-1">{{ abs($dashboard['financial_kpis']['revenue']['growth']) }}% vs
                            previous</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Margin</h3>
                        <span class="text-2xl">📊</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $dashboard['financial_kpis']['profit_margin']['current'] }}%
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Profit: Rp
                        {{ number_format($dashboard['financial_kpis']['profit_margin']['amount'], 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Outstanding</h3>
                        <span class="text-2xl">⏳</span>
                    </div>
                    <p class="text-3xl font-bold text-orange-600">
                        Rp {{ number_format($dashboard['financial_kpis']['outstanding'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Unpaid invoices</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Cash Flow</h3>
                        <span class="text-2xl">💵</span>
                    </div>
                    <p
                        class="text-3xl font-bold {{ $dashboard['financial_kpis']['cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($dashboard['financial_kpis']['cash_flow'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Net position</p>
                </div>
            </div>

            <!-- Operational & Customer KPIs -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📦 Operational Metrics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Orders</span>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $dashboard['operational_kpis']['orders']['current'] }}</p>
                                <p
                                    class="text-xs {{ $dashboard['operational_kpis']['orders']['trend'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $dashboard['operational_kpis']['orders']['growth'] }}% growth
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Inventory Health</span>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $dashboard['operational_kpis']['inventory']['stock_health'] }}%</p>
                                <p class="text-xs text-gray-500">
                                    {{ $dashboard['operational_kpis']['inventory']['low_stock'] }} low stock items</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Fulfillment Rate</span>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $dashboard['operational_kpis']['fulfillment_rate'] }}%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">👥 Customer Metrics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">New Customers</span>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $dashboard['customer_kpis']['new_customers']['current'] }}</p>
                                <p
                                    class="text-xs {{ $dashboard['customer_kpis']['new_customers']['trend'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $dashboard['customer_kpis']['new_customers']['growth'] }}% growth
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Active Customers</span>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $dashboard['customer_kpis']['active_customers'] }}</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Retention Rate</span>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $dashboard['customer_kpis']['retention_rate'] }}%</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Churn Risk</span>
                            <p class="font-semibold text-red-600">{{ $dashboard['customer_kpis']['churn_risk'] }}
                                customers</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🚀 Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('analytics.predictive') }}"
                        class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg hover:shadow-md transition">
                        <div class="text-2xl mb-2">🔮</div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Predictive Analytics</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">AI-powered forecasts</p>
                    </a>
                    <a href="{{ route('analytics.comparative') }}"
                        class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg hover:shadow-md transition">
                        <div class="text-2xl mb-2">📈</div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Comparative Analysis</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">YoY, MoM, QoQ insights</p>
                    </a>
                    <a href="{{ route('analytics.report-builder') }}"
                        class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg hover:shadow-md transition">
                        <div class="text-2xl mb-2">📝</div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Custom Report Builder</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Build custom reports</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
