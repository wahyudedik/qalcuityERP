@extends('layouts.app')

@section('title', 'Inventory Requests')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list text-primary"></i> Inventory Requests
            </h1>
            <p class="text-muted mb-0">Medical supply requisition and approval</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="fas fa-plus"></i> New Request
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $requests->where('status', 'pending')->count() }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $requests->where('status', 'approved')->count() }}</h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $requests->where('status', 'fulfilled')->count() }}</h3>
                    <small class="text-muted">Fulfilled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $requests->where('status', 'rejected')->count() }}</h3>
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
                                    <th>Request #</th>
                                    <th>Date</th>
                                    <th>Requested By</th>
                                    <th>Department</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td><code>{{ $request->request_number }}</code></td>
                                        <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $request->requested_by?->name ?? '-' }}</td>
                                        <td>{{ $request->department ?? '-' }}</td>
                                        <td><strong>{{ count($request->items ?? []) }} items</strong></td>
                                        <td>
                                            @if ($request->priority == 'urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                            @elseif($request->priority == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @else
                                                <span class="badge bg-secondary">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'fulfilled' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($request->status == 'pending')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No requests found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
