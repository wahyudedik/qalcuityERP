<x-app-layout>
    <x-slot name="header">My Appointments</x-slot>

    @php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    @endphp

    @if (!$patient)
        <div
            class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
            <p class="text-sm text-red-700">Patient profile not found. Please contact reception.</p>
        </div>
    @else
        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            @php
                $upcomingAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('appointment_date', '>=', now())
                    ->where('status', 'scheduled')
                    ->count();
                $completedAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('status', 'completed')
                    ->count();
                $cancelledAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('status', 'cancelled')
                    ->count();
                $todayAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->whereDate('appointment_date', today())
                    ->where('status', 'scheduled')
                    ->count();
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Upcoming</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $upcomingAppointments }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Today</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $todayAppointments }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-gray-600 mt-1">{{ $completedAppointments }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Cancelled</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $cancelledAppointments }}</p>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="bg-white rounded-2xl border border-gray-200 mb-6">
            <div class="p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <select name="status"
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Status</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                        <option value="no_show" @selected(request('status') === 'no_show')>No Show</option>
                    </select>
                    <input type="date" name="from" value="{{ request('from') }}"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <input type="date" name="to" value="{{ request('to') }}"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        {{-- Appointments List --}}
        <div class="space-y-4">
            @php
                $appointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->when(request('status'), function ($q) {
                        $q->where('status', request('status'));
                    })
                    ->when(request('from'), function ($q) {
                        $q->whereDate('appointment_date', '>=', request('from'));
                    })
                    ->when(request('to'), function ($q) {
                        $q->whereDate('appointment_date', '<=', request('to'));
                    })
                    ->orderBy('appointment_date', 'desc')
                    ->paginate(10);
            @endphp

            @forelse($appointments as $appointment)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        {{-- Date Badge --}}
                        <div class="flex-shrink-0">
                            <div
                                class="w-20 h-20 bg-blue-100 rounded-xl flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-blue-600">
                                    {{ $appointment->appointment_date->format('d') }}
                                </span>
                                <span class="text-xs text-blue-600">
                                    {{ $appointment->appointment_date->format('M') }}
                                </span>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $appointment->doctor ? $appointment->doctor?->name : 'Doctor Not Assigned' }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $appointment->doctor ? $appointment->doctor?->specialization ?? '-' : '-' }}
                                    </p>
                                </div>
                                @if ($appointment->status === 'scheduled')
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">
                                        Scheduled
                                    </span>
                                @elseif($appointment->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                @elseif($appointment->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">
                                        Cancelled
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-600">
                                        {{ $appointment->appointment_date->format('H:i') }} -
                                        {{ $appointment->appointment_date->format('d M Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-gray-600">
                                        {{ $appointment->department ?? 'General' }}
                                    </span>
                                </div>
                            </div>

                            @if ($appointment->notes)
                                <p
                                    class="mt-3 text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                    <span class="font-medium">Notes:</span> {{ $appointment->notes }}
                                </p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex sm:flex-col gap-2">
                            @if ($appointment->status === 'scheduled')
                                <button onclick="cancelAppointment({{ $appointment->id }})"
                                    class="flex-1 sm:flex-none px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                                    Cancel
                                </button>
                                <button onclick="rescheduleAppointment({{ $appointment->id }})"
                                    class="flex-1 sm:flex-none px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                    Reschedule
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">No Appointments Found</h3>
                    <p class="text-sm text-gray-500 mb-4">You don't have any appointments matching
                        the selected filters.</p>
                    <a href="{{ route('healthcare.portal.appointments.create') }}"
                        class="inline-flex items-center px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Book New Appointment
                    </a>
                </div>
            @endforelse

            @if ($appointments->hasPages())
                <div class="bg-white rounded-2xl border border-gray-200 p-4">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    @endif

    @push('scripts')
        <script>
            function cancelAppointment(id) {
                if (confirm('Are you sure you want to cancel this appointment?')) {
                    fetch(`/healthcare/portal/appointments/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                }
            }

            function rescheduleAppointment(id) {
                window.location.href = `/healthcare/portal/appointments/${id}/reschedule`;
            }
        </script>
    @endpush
</x-app-layout>
