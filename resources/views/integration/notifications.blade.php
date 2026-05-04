<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-bell text-blue-600"></i> Notification Settings
            </h1>
            <p class="text-gray-500">Configure system notifications and alerts</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                <i class="fas fa-plus"></i> Add Notification Rule
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['active_rules'] ?? 0 }}</h3>
                    <small class="text-gray-500">Active Rules</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['sent_today'] ?? 0 }}</h3>
                    <small class="text-gray-500">Sent Today</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['failed'] ?? 0 }}</h3>
                    <small class="text-gray-500">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Notification Rules</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
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
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">{{ $rule->trigger_event ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($rule->channels)
                                                @foreach (explode(',', $rule->channels) as $channel)
                                                    @if (trim($channel) == 'email')
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><i class="fas fa-envelope"></i>
                                                            Email</span>
                                                    @elseif(trim($channel) == 'sms')
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"><i class="fas fa-sms"></i> SMS</span>
                                                    @elseif(trim($channel) == 'push')
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><i class="fas fa-bell"></i>
                                                            Push</span>
                                                    @elseif(trim($channel) == 'in_app')
                                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><i class="fas fa-comment"></i>
                                                            In-App</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule->priority == 'critical')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Critical</span>
                                            @elseif($rule->priority == 'high')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">High</span>
                                            @elseif($rule->priority == 'medium')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Medium</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule->is_active)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $rule->notifications_sent ?? 0 }}</strong>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" title="Edit" data-bs-toggle="modal"
                                                    data-bs-target="#editRuleModal{{ $rule->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if ($rule->is_active)
                                                    <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs transition" title="Deactivate">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @else
                                                    <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Activate">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition" title="Test">
                                                    <i class="fas fa-vial"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10">
                                            <i class="fas fa-bell fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No notification rules configured</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Recent Notifications</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
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
                                                class="text-gray-500">{{ $notification->created_at->diffForHumans() ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $notification->recipient_name ?? '-' }}</strong>
                                                <br><small
                                                    class="text-gray-500">{{ $notification->recipient_email ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($notification->channel == 'email')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><i class="fas fa-envelope"></i> Email</span>
                                            @elseif($notification->channel == 'sms')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"><i class="fas fa-sms"></i> SMS</span>
                                            @elseif($notification->channel == 'push')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><i class="fas fa-bell"></i> Push</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($notification->channel ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($notification->subject ?? '-', 50) }}</small>
                                        </td>
                                        <td>
                                            @if ($notification->status == 'sent')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Sent</span>
                                            @elseif($notification->status == 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                            @elseif($notification->status == 'failed')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Failed</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($notification->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                data-bs-target="#viewNotificationModal{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-bell fa-2x text-gray-500 mb-2"></i>
                                            <p class="text-gray-500">No notifications sent</p>
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
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.notifications.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Rule Name <span class="text-red-600">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g., Appointment Reminder">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Trigger Event <span class="text-red-600">*</span></label>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Notification Channels <span class="text-red-600">*</span></label>
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
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Priority <span class="text-red-600">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Template <span class="text-red-600">*</span></label>
                            <input type="text" name="subject_template" class="form-control" required
                                placeholder="e.g., Appointment Reminder: {{ appointment_date }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Template <span class="text-red-600">*</span></label>
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
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-plus"></i> Add Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
