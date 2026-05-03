<x-app-layout>
    <x-slot name="header">Patient Portal Dashboard</x-slot>

    @php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    @endphp

    @if (!$patient)
        <div
            class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
            <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <h3 class="text-lg font-bold text-red-900 mb-2">Patient Profile Not Found</h3>
            <p class="text-sm text-red-700">Please contact reception to link your account with patient
                profile.</p>
        </div>
    @else
        {{-- Welcome Banner --}}
        <div
            class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Welcome, {{ $patient->name }}!</h2>
                    <p class="text-blue-100">Patient ID: <span
                            class="font-mono">{{ $patient->patient_id ?? 'N/A' }}</span></p>
                    <p class="text-sm text-blue-100 mt-1">Last visit:
                        {{ $patient->lastVisit ? $patient->lastVisit->visit_date->format('d M Y') : 'No visits yet' }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <p class="text-xs text-blue-100">Next Appointment</p>
                        @if (isset($nextAppointment) && $nextAppointment)
                            <p class="text-lg font-bold">{{ $nextAppointment->appointment_date->format('d M Y') }}</p>
                            <p class="text-sm">{{ $nextAppointment->appointment_date->format('H:i') }} -
                                {{ $nextAppointment->doctor ? $nextAppointment->doctor->name : '-' }}</p>
                        @else
                            <p class="text-sm">No upcoming appointments</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Total Visits</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statistics['total_visits'] ?? 0 }}
                </p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Prescriptions</p>
                <p class="text-2xl font-bold text-green-600 mt-1">
                    {{ $statistics['total_prescriptions'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Lab Tests</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">
                    {{ $statistics['total_lab_orders'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Pending Bills</p>
                <p class="text-lg font-bold text-red-600 mt-1">Rp
                    {{ number_format($statistics['pending_bills'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('healthcare.portal.appointments') }}"
                class="bg-white rounded-2xl p-4 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">Book Appointment</p>
                </div>
            </a>
            <a href="{{ route('healthcare.portal.records') }}"
                class="bg-white rounded-2xl p-4 border border-gray-200 hover:border-green-500 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">Medical Records</p>
                </div>
            </a>
            <a href="{{ route('healthcare.portal.billing') }}"
                class="bg-white rounded-2xl p-4 border border-gray-200 hover:border-purple-500 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">View Bills</p>
                </div>
            </a>
            <a href="{{ route('healthcare.portal.prescriptions') }}"
                class="bg-white rounded-2xl p-4 border border-gray-200 hover:border-amber-500 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">Prescriptions</p>
                </div>
            </a>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Upcoming Appointments --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Upcoming Appointments</h3>
                    <a href="{{ route('healthcare.portal.appointments') }}"
                        class="text-sm text-blue-600 hover:underline">View All</a>
                </div>
                <div class="space-y-3">
                    @php
                        $appointments = \App\Models\Appointment::where('patient_id', $patient->id)
                            ->where('appointment_date', '>=', now())
                            ->where('status', 'scheduled')
                            ->orderBy('appointment_date')
                            ->limit(3)
                            ->get();
                    @endphp
                    @forelse($appointments as $appointment)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span
                                    class="text-lg font-bold text-blue-600">{{ $appointment->appointment_date->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $appointment->doctor ? $appointment->doctor->name : '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $appointment->appointment_date->format('d M Y • H:i') }}</p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No upcoming appointments
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Lab Results --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Recent Lab Results</h3>
                    <a href="{{ route('healthcare.portal.records') }}"
                        class="text-sm text-blue-600 hover:underline">View All</a>
                </div>
                <div class="space-y-3">
                    @php
                        $labResults = \App\Models\LabResult::where('patient_id', $patient->id)
                            ->where('status', 'completed')
                            ->orderBy('result_date', 'desc')
                            ->limit(3)
                            ->get();
                    @endphp
                    @forelse($labResults as $result)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div
                                class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $result->test_name ?? 'Lab Test' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $result->result_date ? \Carbon\Carbon::parse($result->result_date)->format('d M Y') : '-' }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">
                                Completed
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No lab results available
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
