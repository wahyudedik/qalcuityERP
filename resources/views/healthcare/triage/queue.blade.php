<x-app-layout>
    <x-slot name="header">{{ __('Triage Queue') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.triage.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Assessments
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Queue Header -->
            <div class="bg-gradient-to-r from-red-600 to-orange-600 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-white">
                            <i class="fas fa-list-ol mr-3"></i>Emergency Triage Queue
                        </h3>
                        <p class="text-white opacity-90 mt-1">Patients sorted by priority level</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold text-white">{{ $queue->count() }}</p>
                        <p class="text-white opacity-90">Patients Waiting</p>
                    </div>
                </div>
            </div>

            <!-- Priority Legend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Priority Levels</h4>
                <div class="grid grid-cols-5 gap-4">
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 bg-red-600 rounded mr-2"></span>
                        <span class="text-sm text-gray-700">T1 - Critical (Immediate)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 bg-orange-500 rounded mr-2"></span>
                        <span class="text-sm text-gray-700">T2 - Emergency (< 10 min)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 bg-yellow-500 rounded mr-2"></span>
                        <span class="text-sm text-gray-700">T3 - Urgent (< 60 min)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 bg-green-500 rounded mr-2"></span>
                        <span class="text-sm text-gray-700">T4 - Semi-Urgent (< 120 min)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 bg-blue-500 rounded mr-2"></span>
                        <span class="text-sm text-gray-700">T5 - Non-Urgent (< 240 min)</span>
                    </div>
                </div>
            </div>

            <!-- Queue List -->
            <div class="space-y-4">
                @forelse($queue as $index => $assessment)
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8
                    @if ($assessment->priority_level === 'critical') border-red-600
                    @elseif($assessment->priority_level === 'emergency') border-orange-500
                    @elseif($assessment->priority_level === 'urgent') border-yellow-500
                    @elseif($assessment->priority_level === 'semi_urgent') border-green-500
                    @else border-blue-500 @endif">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4">
                                    <!-- Queue Number -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold
                                        @if ($assessment->priority_level === 'critical') bg-red-600
                                        @elseif($assessment->priority_level === 'emergency') bg-orange-500
                                        @elseif($assessment->priority_level === 'urgent') bg-yellow-500
                                        @elseif($assessment->priority_level === 'semi_urgent') bg-green-500
                                        @else bg-blue-500 @endif">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>

                                    <!-- Patient Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900">
                                                {{ $assessment->patient->name ?? 'Unknown' }}</h4>
                                            <span
                                                class="px-3 py-1 text-sm font-semibold rounded-full
                                            @if ($assessment->priority_level === 'critical') bg-red-100 text-red-800
                                            @elseif($assessment->priority_level === 'emergency') bg-orange-100 text-orange-800
                                            @elseif($assessment->priority_level === 'urgent') bg-yellow-100 text-yellow-800
                                            @elseif($assessment->priority_level === 'semi_urgent') bg-green-100 text-green-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                                {{ $assessment->triage_code }}
                                            </span>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($assessment->status === 'pending') bg-gray-100 text-gray-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500">Chief Complaint</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ Str::limit($assessment->chief_complaint, 50) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Vital Signs</p>
                                                <p class="font-medium text-gray-900">
                                                    BP:
                                                    {{ $assessment->blood_pressure_systolic ?? '-' }}/{{ $assessment->blood_pressure_diastolic ?? '-' }}
                                                    | HR: {{ $assessment->heart_rate ?? '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Assessment Time</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $assessment->assessment_time ? $assessment->assessment_time->format('H:i') : '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Triage Nurse</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $assessment->nurse->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('healthcare.triage.show', $assessment) }}"
                                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                        <i class="fas fa-eye mr-1"></i>Details
                                    </a>
                                    @if ($assessment->status === 'pending')
                                        <form action="{{ route('healthcare.triage.update', $assessment) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit"
                                                class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                                <i class="fas fa-play mr-1"></i>Start
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                        <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Queue is Empty</h3>
                        <p class="text-gray-500">No patients waiting for triage assessment</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
