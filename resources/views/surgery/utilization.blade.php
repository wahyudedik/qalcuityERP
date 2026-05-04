<x-app-layout>
    <x-slot name="header">

<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold mb-0">
            <i class="fas fa-chart-pie text-blue-600"></i> OR Utilization Report
        </h1>
        <p class="text-gray-500">Operating room efficiency and usage analytics</p>
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="w-full md:w-1/4">
                        <h3 class="text-blue-600">{{ $stats['total_ors'] ?? 0 }}</h3>
                        <small class="text-gray-500">Total ORs</small>
                    </div>
                    <div class="w-full md:w-1/4">
                        <h3 class="text-emerald-600">{{ $stats['utilization_rate'] ?? 0 }}%</h3>
                        <small class="text-gray-500">Utilization Rate</small>
                    </div>
                    <div class="w-full md:w-1/4">
                        <h3 class="text-sky-600">{{ $stats['avg_turnaround'] ?? 0 }} min</h3>
                        <small class="text-gray-500">Avg Turnaround</small>
                    </div>
                    <div class="w-full md:w-1/4">
                        <h3 class="text-amber-600">{{ $stats['cancellation_rate'] ?? 0 }}%</h3>
                        <small class="text-gray-500">Cancellation Rate</small>
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
                <h5 class="mb-0">
                    <i class="fas fa-door-open"></i> OR Utilization by Room
                </h5>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Available hrs</th>
                                <th>Used hrs</th>
                                <th>Utilization</th>
                                <th>Surgeries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orUtilization ?? [] as $or)
                            <tr>
                                <td><strong>{{ $or['name'] }}</strong></td>
                                <td>{{ $or['available_hours'] }}</td>
                                <td>{{ $or['used_hours'] }}</td>
                                <td>
                                    <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 20px;">
                                        <div class="h-full rounded-full bg-{{ $or['utilization'] > 80 ? 'emerald-500' : ($or['utilization'] > 50 ? 'amber-500' : 'red-500')   }}" 
                                             style="width: {{ $or['utilization'] }}%">
                                            {{ $or['utilization'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $or['surgery_count'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-400">No data available</td>
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
                    <i class="fas fa-procedures"></i> Surgeries by Type
                </h5>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr>
                                <th>Surgery Type</th>
                                <th>Count</th>
                                <th>Avg Duration</th>
                                <th>Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($surgeryTypes ?? [] as $type)
                            <tr>
                                <td>{{ $type['name'] }}</td>
                                <td>{{ $type['count'] }}</td>
                                <td>{{ $type['avg_duration'] }} min</td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">{{ $type['success_rate'] }}%</span>
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
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="w-full">
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week"></i> Weekly Schedule
                </h5>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left w-full text-sm text-left-bordered w-full text-sm text-left-sm">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeklySchedule ?? [] as $schedule)
                            <tr>
                                <td><strong>{{ $schedule['or_name'] }}</strong></td>
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                <td class="text-center">
                                    @if(isset($schedule[$day]) && $schedule[$day] > 0)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $schedule[$day }} surgeries</span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-400">No schedule data available</td>
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
