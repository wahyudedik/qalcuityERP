@extends('layouts.app')

@section('title', 'Insurance - ' . $patient->name)

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Insurance</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-alt text-primary"></i> Insurance Coverage
            </h1>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInsuranceModal">
                <i class="fas fa-plus"></i> Add Insurance
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        @forelse($insurances as $insurance)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $insurance->is_active ? 'success' : 'danger' }} me-2">
                                {{ $insurance->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <strong>{{ $insurance->insurance_provider ?? 'N/A' }}</strong>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Policy Number</small></div>
                            <div class="col-6"><strong>{{ $insurance->policy_number ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Group Number</small></div>
                            <div class="col-6"><strong>{{ $insurance->group_number ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Plan Type</small></div>
                            <div class="col-6"><strong>{{ $insurance->plan_type ?? 'N/A' }}</strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Valid Period</small></div>
                            <div class="col-6">
                                <strong>
                                    @if ($insurance->valid_from && $insurance->valid_until)
                                        {{ $insurance->valid_from->format('d/m/Y') }} -
                                        {{ $insurance->valid_until->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Coverage</small></div>
                            <div class="col-6"><strong>Rp
                                    {{ number_format($insurance->coverage_limit ?? 0, 0, ',', '.') }}</strong></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><small class="text-muted">Copay</small></div>
                            <div class="col-6"><strong>{{ $insurance->copay_percentage ?? 0 }}%</strong></div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $insurance->usage_percentage > 80 ? 'danger' : ($insurance->usage_percentage > 50 ? 'warning' : 'success') }}"
                                style="width: {{ $insurance->usage_percentage ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">Used: {{ $insurance->usage_percentage ?? 0 }}%</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No insurance policies added yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Insurance Modal -->
    <div class="modal fade" id="addInsuranceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.patients.insurance.store', $patient) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Insurance Policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Insurance Provider</label>
                            <input type="text" name="insurance_provider" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Policy Number</label>
                                <input type="text" name="policy_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Group Number</label>
                                <input type="text" name="group_number" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid From</label>
                                <input type="date" name="valid_from" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid Until</label>
                                <input type="date" name="valid_until" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Coverage Limit</label>
                                <input type="number" name="coverage_limit" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Copay %</label>
                                <input type="number" name="copay_percentage" class="form-control" min="0"
                                    max="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Insurance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
