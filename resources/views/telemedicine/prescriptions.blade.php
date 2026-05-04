<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-prescription text-blue-600"></i> E-Prescriptions
            </h1>
            <p class="text-gray-500">Prescriptions from teleconsultations</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Rx #</th>
                                    <th>Date</th>
                                    <th>Doctor</th>
                                    <th>Consultation</th>
                                    <th>Medications</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prescriptions as $rx)
                                    <tr>
                                        <td><code>{{ $rx->prescription_number }}</code></td>
                                        <td>{{ $rx->prescribed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $rx->doctor?->name ?? '-' }}</td>
                                        <td>
                                            @if ($rx->consultation)
                                                <a
                                                    href="{{ route('healthcare.telemedicine.consultations.show', $rx->consultation) }}">
                                                    #{{ $rx->consultation?->consultation_number }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ count($rx->medications ?? []) }} medications</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'completed' => 'secondary',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$rx->status] ?? 'secondary'  }}">
                                                {{ ucfirst($rx->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition" data-bs-toggle="modal"
                                                    data-bs-target="#viewRxModal{{ $rx->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Prescription Modal -->
                                    <div class="modal fade" id="viewRxModal{{ $rx->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">E-Prescription - {{ $rx->prescription_number }}
                                                    </h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Doctor:</strong> {{ $rx->doctor?->name ?? '-' }}
                                                            <br><strong>Date:</strong>
                                                            {{ $rx->prescribed_at?->format('d/m/Y H:i') ?? '-' }}
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Patient:</strong> {{ $rx->patient?->name ?? '-' }}
                                                            <br><strong>Status:</strong>
                                                            <span
                                                                class="badge bg-{{ $statusColors[$rx->status] ?? 'secondary'  }}">
                                                                {{ ucfirst($rx->status) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <h6 class="text-blue-600">Medications</h6>
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-sm text-left">
                                                            <thead>
                                                                <tr>
                                                                    <th>Medication</th>
                                                                    <th>Dosage</th>
                                                                    <th>Frequency</th>
                                                                    <th>Duration</th>
                                                                    <th>Instructions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($rx->medications ?? [] as $med)
                                                                    <tr>
                                                                        <td><strong>{{ $med['name'] ?? '-' }}</strong></td>
                                                                        <td>{{ $med['dosage'] ?? '-' }}</td>
                                                                        <td>{{ $med['frequency'] ?? '-' }}</td>
                                                                        <td>{{ $med['duration'] ?? '-' }}</td>
                                                                        <td>{{ $med['instructions'] ?? '-' }}</td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="5" class="text-center text-gray-400">No
                                                                            medications</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    @if ($rx->notes)
                                                        <div class="mt-3">
                                                            <h6 class="text-blue-600">Doctor's Notes</h6>
                                                            <div class="alert alert-info">{{ $rx->notes }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 text-gray-400">No prescriptions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $prescriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
