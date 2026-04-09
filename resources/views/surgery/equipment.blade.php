@extends('layouts.app')

@section('title', 'Surgery Equipment')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tools text-primary"></i> Surgery Equipment
            </h1>
            <p class="text-muted mb-0">Track and manage surgical equipment</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="fas fa-plus"></i> Add Equipment
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
                            <h3 class="text-success">{{ $equipment->where('status', 'available')->count() }}</h3>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ $equipment->where('status', 'in_use')->count() }}</h3>
                            <small class="text-muted">In Use</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ $equipment->where('status', 'sterilizing')->count() }}</h3>
                            <small class="text-muted">Sterilizing</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger">{{ $equipment->where('status', 'maintenance')->count() }}</h3>
                            <small class="text-muted">Maintenance</small>
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
                                    <th>Equipment</th>
                                    <th>Category</th>
                                    <th>Serial #</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Sterilized</th>
                                    <th>Next Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipment as $item)
                                    <tr>
                                        <td><strong>{{ $item->name }}</strong></td>
                                        <td>{{ ucfirst($item->category ?? '-') }}</td>
                                        <td><code>{{ $item->serial_number ?? '-' }}</code></td>
                                        <td>{{ $item->location ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'available' => 'success',
                                                    'in_use' => 'warning',
                                                    'sterilizing' => 'info',
                                                    'maintenance' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->last_sterilized)
                                                {{ $item->last_sterilized->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->next_maintenance)
                                                @if ($item->next_maintenance->isPast())
                                                    <span
                                                        class="text-danger fw-bold">{{ $item->next_maintenance->format('d/m/Y') }}</span>
                                                @else
                                                    {{ $item->next_maintenance->format('d/m/Y') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No surgical equipment found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $equipment->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
