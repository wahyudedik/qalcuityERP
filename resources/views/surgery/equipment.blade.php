<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-tools text-blue-600"></i> Surgery Equipment
            </h1>
            <p class="text-gray-500">Track and manage surgical equipment</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="fas fa-plus"></i> Add Equipment
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
                            <h3 class="text-emerald-600">{{ $equipment->where('status', 'available')->count() }}</h3>
                            <small class="text-gray-500">Available</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-amber-600">{{ $equipment->where('status', 'in_use')->count() }}</h3>
                            <small class="text-gray-500">In Use</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-sky-600">{{ $equipment->where('status', 'sterilizing')->count() }}</h3>
                            <small class="text-gray-500">Sterilizing</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-red-600">{{ $equipment->where('status', 'maintenance')->count() }}</h3>
                            <small class="text-gray-500">Maintenance</small>
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
                                    <th>Equipment</th>
                                    <th>Category</th>
                                    <th>Serial #</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Sterilized</th>
                                    <th>Next Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipment as $item)
                                    <tr>
                                        <td><strong>{{ $item->name }}</strong></td>
                                        <td>{{ ucfirst($item->category ?? '-') }}</td>
                                        <td><code>{{ $item->serial_number ?? '-' }}</code></td>
                                        <td>{{ $item->location ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'available' => 'success',
                                                    'in_use' => 'warning',
                                                    'sterilizing' => 'info',
                                                    'maintenance' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary'  }}">
                                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->last_sterilized)
                                                {{ $item->last_sterilized->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->next_maintenance)
                                                @if ($item->next_maintenance->isPast())
                                                    <span
                                                        class="text-red-600 font-bold">{{ $item->next_maintenance->format('d/m/Y') }}</span>
                                                @else
                                                    {{ $item->next_maintenance->format('d/m/Y') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No surgical equipment found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $equipment->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
