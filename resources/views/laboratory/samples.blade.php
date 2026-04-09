@extends('layouts.app')

@section('title', 'Sample Management')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-vials text-primary"></i> Sample Management
            </h1>
            <p class="text-muted mb-0">Track and manage laboratory samples</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSampleModal">
                <i class="fas fa-plus"></i> Register Sample
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $samples->where('status', 'collected')->count() }}</h3>
                    <small class="text-muted">Collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $samples->where('status', 'processing')->count() }}</h3>
                    <small class="text-muted">Processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $samples->where('status', 'completed')->count() }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $samples->where('status', 'rejected')->count() }}</h3>
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
                                    <th>Sample ID</th>
                                    <th>Patient</th>
                                    <th>Sample Type</th>
                                    <th>Collected At</th>
                                    <th>Tests</th>
                                    <th>Status</th>
                                    <th>Technician</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($samples as $sample)
                                    <tr>
                                        <td><code>{{ $sample->sample_id }}</code></td>
                                        <td>{{ $sample->patient?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'blood' => 'fa-tint',
                                                    'urine' => 'fa-flask',
                                                    'tissue' => 'fa-dna',
                                                    'swab' => 'fa-stroopwafel',
                                                ];
                                            @endphp
                                            <i class="fas {{ $icons[$sample->sample_type] ?? 'fa-vial' }} me-1"></i>
                                            {{ ucfirst($sample->sample_type ?? '-') }}
                                        </td>
                                        <td>{{ $sample->collected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $sample->tests_count ?? 0 }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'collected' => 'info',
                                                    'processing' => 'warning',
                                                    'completed' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$sample->status] ?? 'secondary' }}">
                                                {{ ucfirst($sample->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $sample->technician?->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($sample->status == 'collected')
                                                    <button class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No samples found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $samples->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sample Modal -->
    <div class="modal fade" id="addSampleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.laboratory.samples.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Register Sample</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sample Type</label>
                            <select name="sample_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="blood">Blood</option>
                                <option value="urine">Urine</option>
                                <option value="tissue">Tissue</option>
                                <option value="swab">Swab</option>
                                <option value="stool">Stool</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Collection Date/Time</label>
                            <input type="datetime-local" name="collected_at" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Register Sample</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
