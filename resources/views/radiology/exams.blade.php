@extends('layouts.app')

@section('title', 'Radiology Exams')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-x-ray text-primary"></i> Radiology Exams
            </h1>
            <p class="text-muted mb-0">Manage radiology examinations and imaging</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $exams->where('status', 'scheduled')->count() }}</h3>
                    <small class="text-muted">Scheduled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $exams->where('status', 'in_progress')->count() }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $exams->where('status', 'completed')->count() }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h3 class="text-secondary">{{ $exams->where('status', 'reported')->count() }}</h3>
                    <small class="text-muted">Reported</small>
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
                                    <th>Exam #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Body Part</th>
                                    <th>Radiologist</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                    <tr>
                                        <td><code>{{ $exam->exam_number }}</code></td>
                                        <td>{{ $exam->exam_date?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $exam->patient) }}">
                                                {{ $exam->patient->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'X-Ray' => 'fa-x-ray',
                                                    'MRI' => 'fa-magnet',
                                                    'CT Scan' => 'fa-circle-notch',
                                                    'Ultrasound' => 'fa-wave-square',
                                                    'Mammography' => 'fa-radiation',
                                                ];
                                            @endphp
                                            <i
                                                class="fas {{ $icons[$exam->exam_type] ?? 'fa-x-ray' }} me-1 text-primary"></i>
                                            {{ $exam->exam_type ?? '-' }}
                                        </td>
                                        <td>{{ $exam->body_part ?? '-' }}</td>
                                        <td>{{ $exam->radiologist?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'scheduled' => 'info',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reported' => 'secondary',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$exam->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('healthcare.radiology.exams.show', $exam) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($exam->status == 'completed')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-file-medical"></i> Report
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No radiology exams found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $exams->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
