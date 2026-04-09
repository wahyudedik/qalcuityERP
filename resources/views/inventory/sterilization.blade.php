@extends('layouts.app')

@section('title', 'Sterilization Tracking')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-virus text-primary"></i> Sterilization Tracking
            </h1>
            <p class="text-muted mb-0">Track equipment sterilization cycles</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCycleModal">
                <i class="fas fa-plus"></i> Log Cycle
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-success">{{ $cycles->where('status', 'completed')->count() }}</h3>
                            <small class="text-muted">Completed Today</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ $cycles->where('status', 'in_progress')->count() }}</h3>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ $equipment->where('status', 'sterile')->count() }}</h3>
                            <small class="text-muted">Sterile Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger">{{ $equipment->where('status', 'contaminated')->count() }}</h3>
                            <small class="text-muted">Contaminated</small>
                        </div>
                    </div>
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
                                    <th>Cycle #</th>
                                    <th>Date/Time</th>
                                    <th>Equipment</th>
                                    <th>Sterilizer</th>
                                    <th>Method</th>
                                    <th>Duration</th>
                                    <th>Temperature</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cycles as $cycle)
                                    <tr>
                                        <td><code>{{ $cycle->cycle_number }}</code></td>
                                        <td>{{ $cycle->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td><strong>{{ $cycle->equipment_count ?? 0 }} items</strong></td>
                                        <td>{{ $cycle->sterilizer_name ?? '-' }}</td>
                                        <td>{{ ucfirst($cycle->method ?? '-') }}</td>
                                        <td>{{ $cycle->duration_minutes ?? '-' }} min</td>
                                        <td>{{ $cycle->temperature ?? '-' }}°C</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'completed' => 'success',
                                                    'in_progress' => 'warning',
                                                    'failed' => 'danger',
                                                    'scheduled' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$cycle->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $cycle->status)) }}
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
                                        <td colspan="9" class="text-center py-4 text-muted">No sterilization cycles found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $cycles->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
