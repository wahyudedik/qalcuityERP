@extends('layouts.app')

@section('title', 'Notification Settings')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bell text-primary"></i> Notification Settings
            </h1>
            <p class="text-muted mb-0">Configure system notifications and alerts</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                <i class="fas fa-plus"></i> Add Notification Rule
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $stats['active_rules'] ?? 0 }}</h3>
                    <small class="text-muted">Active Rules</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['sent_today'] ?? 0 }}</h3>
                    <small class="text-muted">Sent Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $stats['failed'] ?? 0 }}</h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notification Rules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Trigger Event</th>
                                    <th>Channels</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Notifications Sent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                    <tr>
                                        <td>
                                            <strong>{{ $rule->name ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $rule->trigger_event ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($rule->channels)
                                                @foreach (explode(',', $rule->channels) as $channel)
                                                    @if (trim($channel) == 'email')
                                                        <span class="badge bg-primary"><i class="fas fa-envelope"></i>
                                                            Email</span>
                                                    @elseif(trim($channel) == 'sms')
                                                        <span class="badge bg-success"><i class="fas fa-sms"></i> SMS</span>
                                                    @elseif(trim($channel) == 'push')
                                                        <span class="badge bg-warning"><i class="fas fa-bell"></i>
                                                            Push</span>
                                                    @elseif(trim($channel) == 'in_app')
                                                        <span class="badge bg-secondary"><i class="fas fa-comment"></i>
                                                            In-App</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule->priority == 'critical')
                                                <span class="badge bg-danger">Critical</span>
                                            @elseif($rule->priority == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @elseif($rule->priority == 'medium')
                                                <span class="badge bg-info">Medium</span>
                                            @else
                                                <span class="badge bg-secondary">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $rule->notifications_sent ?? 0 }}</strong>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" title="Edit" data-bs-toggle="modal"
                                                    data-bs-target="#editRuleModal{{ $rule->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if ($rule->is_active)
                                                    <button class="btn btn-sm btn-warning" title="Deactivate">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-success" title="Activate">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-primary" title="Test">
                                                    <i class="fas fa-vial"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No notification rules configured</p>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sent At</th>
                                    <th>Recipient</th>
                                    <th>Channel</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr>
                                        <td>
                                            <small>{{ $notification->created_at->format('d/m/Y H:i') ?? '-' }}</small>
                                            <br><small
                                                class="text-muted">{{ $notification->created_at->diffForHumans() ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $notification->recipient_name ?? '-' }}</strong>
                                                <br><small
                                                    class="text-muted">{{ $notification->recipient_email ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($notification->channel == 'email')
                                                <span class="badge bg-primary"><i class="fas fa-envelope"></i> Email</span>
                                            @elseif($notification->channel == 'sms')
                                                <span class="badge bg-success"><i class="fas fa-sms"></i> SMS</span>
                                            @elseif($notification->channel == 'push')
                                                <span class="badge bg-warning"><i class="fas fa-bell"></i> Push</span>
                                            @else
                                                <span
                                                    class="badge bg-secondary">{{ ucfirst($notification->channel ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($notification->subject ?? '-', 50) }}</small>
                                        </td>
                                        <td>
                                            @if ($notification->status == 'sent')
                                                <span class="badge bg-success">Sent</span>
                                            @elseif($notification->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($notification->status == 'failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                <span
                                                    class="badge bg-secondary">{{ ucfirst($notification->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewNotificationModal{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-bell fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No notifications sent</p>
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

    <!-- Add Notification Rule Modal -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Notification Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.notifications.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g., Appointment Reminder">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trigger Event <span class="text-danger">*</span></label>
                                <select name="trigger_event" class="form-select" required>
                                    <option value="">Select Event</option>
                                    <option value="appointment_reminder">Appointment Reminder</option>
                                    <option value="lab_result_ready">Lab Result Ready</option>
                                    <option value="prescription_due">Prescription Due</option>
                                    <option value="low_stock">Low Stock Alert</option>
                                    <option value="critical_result">Critical Lab Result</option>
                                    <option value="payment_overdue">Payment Overdue</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Notification Channels <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="email"
                                        id="channel_email" checked>
                                    <label class="form-check-label" for="channel_email">Email</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="sms"
                                        id="channel_sms">
                                    <label class="form-check-label" for="channel_sms">SMS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="push"
                                        id="channel_push">
                                    <label class="form-check-label" for="channel_push">Push Notification</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channels[]" value="in_app"
                                        id="channel_in_app" checked>
                                    <label class="form-check-label" for="channel_in_app">In-App</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Template <span class="text-danger">*</span></label>
                            <input type="text" name="subject_template" class="form-control" required
                                placeholder="e.g., Appointment Reminder: {{ appointment_date }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Template <span class="text-danger">*</span></label>
                            <textarea name="message_template" class="form-control" rows="4" required
                                placeholder="e.g., Dear {{ patient_name }}, you have an appointment on {{ appointment_date }}..."></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Activate this rule
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
