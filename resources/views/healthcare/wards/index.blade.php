<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ward Management') }}
            </h2>
            <a href="{{ route('healthcare.wards.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Ward
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-hospital text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Wards</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_wards'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
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
                            <p class="text-sm font-medium text-gray-500">Available Beds</p>
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
                            <p class="text-sm font-medium text-gray-500">Occupied Beds</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['occupied_beds'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['occupancy_rate'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('healthcare.wards.index') }}" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ward Type</label>
                        <select name="ward_type"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="general" @selected(request('ward_type') === 'general')>General</option>
                            <option value="icu" @selected(request('ward_type') === 'icu')>ICU</option>
                            <option value="emergency" @selected(request('ward_type') === 'emergency')>Emergency</option>
                            <option value="maternity" @selected(request('ward_type') === 'maternity')>Maternity</option>
                            <option value="pediatric" @selected(request('ward_type') === 'pediatric')>Pediatric</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Wards Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Floor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Beds</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Occupancy</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($wards as $ward)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $ward->ward_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ward->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($ward->ward_type === 'icu') bg-red-100 text-red-800
                                    @elseif($ward->ward_type === 'emergency') bg-orange-100 text-orange-800
                                    @elseif($ward->ward_type === 'maternity') bg-pink-100 text-pink-800
                                    @elseif($ward->ward_type === 'pediatric') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($ward->ward_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ward->floor }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ward->beds_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-1 mr-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: {{ $ward->beds_count > 0 ? (($ward->beds_count - $ward->occupied_beds_count) / $ward->beds_count) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm text-gray-700">{{ $ward->occupied_beds_count }}/{{ $ward->beds_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($ward->is_active) bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                        {{ $ward->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('healthcare.wards.show', $ward) }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('healthcare.wards.edit', $ward) }}"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteWard({{ $ward->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-ward-{{ $ward->id }}"
                                        action="{{ route('healthcare.wards.destroy', $ward) }}" method="POST"
                                        class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No wards found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($wards->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $wards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteWard(id) {
                if (confirm('Are you sure you want to delete this ward?')) {
                    document.getElementById(`delete-ward-${id}`).submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
