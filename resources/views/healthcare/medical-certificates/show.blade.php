<x-app-layout>
    <x-slot name="header">{{ __('Certificate Details') }} -
                {{ $certificate->certificate_number }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.medical-certificates.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0"><i class="fas fa-certificate text-blue-500 text-2xl"></i></div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-semibold">
                            {{ ucfirst(str_replace('_', ' ', $certificate->certificate_type)) }}</p>
                        <p class="text-xs text-blue-600">Certificate No: {{ $certificate->certificate_number }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-user mr-2 text-blue-600"></i>Patient & Certificate Info</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-lg text-gray-900">{{ $certificate->patient?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $certificate->status === 'active' ? 'bg-green-100 text-green-800' : ($certificate->status === 'expired' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($certificate->status) }}</span>
                            </dd>
                        </div>
                        @if ($certificate->issue_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $certificate->issue_date->format('d/m/Y') }}
                                </dd>
                            </div>
                        @endif
                        @if ($certificate->valid_until)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $certificate->valid_until->format('d/m/Y') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-stethoscope mr-2 text-red-600"></i>Medical Information</h3>
                    <dl class="space-y-4">
                        @if ($certificate->diagnosis)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $certificate->diagnosis }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Issuing Doctor</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->doctor_name }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-file-alt mr-2 text-purple-600"></i>Certificate Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $certificate->description }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
