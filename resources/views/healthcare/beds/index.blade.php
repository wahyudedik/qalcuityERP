<x-app-layout>
    <x-slot name="header">Manajemen Tempat Tidur</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Tempat Tidur'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('healthcare.beds.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-medium text-sm text-white hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Tempat Tidur
                </a>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-bed text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Beds</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_beds'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Available</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['available_beds'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <i class="fas fa-user-injured text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupied</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['occupied_beds'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-tools text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Maintenance</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['maintenance_beds'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('healthcare.beds.index') }}" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ward</label>
                        <select name="ward_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Wards</option>
                            @foreach ($wards as $ward)
                                <option value="{{ $ward->id }}" @selected(request('ward_id') == $ward->id)>{{ $ward->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="available" @selected(request('status') === 'available')>Available</option>
                            <option value="occupied" @selected(request('status') === 'occupied')>Occupied</option>
                            <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
                            <option value="reserved" @selected(request('status') === 'reserved')>Reserved</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Beds Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    @forelse($beds as $bed)
                        <div class="border-2 rounded-lg p-4 text-center cursor-pointer hover:shadow-lg transition
                        @if ($bed->status === 'available') border-green-300 bg-green-50
                        @elseif($bed->status === 'occupied') border-red-300 bg-red-50
                        @elseif($bed->status === 'maintenance') border-yellow-300 bg-yellow-50
                        @elseif($bed->status === 'reserved') border-blue-300 bg-blue-50
                        @else border-gray-300 bg-gray-50 @endif"
                            onclick="window.location.href='{{ route('healthcare.beds.show', $bed) }}'">
                            <i
                                class="fas fa-bed text-3xl mb-2
                            @if ($bed->status === 'available') text-green-600
                            @elseif($bed->status === 'occupied') text-red-600
                            @elseif($bed->status === 'maintenance') text-yellow-600
                            @elseif($bed->status === 'reserved') text-blue-600
                            @else text-gray-600 @endif"></i>
                            <p class="text-sm font-semibold text-gray-900">{{ $bed->bed_number }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $bed->ward ? $bed->ward->ward_code : 'No Ward' }}
                            </p>
                            <span
                                class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full
                            @if ($bed->status === 'available') bg-green-200 text-green-800
                            @elseif($bed->status === 'occupied') bg-red-200 text-red-800
                            @elseif($bed->status === 'maintenance') bg-yellow-200 text-yellow-800
                            @elseif($bed->status === 'reserved') bg-blue-200 text-blue-800
                            @else bg-gray-200 text-gray-800 @endif">
                                {{ ucfirst($bed->status) }}
                            </span>
                            @if ($bed->status === 'occupied' && $bed->patient)
                                <p class="text-xs text-gray-700 mt-1 truncate">{{ $bed->patient->name }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500">
                            <i class="fas fa-bed text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg">No beds found</p>
                        </div>
                    @endforelse
                </div>

                @if ($beds->hasPages())
                    <div class="mt-6">
                        {{ $beds->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
