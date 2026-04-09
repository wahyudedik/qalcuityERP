<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Telemedicine Consultation Details') }} -
                {{ $telemedicine->consultation_id }}</h2>
            <a href="{{ route('healthcare.telemedicine.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($telemedicine->status === 'scheduled')
                <div class="bg-green-600 text-white p-4 rounded-lg mb-6">
                    <a href="{{ route('healthcare.telemedicine.join', $telemedicine) }}"
                        class="inline-flex items-center px-6 py-3 bg-white text-green-600 rounded-md font-semibold hover:bg-gray-100">
                        <i class="fas fa-video mr-2"></i>Join Video Call
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Consultation Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Consultation ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $telemedicine->consultation_id }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $telemedicine->patient->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Doctor</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $telemedicine->doctor->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($telemedicine->consultation_type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $telemedicine->status === 'completed' ? 'bg-green-100 text-green-800' : ($telemedicine->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst(str_replace('_', ' ', $telemedicine->status)) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timeline</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scheduled At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $telemedicine->scheduled_at ? $telemedicine->scheduled_at->format('d/m/Y H:i') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Started At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $telemedicine->started_at ? $telemedicine->started_at->format('d/m/Y H:i') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ended At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $telemedicine->ended_at ? $telemedicine->ended_at->format('d/m/Y H:i') : '-' }}</dd>
                        </div>
                        @if ($telemedicine->duration)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $telemedicine->duration }} minutes</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-notes-medical mr-2 text-red-600"></i>Clinical Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Chief Complaint</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                {{ $telemedicine->chief_complaint }}</dd>
                        </div>
                        @if ($telemedicine->diagnosis)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    {{ $telemedicine->diagnosis }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-prescription mr-2 text-green-600"></i>Prescription & Notes</h3>
                    @if ($telemedicine->prescription)
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Prescription</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    {{ $telemedicine->prescription }}</dd>
                            </div>
                        </dl>
                    @endif
                    @if ($telemedicine->notes)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $telemedicine->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
