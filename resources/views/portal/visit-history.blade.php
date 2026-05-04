<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-history text-blue-600"></i> My Visit History
            </h1>
            <p class="text-gray-500">Complete record of your healthcare visits</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-blue-400">
                <div class="p-5 text-center">
                    <h3 class="text-blue-600">{{ $stats['total_visits'] ?? 0 }}</h3>
                    <small class="text-gray-500">Total Visits</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['last_visit_days'] ?? 0 }}</h3>
                    <small class="text-gray-500">Days Since Last Visit</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['upcoming_appointments'] ?? 0 }}</h3>
                    <small class="text-gray-500">Upcoming Appointments</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Visit Records</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visit Type</th>
                                    <th>Department</th>
                                    <th>Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visits as $visit)
                                    <tr>
                                        <td>
                                            <strong>{{ $visit->visit_date->format('d/m/Y') ?? '-' }}</strong>
                                            <br><small
                                                class="text-gray-500">{{ $visit->visit_date->diffForHumans() ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($visit->visit_type == 'consultation')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Consultation</span>
                                            @elseif($visit->visit_type == 'emergency')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Emergency</span>
                                            @elseif($visit->visit_type == 'follow_up')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Follow-up</span>
                                            @elseif($visit->visit_type == 'checkup')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Check-up</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($visit->visit_type ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $visit->department ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user-md fa-xs"></i>
                                                </div>
                                                <strong>{{ $visit->doctor_name ?? '-' }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $visit->diagnosis ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($visit->status == 'completed')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Completed</span>
                                            @elseif($visit->status == 'in_progress')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">In Progress</span>
                                            @elseif($visit->status == 'cancelled')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($visit->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                    data-bs-target="#visitDetailModal{{ $visit->id }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Download Summary"
                                                    onclick="window.print()">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Visit Detail Modal -->
                                    <div class="modal fade" id="visitDetailModal{{ $visit->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Visit Details</h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Visit Date:</strong>
                                                            <p>{{ $visit->visit_date->format('d/m/Y H:i') ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Visit Type:</strong>
                                                            <p>{{ ucfirst($visit->visit_type ?? '-') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Department:</strong>
                                                            <p>{{ $visit->department ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Doctor:</strong>
                                                            <p>{{ $visit->doctor_name ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Diagnosis:</strong>
                                                        <p>{{ $visit->diagnosis ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Treatment/Notes:</strong>
                                                        <p class="bg-gray-50 p-3 rounded" style="white-space: pre-wrap;">
                                                            {{ $visit->notes ?? 'N/A' }}</p>
                                                    </div>
                                                    @if ($visit->prescriptions)
                                                        <div class="mb-3">
                                                            <strong>Prescriptions:</strong>
                                                            <ul class="list-group">
                                                                @foreach ($visit->prescriptions as $prescription)
                                                                    <li class="list-group-item">
                                                                        <strong>{{ $prescription['medication'] ?? '-' }}</strong>
                                                                        <br><small>{{ $prescription['dosage'] ?? '-' }} -
                                                                            {{ $prescription['instructions'] ?? '-' }}</small>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10">
                                            <i class="fas fa-history fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No visit history available</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($visits) && $visits->hasPages())
                        <div class="mt-3">
                            {{ $visits->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
