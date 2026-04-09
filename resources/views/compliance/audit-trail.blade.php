@extends('layouts.app')

@section('title', 'Audit Trail')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-alt text-primary"></i> Audit Trail
            </h1>
            <p class="text-muted mb-0">System activity and compliance logs</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Export
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('compliance.audit-trail.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select name="period" class="form-select">
                                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>This Week
                                </option>
                                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>This Month
                                </option>
                                <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Action Type</label>
                            <select name="action_type" class="form-select">
                                <option value="">All Actions</option>
                                <option value="create" {{ request('action_type') == 'create' ? 'selected' : '' }}>Create
                                </option>
                                <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>Update
                                </option>
                                <option value="delete" {{ request('action_type') == 'delete' ? 'selected' : '' }}>Delete
                                </option>
                                <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>Login
                                </option>
                                <option value="logout" {{ request('action_type') == 'logout' ? 'selected' : '' }}>Logout
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Module</label>
                            <select name="module" class="form-select">
                                <option value="">All Modules</option>
                                <option value="patients" {{ request('module') == 'patients' ? 'selected' : '' }}>Patients
                                </option>
                                <option value="emr" {{ request('module') == 'emr' ? 'selected' : '' }}>EMR</option>
                                <option value="pharmacy" {{ request('module') == 'pharmacy' ? 'selected' : '' }}>Pharmacy
                                </option>
                                <option value="billing" {{ request('module') == 'billing' ? 'selected' : '' }}>Billing
                                </option>
                                <option value="inventory" {{ request('module') == 'inventory' ? 'selected' : '' }}>
                                    Inventory</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search description..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('compliance.audit-trail.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Audit Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditLogs as $log)
                                    <tr>
                                        <td>
                                            <small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                            <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user fa-xs"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $log->user->name ?? 'System' }}</strong>
                                                    <br><small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $colors = [
                                                    'create' => 'success',
                                                    'update' => 'warning',
                                                    'delete' => 'danger',
                                                    'login' => 'info',
                                                    'logout' => 'secondary',
                                                ];
                                                $color = $colors[$log->action_type] ?? 'primary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst($log->action_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark">{{ ucfirst($log->module ?? 'General') }}</span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($log->description, 100) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#detailModal{{ $log->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Audit Log Details</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Timestamp:</strong>
                                                            <p>{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>User:</strong>
                                                            <p>{{ $log->user->name ?? 'System' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Action Type:</strong>
                                                            <p><span
                                                                    class="badge bg-{{ $color }}">{{ ucfirst($log->action_type) }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Module:</strong>
                                                            <p>{{ ucfirst($log->module ?? 'General') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p>{{ $log->description }}</p>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>IP Address:</strong>
                                                            <p>{{ $log->ip_address ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>User Agent:</strong>
                                                            <p><small>{{ Str::limit($log->user_agent ?? '-', 100) }}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if ($log->old_values || $log->new_values)
                                                        <div class="row">
                                                            @if ($log->old_values)
                                                                <div class="col-md-6">
                                                                    <strong>Old Values:</strong>
                                                                    <pre class="bg-light p-2 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            @endif
                                                            @if ($log->new_values)
                                                                <div class="col-md-6">
                                                                    <strong>New Values:</strong>
                                                                    <pre class="bg-light p-2 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No audit logs found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($auditLogs) && $auditLogs->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="text-muted mb-0">
                                Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} of
                                {{ $auditLogs->total() }} entries
                            </p>
                            {{ $auditLogs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Compliance Notice:</strong> Audit logs are retained for {{ $retentionPeriod ?? '7 years' }} as per
                regulatory requirements.
                Logs are immutable and cannot be modified or deleted.
            </div>
        </div>
    </div>
@endsection
