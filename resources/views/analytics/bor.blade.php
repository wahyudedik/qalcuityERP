<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-bed text-blue-600"></i> Bed Occupancy Rate (BOR)
            </h1>
            <p class="text-gray-500">Hospital bed utilization metrics</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h2 class="text-blue-600">{{ $stats['current_bor'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Current BOR</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['avg_bor_month'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Monthly Average</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['total_beds'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Beds</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-amber-600">{{ $stats['occupied_beds'] ?? 0 }}</h2>
                            <small class="text-gray-500">Occupied</small>
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
                    <h5 class="mb-0">BOR by Ward</h5>
                </div>
                <div class="p-5">
                    @forelse($wardBOR ?? [] as $ward)
                        <div class="mb-3">
                            <div class="flex justify-between mb-1">
                                <strong>{{ $ward['name'] }}</strong>
                                <span>{{ $ward['bor'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 25px;">
                                <div class="h-full rounded-full bg-{{ $ward['bor'] > 85 ? 'red-500' : ($ward['bor'] > 60 ? 'amber-500' : 'emerald-500')   }}"
                                    style="width: {{ $ward['bor'] }}%">
                                    {{ $ward['bor'] }}%
                                </div>
                            </div>
                            <small class="text-gray-500">{{ $ward['occupied'] }}/{{ $ward['total'] }} beds occupied</small>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No ward data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">BOR Trend (Last 7 Days)</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>BOR</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($borTrend ?? [] as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $day['bor'] > 85 ? 'red-500' : ($day['bor'] > 60 ? 'amber-500' : 'emerald-500')  }}">
                                                {{ $day['bor'] }}%
                                            </span>
                                        </td>
                                        <td>{{ $day['occupied'] }}</td>
                                        <td>{{ $day['available'] }}</td>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>BOR Benchmark:</strong> Ideal BOR is 60-85%. Above 85% indicates overcapacity, below 60% indicates
                underutilization.
            </div>
        </div>
    </div>
</x-app-layout>
