@extends('layouts.app')

@section('title', 'Compliance Reports')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-contract text-primary"></i> Compliance Reports
            </h1>
            <p class="text-muted mb-0">Regulatory compliance and audit reports</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('compliance.reports.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Reports</option>
                                <option value="hipaa" {{ request('type') == 'hipaa' ? 'selected' : '' }}>HIPAA Compliance
                                </option>
                                <option value="data-protection"
                                    {{ request('type') == 'data-protection' ? 'selected' : '' }}>Data Protection</option>
                                <option value="access-control" {{ request('type') == 'access-control' ? 'selected' : '' }}>
                                    Access Control</option>
                                <option value="security" {{ request('type') == 'security' ? 'selected' : '' }}>Security
                                    Audit</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['compliance_score'] ?? 0 }}%</h3>
                    <small class="text-muted">Overall Compliance Score</small>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['compliance_score'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['audits_passed'] ?? 0 }}</h3>
                    <small class="text-muted">Audits Passed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['pending_reviews'] ?? 0 }}</h3>
                    <small class="text-muted">Pending Reviews</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $stats['violations'] ?? 0 }}</h3>
                    <small class="text-muted">Violations</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Compliance by Category</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories ?? [] as $category)
                                    <tr>
                                        <td>{{ $category['name'] ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $category['score'] >= 90 ? 'success' : ($category['score'] >= 70 ? 'warning' : 'danger') }}"
                                                        style="width: {{ $category['score'] ?? 0 }}%"></div>
                                                </div>
                                                <strong>{{ $category['score'] ?? 0 }}%</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($category['score'] >= 90)
                                                <span class="badge bg-success">Compliant</span>
                                            @elseif($category['score'] >= 70)
                                                <span class="badge bg-warning">Partial</span>
                                            @else
                                                <span class="badge bg-danger">Non-Compliant</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Violations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violations ?? [] as $violation)
                                    <tr>
                                        <td>
                                            <small>{{ $violation['date'] ?? '-' }}</small>
                                        </td>
                                        <td>{{ $violation['type'] ?? '-' }}</td>
                                        <td>
                                            @if ($violation['severity'] == 'critical')
                                                <span class="badge bg-danger">Critical</span>
                                            @elseif($violation['severity'] == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @else
                                                <span class="badge bg-info">Medium</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($violation['status'] == 'resolved')
                                                <span class="badge bg-success">Resolved</span>
                                            @else
                                                <span class="badge bg-warning">Open</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No violations found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Compliance Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Date</th>
                                    <th>Report Type</th>
                                    <th>Compliance Score</th>
                                    <th>Auditor</th>
                                    <th>Findings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports ?? [] as $report)
                                    <tr>
                                        <td>
                                            <strong>{{ $report['date'] ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $report['type'] ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $report['score'] >= 90 ? 'success' : ($report['score'] >= 70 ? 'warning' : 'danger') }}"
                                                        style="width: {{ $report['score'] ?? 0 }}%"></div>
                                                </div>
                                                <strong>{{ $report['score'] ?? 0 }}%</strong>
                                            </div>
                                        </td>
                                        <td>{{ $report['auditor'] ?? '-' }}</td>
                                        <td>{{ $report['findings'] ?? 0 }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="View Report">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-success" title="Download"
                                                onclick="window.print()">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-file-contract fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No compliance reports generated</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Regulatory Requirements:</strong> Compliance reports must be reviewed quarterly and retained for a
                minimum of 7 years.
                All violations must be addressed within 30 days of discovery.
            </div>
        </div>
    </div>
@endsection
