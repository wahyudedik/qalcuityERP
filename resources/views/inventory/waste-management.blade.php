<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-biohazard text-blue-600"></i> Medical Waste Management
            </h1>
            <p class="text-gray-500">Track and manage medical waste disposal</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addWasteModal">
                <i class="fas fa-plus"></i> Log Waste
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ number_format($stats['total_waste_kg'] ?? 0, 2) }} kg</h3>
                    <small class="text-gray-500">Total This Month</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['infectious_kg'] ?? 0 }} kg</h3>
                    <small class="text-gray-500">Infectious Waste</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['sharps_kg'] ?? 0 }} kg</h3>
                    <small class="text-gray-500">Sharps</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['disposed_kg'] ?? 0 }} kg</h3>
                    <small class="text-gray-500">Properly Disposed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Log #</th>
                                    <th>Date</th>
                                    <th>Waste Type</th>
                                    <th>Weight (kg)</th>
                                    <th>Source</th>
                                    <th>Disposal Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wasteLogs as $log)
                                    <tr>
                                        <td><code>{{ $log->log_number }}</code></td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'infectious' => 'danger',
                                                    'sharps' => 'warning',
                                                    'pharmaceutical' => 'info',
                                                    'general' => 'secondary',
                                                    'chemical' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$log->waste_type] ?? 'secondary'  }}">
                                                {{ ucfirst($log->waste_type ?? '-') }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $log->weight_kg ?? 0 }} kg</strong></td>
                                        <td>{{ $log->source_department ?? '-' }}</td>
                                        <td>{{ ucfirst($log->disposal_method ?? '-') }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'collected' => 'info',
                                                    'disposed' => 'success',
                                                    'incinerated' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$log->status] ?? 'secondary'  }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No waste logs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $wasteLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
