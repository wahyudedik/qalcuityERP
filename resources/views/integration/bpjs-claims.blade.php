@extends('layouts.app')

@section('title', 'BPJS Claims')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> BPJS Claims
            </h1>
            <p class="text-muted mb-0">Indonesian national insurance claim submission and tracking</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitClaimModal">
                <i class="fas fa-plus"></i> Submit Claim
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['approved'] ?? 0 }}</h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $stats['rejected'] ?? 0 }}</h3>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        Rp {{ number_format(($stats['total_amount'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-muted">Total Claims</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claims History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Patient</th>
                                    <th>Submission Date</th>
                                    <th>Diagnosis</th>
                                    <th>Claim Amount</th>
                                    <th>Status</th>
                                    <th>BPJS Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr>
                                        <td>
                                            <strong>{{ $claim->claim_id ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $claim->patient->name ?? '-' }}</strong>
                                                <br><small
                                                    class="text-muted">{{ $claim->patient->bpjs_number ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $claim->submission_date->format('d/m/Y') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $claim->diagnosis_code ?? '-' }}</small>
                                            <br><small
                                                class="text-muted">{{ Str::limit($claim->diagnosis_name ?? '-', 30) }}</small>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if ($claim->status == 'approved')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Approved
                                                </span>
                                            @elseif($claim->status == 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @elseif($claim->status == 'rejected')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($claim->approved_amount)
                                                <small class="text-success">
                                                    Approved: Rp {{ number_format($claim->approved_amount, 0, ',', '.') }}
                                                </small>
                                            @elseif($claim->rejection_reason)
                                                <small class="text-danger" title="{{ $claim->rejection_reason }}">
                                                    <i class="fas fa-exclamation-triangle"></i> Rejected
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#viewClaimModal{{ $claim->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($claim->status == 'rejected')
                                                    <button class="btn btn-sm btn-warning" title="Resubmit">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Claim Modal -->
                                    <div class="modal fade" id="viewClaimModal{{ $claim->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">BPJS Claim Details</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Claim ID:</strong>
                                                            <p>{{ $claim->claim_id ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                @if ($claim->status == 'approved')
                                                                    <span class="badge bg-success">Approved</span>
                                                                @elseif($claim->status == 'pending')
                                                                    <span class="badge bg-warning">Pending</span>
                                                                @else
                                                                    <span class="badge bg-danger">Rejected</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Patient Name:</strong>
                                                            <p>{{ $claim->patient->name ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>BPJS Number:</strong>
                                                            <p>{{ $claim->patient->bpjs_number ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Diagnosis:</strong>
                                                            <p>{{ $claim->diagnosis_code }} -
                                                                {{ $claim->diagnosis_name ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Procedure:</strong>
                                                            <p>{{ $claim->procedure_code ?? '-' }} -
                                                                {{ $claim->procedure_name ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Claim Amount:</strong>
                                                            <p class="text-primary">
                                                                <strong>Rp
                                                                    {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Approved Amount:</strong>
                                                            <p class="text-success">
                                                                <strong>Rp
                                                                    {{ number_format($claim->approved_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if ($claim->rejection_reason)
                                                        <div class="alert alert-danger">
                                                            <strong>Rejection Reason:</strong>
                                                            <p class="mb-0">{{ $claim->rejection_reason }}</p>
                                                        </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <strong>Submission Details:</strong>
                                                        <pre class="bg-light p-2 rounded"><code>{{ json_encode($claim->submission_data ?? [], JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No BPJS claims found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($claims) && $claims->hasPages())
                        <div class="mt-3">
                            {{ $claims->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Claim Modal -->
    <div class="modal fade" id="submitClaimModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit BPJS Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.bpjs-claims.submit') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->name }} - {{ $patient->bpjs_number ?? 'No BPJS Number' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis Code (ICD-10) <span class="text-danger">*</span></label>
                            <input type="text" name="diagnosis_code" class="form-control" required
                                placeholder="e.g., J06.9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Procedure Code (ICD-9-CM)</label>
                            <input type="text" name="procedure_code" class="form-control" placeholder="e.g., 47.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Amount <span class="text-danger">*</span></label>
                            <input type="number" name="claim_amount" class="form-control" required placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supporting Documents</label>
                            <textarea name="documents" class="form-control" rows="3" placeholder="List supporting documents..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
