<x-app-layout>
    <x-slot name="header">{{ __('Triage Assessment Details') }} - {{ $assessment->triage_code }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.triage.edit', $assessment) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
        <a href="{{ route('healthcare.triage.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Priority Banner -->
            <div
                class="overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-8
                @if ($assessment->priority_level === 'critical') border-red-600 bg-red-50
                @elseif($assessment->priority_level === 'emergency') border-orange-500 bg-orange-50
                @elseif($assessment->priority_level === 'urgent') border-yellow-500 bg-yellow-50
                @elseif($assessment->priority_level === 'semi_urgent') border-green-500 bg-green-50
                @else border-blue-500 bg-blue-50 @endif">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span
                                class="px-4 py-2 text-2xl font-bold rounded-full
                                @if ($assessment->priority_level === 'critical') bg-red-600 text-white
                                @elseif($assessment->priority_level === 'emergency') bg-orange-500 text-white
                                @elseif($assessment->priority_level === 'urgent') bg-yellow-500 text-white
                                @elseif($assessment->priority_level === 'semi_urgent') bg-green-500 text-white
                                @else bg-blue-500 text-white @endif">
                                {{ $assessment->triage_code }}
                            </span>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">{{ ucfirst($assessment->priority_level) }}
                                    Priority</h3>
                                <p class="text-sm text-gray-600">{{ $assessment->patient?->name ?? 'Unknown Patient' }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full
                            @if ($assessment->status === 'pending') bg-gray-200 text-gray-800
                            @elseif($assessment->status === 'in_progress') bg-blue-200 text-blue-800
                            @else bg-green-200 text-green-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Patient Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Patient Information
                        </h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Patient Name</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $assessment->patient?->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Medical Record Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $assessment->patient?->medical_record_number ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assessment Time</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $assessment->assessment_time ? $assessment->assessment_time->format('d/m/Y H:i') : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Triage Nurse</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $assessment->nurse?->name ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Chief Complaint -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-notes-medical mr-2 text-red-600"></i>Chief Complaint
                        </h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $assessment->chief_complaint }}</p>
                        @if ($assessment->notes)
                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Notes</h4>
                                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $assessment->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Vital Signs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-heartbeat mr-2 text-red-600"></i>Vital Signs
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-tachometer-alt text-3xl text-blue-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Blood Pressure</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $assessment->blood_pressure_systolic ?? '-' }}/{{ $assessment->blood_pressure_diastolic ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500">mmHg</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-heart text-3xl text-red-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Heart Rate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->heart_rate ?? '-' }}</p>
                            <p class="text-xs text-gray-500">bpm</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-thermometer-half text-3xl text-orange-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Temperature</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->temperature ?? '-' }}</p>
                            <p class="text-xs text-gray-500">°C</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-lungs text-3xl text-green-600 mb-2"></i>
                            <p class="text-sm text-gray-500">O2 Saturation</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->oxygen_saturation ?? '-' }}</p>
                            <p class="text-xs text-gray-500">%</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-wind text-3xl text-purple-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Respiratory Rate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->respiratory_rate ?? '-' }}</p>
                            <p class="text-xs text-gray-500">breaths/min</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-exclamation-circle text-3xl text-yellow-600 mb-2"></i>
                            <p class="text-sm text-gray-500">Pain Score</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->pain_score ?? '-' }}/10</p>
                            <p class="text-xs text-gray-500">0-10 scale</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-brain text-3xl text-indigo-600 mb-2"></i>
                            <p class="text-sm text-gray-500">GCS Score</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $assessment->gcs ?? '-' }}/15</p>
                            <p class="text-xs text-gray-500">3-15 scale</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $assessment->created_at->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $assessment->updated_at->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
