<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-chart-line text-blue-600"></i> Mortality Rate Analysis
            </h1>
            <p class="text-gray-500">Patient mortality metrics and trends</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h2 class="text-red-600">{{ $stats['mortality_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Overall Mortality Rate</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-blue-600">{{ $stats['total_deaths'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Deaths</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['total_discharges'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Discharges</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['benchmark_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">National Benchmark</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Mortality by Department</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Deaths</th>
                                    <th>Discharges</th>
                                    <th>Rate</th>
                                    <th>vs Benchmark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptMortality ?? [] as $dept)
                                    <tr>
                                        <td><strong>{{ $dept['name'] }}</strong></td>
                                        <td>{{ $dept['deaths'] }}</td>
                                        <td>{{ $dept['discharges'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $dept['rate'] > ($stats['benchmark_rate'] ?? 3) ? 'red-500' : 'emerald-500'  }}">
                                                {{ $dept['rate'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $diff = $dept['rate'] - ($stats['benchmark_rate'] ?? 3);
                                            @endphp
                                            @if ($diff > 0)
                                                <span class="text-red-600">+{{ number_format($diff, 2) }}%</span>
                                            @else
                                                <span class="text-emerald-600">{{ number_format($diff, 2) }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No data available</td>
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
                    <h5 class="mb-0">Monthly Trend</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Deaths</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyTrend ?? [] as $month)
                                    <tr>
                                        <td>{{ $month['month'] }}</td>
                                        <td>{{ $month['deaths'] }}</td>
                                        <td><strong>{{ $month['rate'] }}%</strong></td>
                                        <td>
                                            @if ($month['trend'] > 0)
                                                <span class="text-red-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-up"></i></span>
                                            @elseif($month['trend'] < 0)
                                                <span class="text-emerald-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-down"></i></span>
                                            @else
                                                <span class="text-gray-500"><i class="fas fa-minus"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No trend data</td>
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
