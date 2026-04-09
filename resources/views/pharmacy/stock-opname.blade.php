@extends('layouts.app')

@section('title', 'Stock Opname')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check text-primary"></i> Stock Opname
            </h1>
            <p class="text-muted mb-0">Physical inventory count and reconciliation</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOpnameModal">
                <i class="fas fa-plus"></i> New Stock Opname
            </button>
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
                                    <th>Opname #</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Items Counted</th>
                                    <th>Discrepancies</th>
                                    <th>Status</th>
                                    <th>Conducted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($opnames as $opname)
                                    <tr>
                                        <td><code>{{ $opname->opname_number }}</code></td>
                                        <td>{{ $opname->opname_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ ucfirst($opname->category ?? 'All') }}</td>
                                        <td>{{ $opname->items_counted ?? 0 }}</td>
                                        <td>
                                            @if ($opname->discrepancies > 0)
                                                <span class="text-danger fw-bold">{{ $opname->discrepancies }}</span>
                                            @else
                                                <span class="text-success">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reconciled' => 'info',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$opname->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $opname->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $opname->conducted_by?->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('healthcare.pharmacy.stock-opname.show', $opname) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($opname->status != 'reconciled')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i> Reconcile
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No stock opname records found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $opnames->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- New Stock Opname Modal -->
    <div class="modal fade" id="newOpnameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.pharmacy.stock-opname.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Stock Opname</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="all">All Categories</option>
                                <option value="medications">Medications Only</option>
                                <option value="supplies">Medical Supplies</option>
                                <option value="equipment">Equipment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opname Date</label>
                            <input type="date" name="opname_date" class="form-control"
                                value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Opname</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
