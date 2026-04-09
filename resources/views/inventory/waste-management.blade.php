@extends('layouts.app')

@section('title', 'Medical Waste Management')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-biohazard text-primary"></i> Medical Waste Management
            </h1>
            <p class="text-muted mb-0">Track and manage medical waste disposal</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWasteModal">
                <i class="fas fa-plus"></i> Log Waste
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ number_format($stats['total_waste_kg'] ?? 0, 2) }} kg</h3>
                    <small class="text-muted">Total This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['infectious_kg'] ?? 0 }} kg</h3>
                    <small class="text-muted">Infectious Waste</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['sharps_kg'] ?? 0 }} kg</h3>
                    <small class="text-muted">Sharps</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['disposed_kg'] ?? 0 }} kg</h3>
                    <small class="text-muted">Properly Disposed</small>
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
                                    <th>Log #</th>
                                    <th>Date</th>
                                    <th>Waste Type</th>
                                    <th>Weight (kg)</th>
                                    <th>Source</th>
                                    <th>Disposal Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wasteLogs as $log)
                                    <tr>
                                        <td><code>{{ $log->log_number }}</code></td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'infectious' => 'danger',
                                                    'sharps' => 'warning',
                                                    'pharmaceutical' => 'info',
                                                    'general' => 'secondary',
                                                    'chemical' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$log->waste_type] ?? 'secondary' }}">
                                                {{ ucfirst($log->waste_type ?? '-') }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $log->weight_kg ?? 0 }} kg</strong></td>
                                        <td>{{ $log->source_department ?? '-' }}</td>
                                        <td>{{ ucfirst($log->disposal_method ?? '-') }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'collected' => 'info',
                                                    'disposed' => 'success',
                                                    'incinerated' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$log->status] ?? 'secondary' }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No waste logs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $wasteLogs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
