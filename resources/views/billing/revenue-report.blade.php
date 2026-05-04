<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-chart-line text-blue-600"></i> Revenue Report
            </h1>
            <p class="text-gray-500">Healthcare revenue analytics and trends</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                        <div class="w-full md:w-1/4">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="w-full md:w-1/4">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="w-full md:w-1/6">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-400">
                <div class="p-5 text-center">
                    <h3 class="text-blue-600">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-gray-500">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">Rp {{ number_format($stats['collected'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-gray-500">Collected</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">Rp {{ number_format($stats['pending'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['total_patients'] ?? 0 }}</h3>
                    <small class="text-gray-500">Patients Served</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-hospital"></i> Revenue by Department
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Revenue</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departmentRevenue ?? [] as $dept)
                                    <tr>
                                        <td>{{ $dept['name'] }}</td>
                                        <td><strong>Rp {{ number_format($dept['revenue'], 0, ',', '.') }}</strong></td>
                                        <td>
                                            <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 20px;">
                                                <div class="bg-blue-600 h-full rounded-full"
                                                    style="width: {{ $dept['percentage'] }}%">
                                                    {{ $dept['percentage'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-400">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-bg-white rounded-2xl border border-gray-200"></i> Revenue by Payment Method
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods ?? [] as $method)
                                    <tr>
                                        <td>{{ $method['name'] }}</td>
                                        <td><strong>Rp {{ number_format($method['amount'], 0, ',', '.') }}</strong></td>
                                        <td>{{ $method['count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-400">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar"></i> Daily Revenue Trend
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patients</th>
                                    <th>Revenue</th>
                                    <th>Avg. per Patient</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyTrend ?? [] as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td>{{ $day['patients'] }}</td>
                                        <td><strong>Rp {{ number_format($day['revenue'], 0, ',', '.') }}</strong></td>
                                        <td>Rp {{ number_format($day['avg_per_patient'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No trend data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
