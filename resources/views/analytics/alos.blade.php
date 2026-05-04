<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-clock text-blue-600"></i> Average Length of Stay (ALOS)
            </h1>
            <p class="text-gray-500">Patient hospitalization duration metrics</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h2 class="text-blue-600">{{ $stats['alos'] ?? 0 }} days</h2>
                            <small class="text-gray-500">Overall ALOS</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['total_discharges'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Discharges</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['total_days'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Patient Days</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-amber-600">{{ $stats['target_alos'] ?? 0 }} days</h2>
                            <small class="text-gray-500">Target ALOS</small>
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
                    <h5 class="mb-0">ALOS by Department</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>ALOS</th>
                                    <th>Discharges</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptALOS ?? [] as $dept)
                                    <tr>
                                        <td><strong>{{ $dept['name'] }}</strong></td>
                                        <td>{{ $dept['alos'] }} days</td>
                                        <td>{{ $dept['discharges'] }}</td>
                                        <td>
                                            @if ($dept['alos'] <= ($stats['target_alos'] ?? 5))
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">On Target</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Above Target</span>
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
                    <h5 class="mb-0">ALOS Trend (Last 30 Days)</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>ALOS</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alosTrend ?? [] as $period)
                                    <tr>
                                        <td>{{ $period['period'] }}</td>
                                        <td><strong>{{ $period['alos'] }} days</strong></td>
                                        <td>
                                            @if ($period['trend'] > 0)
                                                <span class="text-red-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-up"></i>
                                                    +{{ $period['trend'] }}%</span>
                                            @elseif($period['trend'] < 0)
                                                <span class="text-emerald-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-down"></i>
                                                    {{ $period['trend'] }}%</span>
                                            @else
                                                <span class="text-gray-500"><i class="fas fa-minus"></i> 0%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-400">No trend data</td>
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
