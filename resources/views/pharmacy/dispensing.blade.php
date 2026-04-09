@extends('layouts.app')

@section('title', 'Medication Dispensing')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-pills text-primary"></i> Medication Dispensing
            </h1>
            <p class="text-muted mb-0">Process and track medication dispensing</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $pending->count() }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $in_progress->count() }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $completed->count() }}</h3>
                    <small class="text-muted">Dispensed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $expired->count() }}</h3>
                    <small class="text-muted">Expired</small>
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
                                                {{ $rx->patient->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            <strong>{{ $rx->medication_name ?? '-' }}</strong>
                                            <br><small class="text-muted">{{ $rx->dosage ?? '' }} -
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
                                                <button class="btn btn-sm btn-success"
                                                    onclick="dispenseMedication({{ $rx->id }})">
                                                    <i class="fas fa-check"></i> Dispense
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails({{ $rx->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No prescriptions to display
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
            function dispenseMedication(rxId) {
                if (confirm('Confirm medication dispensing?')) {
                    // Implement AJAX dispensing
                    window.location.reload();
                }
            }

            function viewDetails(rxId) {
                // Implement view details modal
                window.location.href = '/healthcare/pharmacy/dispensing/' + rxId;
            }
        </script>
    @endpush
@endsection
