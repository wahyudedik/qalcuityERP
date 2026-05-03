<x-app-layout>
    <x-slot name="header">{{ __('Ward Details') }} - {{ $ward->name }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.wards.edit', $ward) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
        <a href="{{ route('healthcare.wards.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Ward Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ward Information</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ward Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ward->ward_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ward->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if ($ward->ward_type === 'icu') bg-red-100 text-red-800
                                        @elseif($ward->ward_type === 'emergency') bg-orange-100 text-orange-800
                                        @elseif($ward->ward_type === 'maternity') bg-pink-100 text-pink-800
                                        @elseif($ward->ward_type === 'pediatric') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($ward->ward_type) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ward->floor }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if ($ward->is_active) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $ward->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Bed Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bed Statistics</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $ward->beds_count ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Occupied Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-red-600">
                                    {{ $ward->occupied_beds_count ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Available Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">
                                    {{ ($ward->beds_count ?? 0) - ($ward->occupied_beds_count ?? 0) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Occupancy Rate</dt>
                                <dd class="mt-1 text-2xl font-semibold text-blue-600">
                                    @php
                                        $occupancy =
                                            $ward->beds_count > 0
                                                ? round(
                                                    (($ward->occupied_beds_count ?? 0) / $ward->beds_count) * 100,
                                                    2,
                                                )
                                                : 0;
                                    @endphp
                                    {{ $occupancy }}%
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                        <p class="text-sm text-gray-700">{{ $ward->description ?: 'No description provided.' }}</p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Additional Information</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ward->created_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ward->updated_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Beds List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Beds in this Ward</h3>
                    @if ($ward->beds && $ward->beds->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach ($ward->beds as $bed)
                                <div
                                    class="border rounded-lg p-3 text-center
                            @if ($bed->status === 'available') bg-green-50 border-green-200
                            @elseif($bed->status === 'occupied') bg-red-50 border-red-200
                            @elseif($bed->status === 'maintenance') bg-yellow-50 border-yellow-200
                            @else bg-gray-50 border-gray-200 @endif">
                                    <i
                                        class="fas fa-bed text-2xl mb-2
                                @if ($bed->status === 'available') text-green-600
                                @elseif($bed->status === 'occupied') text-red-600
                                @elseif($bed->status === 'maintenance') text-yellow-600
                                @else text-gray-600 @endif"></i>
                                    <p class="text-sm font-medium">{{ $bed->bed_number }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($bed->status) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center">No beds assigned to this ward yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
