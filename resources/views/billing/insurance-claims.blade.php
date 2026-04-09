@extends('layouts.app')

@section('title', 'Insurance Claims')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> Insurance Claims
            </h1>
            <p class="text-muted mb-0">Manage and track insurance claim submissions</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $claims->where('status', 'pending')->count() }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $claims->where('status', 'submitted')->count() }}</h3>
                    <small class="text-muted">Submitted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $claims->where('status', 'approved')->count() }}</h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $claims->where('status', 'rejected')->count() }}</h3>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Claim #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Insurance</th>
                                    <th>Amount</th>
                                    <th>Approved</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr>
                                        <td><code>{{ $claim->claim_number }}</code></td>
                                        <td>{{ $claim->claim_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $claim->patient) }}">
                                                {{ $claim->patient->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $claim->insurance_provider ?? '-' }}</td>
                                        <td><strong>Rp {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if ($claim->approved_amount)
                                                <strong class="text-success">Rp
                                                    {{ number_format($claim->approved_amount, 0, ',', '.') }}</strong>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'submitted' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'partial' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$claim->status] ?? 'secondary' }}">
                                                {{ ucfirst($claim->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($claim->status == 'pending')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-paper-plane"></i> Submit
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No insurance claims found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $claims->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
