<x-app-layout>
    <x-slot name="header">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-0">
                    <i class="fas fa-pills text-blue-600"></i> Medication Dispensing
                </h1>
                <p class="text-gray-500">Process and track medication dispensing</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $pending->count() }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $in_progress->count() }}</h3>
                    <small class="text-gray-500">In Progress</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $completed->count() }}</h3>
                    <small class="text-gray-500">Dispensed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $expired->count() }}</h3>
                    <small class="text-gray-500">Expired</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Rx #</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Prescriber</th>
                                    <th>Prescribed</th>
                                    <th>Status</th>
                                    <th>Pharmacist</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prescriptions as $rx)
                                    <tr>
                                        <td><code>{{ $rx->prescription_number }}</code></td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $rx->patient) }}">
                                                {{ $rx->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            <strong>{{ $rx->medication_name ?? '-' }}</strong>
                                            <br><small class="text-gray-500">{{ $rx->dosage ?? '' }} -
                                                {{ $rx->frequency ?? '' }}</small>
                                        </td>
                                        <td>{{ $rx->prescriber?->name ?? '-' }}</td>
                                        <td>{{ $rx->prescribed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'dispensed' => 'success',
                                                    'expired' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$rx->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $rx->dispensed_by?->name ?? '-' }}</td>
                                        <td>
                                            @if ($rx->status == 'pending' || $rx->status == 'in_progress')
                                                <button
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition"
                                                    onclick="dispenseMedication({{ $rx->id }})">
                                                    <i class="fas fa-check"></i> Dispense
                                                </button>
                                            @else
                                                <button
                                                    class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition"
                                                    onclick="viewDetails({{ $rx->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No prescriptions to
                                            display
                                        </td>
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

    @push('scripts')
        <script>
            async function dispenseMedication(rxId) {
                const confirmed = await Dialog.confirm('Confirm medication dispensing?');
                if (!confirmed) return;
                // Implement AJAX dispensing
                window.location.reload();
            }

            function viewDetails(rxId) {
                // Implement view details modal
                window.location.href = '/healthcare/pharmacy/dispensing/' + rxId;
            }
        </script>
    @endpush
</x-app-layout>
