@extends('layouts.app')

@section('title', 'Lab Equipment Integration')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-microscope text-primary"></i> Lab Equipment Integration
            </h1>
            <p class="text-muted mb-0">Laboratory equipment connectivity and auto-polling</p>
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
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['connected'] ?? 0 }}</h3>
                    <small class="text-muted">Connected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $stats['disconnected'] ?? 0 }}</h3>
                    <small class="text-muted">Disconnected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['polling_active'] ?? 0 }}</h3>
                    <small class="text-muted">Auto-Polling Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['results_today'] ?? 0 }}</h3>
                    <small class="text-muted">Results Today</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Equipment Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Type</th>
                                    <th>Connection</th>
                                    <th>Status</th>
                                    <th>Auto-Poll</th>
                                    <th>Last Poll</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipment as $device)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-microscope"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $device->name ?? '-' }}</strong>
                                                    <br><small class="text-muted">ID:
                                                        {{ $device->device_id ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $device->type ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($device->connection_type == 'hl7')
                                                <span class="badge bg-primary">HL7</span>
                                            @elseif($device->connection_type == 'astm')
                                                <span class="badge bg-success">ASTM</span>
                                            @elseif($device->connection_type == 'serial')
                                                <span class="badge bg-warning">Serial (RS-232)</span>
                                            @else
                                                <span
                                                    class="badge bg-secondary">{{ ucfirst($device->connection_type ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->is_connected)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Connected
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Disconnected
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->auto_poll_enabled)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-sync fa-spin"></i> Every
                                                    {{ $device->poll_interval ?? 5 }}s
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Disabled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->last_poll_at)
                                                <small>{{ $device->last_poll_at->diffForHumans() ?? '-' }}</small>
                                            @else
                                                <small class="text-muted">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" title="Test Connection">
                                                    <i class="fas fa-plug"></i>
                                                </button>
                                                @if (!$device->auto_poll_enabled)
                                                    <button class="btn btn-sm btn-success" title="Enable Auto-Poll">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-warning" title="Disable Auto-Poll">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-primary" title="View Logs"
                                                    data-bs-toggle="modal" data-bs-target="#logsModal{{ $device->id }}">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Logs Modal -->
                                    <div class="modal fade" id="logsModal{{ $device->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Connection Logs - {{ $device->name }}</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Timestamp</th>
                                                                    <th>Event</th>
                                                                    <th>Details</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($device->logs ?? [] as $log)
                                                                    <tr>
                                                                        <td><small>{{ $log['timestamp'] ?? '-' }}</small>
                                                                        </td>
                                                                        <td>
                                                                            @if ($log['event'] == 'connected')
                                                                                <span
                                                                                    class="badge bg-success">Connected</span>
                                                                            @elseif($log['event'] == 'disconnected')
                                                                                <span
                                                                                    class="badge bg-danger">Disconnected</span>
                                                                            @elseif($log['event'] == 'poll_success')
                                                                                <span class="badge bg-info">Poll
                                                                                    Success</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-warning">{{ $log['event'] ?? '-' }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td><small>{{ $log['details'] ?? '-' }}</small>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="3" class="text-center text-muted">No
                                                                            logs available</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
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
                                            <i class="fas fa-microscope fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No lab equipment configured</p>
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

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Lab Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.lab-equipment.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g., Hematology Analyzer">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Device ID <span class="text-danger">*</span></label>
                                <input type="text" name="device_id" class="form-control" required
                                    placeholder="e.g., HEM-001">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="hematology">Hematology Analyzer</option>
                                    <option value="chemistry">Chemistry Analyzer</option>
                                    <option value="immunoassay">Immunoassay Analyzer</option>
                                    <option value="urinalysis">Urinalysis Analyzer</option>
                                    <option value="coagulation">Coagulation Analyzer</option>
                                    <option value="microscope">Digital Microscope</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Connection Type <span class="text-danger">*</span></label>
                                <select name="connection_type" class="form-select" required>
                                    <option value="">Select Connection</option>
                                    <option value="hl7">HL7</option>
                                    <option value="astm">ASTM</option>
                                    <option value="serial">Serial (RS-232)</option>
                                    <option value="tcp">TCP/IP</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IP Address / Port</label>
                                <input type="text" name="ip_address" class="form-control"
                                    placeholder="e.g., 192.168.1.100:5000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Auto-Poll Interval (seconds)</label>
                                <input type="number" name="poll_interval" class="form-control" value="5"
                                    min="1" max="60">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="auto_poll_enabled"
                                id="auto_poll_enabled" checked>
                            <label class="form-check-label" for="auto_poll_enabled">
                                Enable Auto-Polling
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
