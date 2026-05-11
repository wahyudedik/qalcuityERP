<x-app-layout>
    <x-slot name="header">{{ __('Bed Details') }} - {{ $bed->bed_number }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.beds.edit', $bed) }}"
            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
            <i class="fas fa-edit mr-2"></i>Edit
        </a>
        <a href="{{ route('healthcare.beds.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Bed Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <i
                                class="fas fa-bed text-6xl
                                @if ($bed->status === 'available') text-green-600
                                @elseif($bed->status === 'occupied') text-red-600
                                @elseif($bed->status === 'maintenance') text-yellow-600
                                @else text-blue-600 @endif"></i>
                            <h3 class="text-2xl font-bold mt-4">{{ $bed->bed_number }}</h3>
                            <span
                                class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full
                                @if ($bed->status === 'available') bg-green-100 text-green-800
                                @elseif($bed->status === 'occupied') bg-red-100 text-red-800
                                @elseif($bed->status === 'maintenance') bg-yellow-100 text-yellow-800
                                @elseif($bed->status === 'reserved') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($bed->status) }}
                            </span>
                        </div>

                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bed Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($bed->bed_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ward</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $bed->ward ? $bed->ward?->name : 'Not assigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Room Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bed->room_number ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bed->floor ?: '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Pricing & Amenities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Amenities</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rate Per Day</dt>
                                <dd class="mt-1 text-2xl font-bold text-blue-600">Rp
                                    {{ number_format($bed->rate_per_day, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amenities</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bed->amenities ?: 'No amenities listed' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($bed->is_active) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $bed->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            @if ($bed->status === 'occupied' && $bed->patientVisit)
                <!-- Current Patient Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user-injured mr-2 text-red-600"></i>Current Patient
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Patient Name</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $bed->patientVisit?->patient->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Admission Date</p>
                                <p class="mt-1 text-lg text-gray-900">
                                    {{ $bed->patientVisit?->admission_date ? $bed->patientVisit?->admission_date->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Doctor</p>
                                <p class="mt-1 text-lg text-gray-900">{{ $bed->patientVisit?->doctor->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <form action="{{ route('healthcare.beds.release', $bed) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                    data-confirm="Are you sure you want to release this bed?"
                                    data-confirm-type="danger">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Release Bed
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if ($bed->status === 'available')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="flex space-x-4">
                            <button onclick="document.getElementById('assignModal').classList.remove('hidden')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-user-plus mr-2"></i>Assign Patient
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Assign Patient Modal -->
                <div id="assignModal"
                    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Patient to Bed</h3>
                            <form action="{{ route('healthcare.beds.assign-patient', $bed) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Patient Visit</label>
                                    <select name="patient_visit_id" required
                                        class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select Visit</option>
                                        <!-- You would load this dynamically -->
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button"
                                        onclick="document.getElementById('assignModal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Assign
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bed->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bed->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if ($bed->occupied_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Occupied</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bed->occupied_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
