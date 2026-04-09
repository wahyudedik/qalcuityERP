@extends('layouts.app')

@section('title', 'Visit History')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-history text-primary"></i> My Visit History
            </h1>
            <p class="text-muted mb-0">Complete record of your healthcare visits</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary">{{ $stats['total_visits'] ?? 0 }}</h3>
                    <small class="text-muted">Total Visits</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['last_visit_days'] ?? 0 }}</h3>
                    <small class="text-muted">Days Since Last Visit</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['upcoming_appointments'] ?? 0 }}</h3>
                    <small class="text-muted">Upcoming Appointments</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Visit Records</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                                class="text-muted">{{ $visit->visit_date->diffForHumans() ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($visit->visit_type == 'consultation')
                                                <span class="badge bg-primary">Consultation</span>
                                            @elseif($visit->visit_type == 'emergency')
                                                <span class="badge bg-danger">Emergency</span>
                                            @elseif($visit->visit_type == 'follow_up')
                                                <span class="badge bg-info">Follow-up</span>
                                            @elseif($visit->visit_type == 'checkup')
                                                <span class="badge bg-success">Check-up</span>
                                            @else
                                                <span
                                                    class="badge bg-secondary">{{ ucfirst($visit->visit_type ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $visit->department ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
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
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($visit->status == 'in_progress')
                                                <span class="badge bg-warning">In Progress</span>
                                            @elseif($visit->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($visit->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#visitDetailModal{{ $visit->id }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-success" title="Download Summary"
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
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Visit Date:</strong>
                                                            <p>{{ $visit->visit_date->format('d/m/Y H:i') ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Visit Type:</strong>
                                                            <p>{{ ucfirst($visit->visit_type ?? '-') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Department:</strong>
                                                            <p>{{ $visit->department ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
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
                                                        <p class="bg-light p-3 rounded" style="white-space: pre-wrap;">
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
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No visit history available</p>
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
@endsection
