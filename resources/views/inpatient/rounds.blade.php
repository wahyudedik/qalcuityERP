<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-user-md text-blue-600"></i> Doctor Rounds
            </h1>
            <p class="text-gray-500">Daily ward rounds and patient assessments</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addRoundModal">
                <i class="fas fa-plus"></i> Record Round
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="w-full md:w-1/3">
                            <form method="GET" class="flex gap-2">
                                <input type="date" name="date" class="form-control"
                                    value="{{ request('date', today()->format('Y-m-d')) }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="w-full md:w-2/3 text-right">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 mr-2">{{ count($rounds) }} rounds today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($rounds as $round)
            <div class="w-full mb-3">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            <strong>{{ $round->round_time?->format('H:i') ?? '-' }}</strong>
                            <span class="ms-3">
                                <i class="fas fa-user-md text-blue-600 mr-1"></i>
                                {{ $round->doctor?->name ?? '-' }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Patient Information</h6>
                                <div class="mb-2">
                                    <strong>{{ $round->patient?->name ?? '-' }}</strong>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700 ml-2">{{ $round->patient?->medical_record_number ?? '' }}</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-bed mr-1"></i> Bed: {{ $round->bed?->bed_number ?? '-' }} |
                                    Ward: {{ $round->ward?->name ?? '-' }}
                                </div>
                            </div>
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Assessment</h6>
                                <div class="mb-2">
                                    <span
                                        class="badge bg-{{ $round->condition == 'sw-full text-sm text-left' ? 'emerald-500' : ($round->condition == 'critical' ? 'red-500' : 'amber-500')  }}">
                                        {{ ucfirst($round->condition ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Subjective</h6>
                                <p class="text-sm bg-gray-50 p-2 rounded">{{ $round->subjective ?? 'N/A' }}</p>
                            </div>
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Objective</h6>
                                <p class="text-sm bg-gray-50 p-2 rounded">{{ $round->objective ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Assessment</h6>
                                <p class="text-sm bg-gray-50 p-2 rounded">{{ $round->assessment ?? 'N/A' }}</p>
                            </div>
                            <div class="w-full md:w-1/2">
                                <h6 class="text-blue-600 mb-2">Plan</h6>
                                <p class="text-sm bg-gray-50 p-2 rounded">{{ $round->plan ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if ($round->follow_up_needed)
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Follow-up needed:</strong> {{ $round->follow_up_notes ?? 'Schedule follow-up' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-user-md fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No rounds recorded for this date</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Round Modal -->
    <div class="modal fade" id="addRoundModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('healthcare.inpatient.rounds.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Doctor Round</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($admittedPatients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/3">
                                <label class="form-label">Round Time</label>
                                <input type="time" name="round_time" class="form-control" required>
                            </div>
                            <div class="w-full md:w-1/3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select" required>
                                    <option value="stable">Stable</option>
                                    <option value="improving">Improving</option>
                                    <option value="deteriorating">Deteriorating</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Subjective</label>
                                <textarea name="subjective" class="form-control" rows="3" placeholder="Patient complaints, symptoms..."></textarea>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Objective</label>
                                <textarea name="objective" class="form-control" rows="3"
                                    placeholder="Physical examination findings, vitals..."></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Assessment</label>
                                <textarea name="assessment" class="form-control" rows="3" placeholder="Diagnosis, clinical assessment..."></textarea>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Plan</label>
                                <textarea name="plan" class="form-control" rows="3" placeholder="Treatment plan, medications, orders..."></textarea>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="follow_up_needed" id="followUpNeeded">
                            <label class="form-check-label" for="followUpNeeded">Follow-up needed</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control" rows="2" placeholder="When and what to follow up..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Save Round</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
