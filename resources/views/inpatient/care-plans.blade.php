@extends('layouts.app')

@section('title', 'Care Plans')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list text-primary"></i> Care Plans
            </h1>
            <p class="text-muted mb-0">Patient care plans and nursing interventions</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus"></i> Create Care Plan
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        @forelse($carePlans as $plan)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $plan->patient?->name ?? 'N/A' }}</strong>
                            <span
                                class="badge bg-{{ $plan->status == 'active' ? 'success' : ($plan->status == 'completed' ? 'secondary' : 'warning') }} ms-2">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Created</small>
                                <strong>{{ $plan->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Target Date</small>
                                <strong>{{ $plan->target_date?->format('d/m/Y') ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Assigned Nurse</small>
                                <strong>{{ $plan->assigned_nurse?->name ?? 'N/A' }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-primary">Diagnosis & Goals</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-2"><strong>Nursing Diagnosis:</strong> {{ $plan->diagnosis ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Goals:</strong> {{ $plan->goals ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-primary">Interventions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
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
                                                        class="badge bg-{{ $intervention['status'] == 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($intervention['status'] ?? 'pending') }}
                                                    </span>
                                                </td>
                                                <td>{{ $intervention['notes'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No interventions added
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h6 class="text-primary">Evaluation</h6>
                            <p class="bg-light p-3 rounded mb-0">{{ $plan->evaluation ?? 'Pending evaluation' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No care plans created yet</p>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($admittedPatients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Care Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
