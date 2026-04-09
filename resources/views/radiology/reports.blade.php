@extends('layouts.app')

@section('title', 'Radiology Reports')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-medical text-primary"></i> Radiology Reports
            </h1>
            <p class="text-muted mb-0">Radiologist interpretation reports</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report #</th>
                                    <th>Exam Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Radiologist</th>
                                    <th>Findings</th>
                                    <th>Impression</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td><code>{{ $report->report_number }}</code></td>
                                        <td>{{ $report->exam_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $report->patient) }}">
                                                {{ $report->patient->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $report->exam_type ?? '-' }}</td>
                                        <td>{{ $report->radiologist?->name ?? '-' }}</td>
                                        <td><small>{{ Str::limit($report->findings, 40) ?? '-' }}</small></td>
                                        <td><small>{{ Str::limit($report->impression, 40) ?? '-' }}</small></td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'preliminary' => 'warning',
                                                    'final' => 'success',
                                                    'amended' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#viewReportModal{{ $report->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($report->status == 'draft' || $report->status == 'preliminary')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i> Finalize
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Report Modal -->
                                    <div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Radiology Report - {{ $report->report_number }}
                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Patient:</strong> {{ $report->patient?->name ?? '-' }}
                                                            <br><strong>Exam Type:</strong> {{ $report->exam_type ?? '-' }}
                                                            <br><strong>Exam Date:</strong>
                                                            {{ $report->exam_date?->format('d/m/Y H:i') ?? '-' }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Radiologist:</strong>
                                                            {{ $report->radiologist?->name ?? '-' }}
                                                            <br><strong>Report Date:</strong>
                                                            {{ $report->created_at->format('d/m/Y H:i') }}
                                                            <br><strong>Status:</strong>
                                                            <span
                                                                class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                                                {{ ucfirst($report->status) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Clinical History</h6>
                                                        <p class="bg-light p-3 rounded">
                                                            {{ $report->clinical_history ?? 'N/A' }}</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Technique</h6>
                                                        <p class="bg-light p-3 rounded">{{ $report->technique ?? 'N/A' }}
                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Findings</h6>
                                                        <p class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                                                            {{ $report->findings ?? 'N/A' }}</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Impression</h6>
                                                        <div class="alert alert-info">
                                                            <strong>{{ $report->impression ?? 'N/A' }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Recommendations</h6>
                                                        <p class="bg-light p-3 rounded">
                                                            {{ $report->recommendations ?? 'N/A' }}</p>
                                                    </div>

                                                    @if ($report->images && count($report->images) > 0)
                                                        <div class="mb-3">
                                                            <h6 class="text-primary">Images ({{ count($report->images) }})
                                                            </h6>
                                                            <div class="row">
                                                                @foreach ($report->images as $image)
                                                                    <div class="col-md-3 mb-2">
                                                                        <img src="{{ $image['url'] ?? '#' }}"
                                                                            class="img-fluid rounded border"
                                                                            alt="Radiology image">
                                                                    </div>
                                                                @endforeach
                                                            </div>
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
                                        <td colspan="9" class="text-center py-4 text-muted">No radiology reports found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
