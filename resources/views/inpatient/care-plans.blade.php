<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-clipboard-list text-blue-600"></i> Care Plans
            </h1>
            <p class="text-gray-500">Patient care plans and nursing interventions</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus"></i> Create Care Plan
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($carePlans as $plan)
            <div class="w-full mb-4">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <strong>{{ $plan->patient?->name ?? 'N/A' }}</strong>
                            <span
                                class="badge bg-{{ $plan->status == 'active' ? 'emerald-500' : ($plan->status == 'completed' ? 'secondary' : 'amber-500')  }} ml-2">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="w-full md:w-1/3">
                                <small class="text-gray-500 d-block">Created</small>
                                <strong>{{ $plan->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                            <div class="w-full md:w-1/3">
                                <small class="text-gray-500 d-block">Target Date</small>
                                <strong>{{ $plan->target_date?->format('d/m/Y') ?? 'N/A' }}</strong>
                            </div>
                            <div class="w-full md:w-1/3">
                                <small class="text-gray-500 d-block">Assigned Nurse</small>
                                <strong>{{ $plan->assigned_nurse?->name ?? 'N/A' }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-blue-600">Diagnosis & Goals</h6>
                            <div class="bg-gray-50 p-3 rounded">
                                <p class="mb-2"><strong>Nursing Diagnosis:</strong> {{ $plan->diagnosis ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Goals:</strong> {{ $plan->goals ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-blue-600">Interventions</h6>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left w-full text-sm text-left-bordered">
                                    <thead class="w-full text-sm text-left-light">
                                        <tr>
                                            <th>Intervention</th>
                                            <th>Frequency</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($plan->interventions ?? [] as $intervention)
                                            <tr>
                                                <td>{{ $intervention['description'] ?? '-' }}</td>
                                                <td>{{ $intervention['frequency'] ?? '-' }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $intervention['status'] == 'completed' ? 'emerald-500' : 'amber-500'  }}">
                                                        {{ ucfirst($intervention['status'] ?? 'pending') }}
                                                    </span>
                                                </td>
                                                <td>{{ $intervention['notes'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-gray-400">No interventions added
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h6 class="text-blue-600">Evaluation</h6>
                            <p class="bg-gray-50 p-3 rounded mb-0">{{ $plan->evaluation ?? 'Pending evaluation' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-clipboard-list fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No care plans created yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Care Plan Modal -->
    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('healthcare.inpatient.care-plans.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create Care Plan</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($admittedPatients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Target Date</label>
                                <input type="date" name="target_date" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nursing Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goals</label>
                            <textarea name="goals" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interventions (one per line)</label>
                            <textarea name="interventions" class="form-control" rows="4"
                                placeholder="Medication administration&#10;Wound care&#10;Vital signs monitoring"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Evaluation</label>
                            <textarea name="evaluation" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Create Care Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
