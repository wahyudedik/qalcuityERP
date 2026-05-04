<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-bed text-blue-600"></i> Bed Occupancy Report
            </h1>
            <p class="text-gray-500">Hospital bed utilization and occupancy statistics</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
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
                            <h2 class="text-blue-600">{{ $stats['total_beds'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Beds</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['occupied_beds'] ?? 0 }}</h2>
                            <small class="text-gray-500">Occupied</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['available_beds'] ?? 0 }}</h2>
                            <small class="text-gray-500">Available</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-{{ ($stats['occupancy_rate'] ?? 0) > 85 ? 'danger' : 'warning' }}">
                                {{ $stats['occupancy_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Occupancy Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Occupancy by Ward
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Total</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wardStats ?? [] as $ward)
                                    <tr>
                                        <td>{{ $ward['name'] }}</td>
                                        <td>{{ $ward['total'] }}</td>
                                        <td>{{ $ward['occupied'] }}</td>
                                        <td>{{ $ward['available'] }}</td>
                                        <td>
                                            <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 20px;">
                                                <div class="h-full rounded-full bg-{{ $ward['rate'] > 85 ? 'red-500' : ($ward['rate'] > 60 ? 'amber-500' : 'emerald-500')   }}"
                                                    style="width: {{ $ward['rate'] }}%">
                                                    {{ $ward['rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-400">No ward data available</td>
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
                        <i class="fas fa-clock"></i> Average Length of Stay
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Avg LOS (days)</th>
                                    <th>Admissions</th>
                                    <th>Discharges</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departmentStats ?? [] as $dept)
                                    <tr>
                                        <td>{{ $dept['name'] }}</td>
                                        <td><strong>{{ $dept['avg_los'] ?? 0 }}</strong></td>
                                        <td>{{ $dept['admissions'] ?? 0 }}</td>
                                        <td>{{ $dept['discharges'] ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No department data</td>
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
                        <i class="fas fa-bed"></i> Current Bed Status
                    </h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Bed Number</th>
                                    <th>Ward</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                    <th>Admission Date</th>
                                    <th>Length of Stay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beds ?? [] as $bed)
                                    <tr>
                                        <td><strong>{{ $bed->bed_number }}</strong></td>
                                        <td>{{ $bed->ward?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'occupied' => 'success',
                                                    'available' => 'info',
                                                    'maintenance' => 'warning',
                                                    'reserved' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$bed->status] ?? 'secondary'  }}">
                                                {{ ucfirst($bed->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $bed->currentPatient?->name ?? '-' }}</td>
                                        <td>{{ $bed->currentPatient?->admission?->admission_date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($bed->currentPatient?->admission)
                                                {{ $bed->currentPatient?->admission->admission_date->diffInDays(now()) }}
                                                days
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400">No bed data available</td>
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
