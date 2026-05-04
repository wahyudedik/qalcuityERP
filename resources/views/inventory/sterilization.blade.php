<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-shield-virus text-blue-600"></i> Sterilization Tracking
            </h1>
            <p class="text-gray-500">Track equipment sterilization cycles</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addCycleModal">
                <i class="fas fa-plus"></i> Log Cycle
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
                            <h3 class="text-emerald-600">{{ $cycles->where('status', 'completed')->count() }}</h3>
                            <small class="text-gray-500">Completed Today</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-amber-600">{{ $cycles->where('status', 'in_progress')->count() }}</h3>
                            <small class="text-gray-500">In Progress</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-sky-600">{{ $equipment->where('status', 'sterile')->count() }}</h3>
                            <small class="text-gray-500">Sterile Items</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-red-600">{{ $equipment->where('status', 'contaminated')->count() }}</h3>
                            <small class="text-gray-500">Contaminated</small>
                        </div>
                    </div>
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
                                    <th>Cycle #</th>
                                    <th>Date/Time</th>
                                    <th>Equipment</th>
                                    <th>Sterilizer</th>
                                    <th>Method</th>
                                    <th>Duration</th>
                                    <th>Temperature</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cycles as $cycle)
                                    <tr>
                                        <td><code>{{ $cycle->cycle_number }}</code></td>
                                        <td>{{ $cycle->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td><strong>{{ $cycle->equipment_count ?? 0 }} items</strong></td>
                                        <td>{{ $cycle->sterilizer_name ?? '-' }}</td>
                                        <td>{{ ucfirst($cycle->method ?? '-') }}</td>
                                        <td>{{ $cycle->duration_minutes ?? '-' }} min</td>
                                        <td>{{ $cycle->temperature ?? '-' }}°C</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'completed' => 'success',
                                                    'in_progress' => 'warning',
                                                    'failed' => 'danger',
                                                    'scheduled' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$cycle->status] ?? 'secondary'  }}">
                                                {{ ucfirst(str_replace('_', ' ', $cycle->status)) }}
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
                                        <td colspan="9" class="text-center py-6 text-gray-400">No sterilization cycles found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $cycles->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
