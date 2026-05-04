<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-shield-alt text-blue-600"></i> Audit Trail
            </h1>
            <p class="text-gray-500">System activity and compliance logs</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                <i class="fas fa-print"></i> Export
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" action="{{ route('compliance.audit-trail.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-3">
                        <div class="w-full md:w-1/4">
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
                        <div class="w-full md:w-1/6">
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
                        <div class="w-full md:w-1/6">
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
                        <div class="w-full md:w-1/6">
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
                        <div class="w-full md:w-1/4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search description..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="w-full">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('compliance.audit-trail.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Audit Logs</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
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
                                            <br><small class="text-gray-500">{{ $log->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user fa-xs"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $log->user?->name ?? 'System' }}</strong>
                                                    <br><small class="text-gray-500">{{ $log->user?->email ?? '-' }}</small>
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
                                            <span class="badge bg-{{ $color  }}">
                                                {{ ucfirst($log->action_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-gray-50 text-dark">{{ ucfirst($log->module ?? 'General') }}</span>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($log->description, 100) }}</small>
                                        </td>
                                        <td>
                                            <small class="text-gray-500">{{ $log->ip_address ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
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
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Timestamp:</strong>
                                                            <p>{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>User:</strong>
                                                            <p>{{ $log->user?->name ?? 'System' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Action Type:</strong>
                                                            <p><span
                                                                    class="badge bg-{{ $color  }}">{{ ucfirst($log->action_type) }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Module:</strong>
                                                            <p>{{ ucfirst($log->module ?? 'General') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p>{{ $log->description }}</p>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>IP Address:</strong>
                                                            <p>{{ $log->ip_address ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>User Agent:</strong>
                                                            <p><small>{{ Str::limit($log->user_agent ?? '-', 100) }}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if ($log->old_values || $log->new_values)
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                            @if ($log->old_values)
                                                                <div class="w-full md:w-1/2">
                                                                    <strong>Old Values:</strong>
                                                                    <pre class="bg-gray-50 p-2 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            @endif
                                                            @if ($log->new_values)
                                                                <div class="w-full md:w-1/2">
                                                                    <strong>New Values:</strong>
                                                                    <pre class="bg-gray-50 p-2 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10">
                                            <i class="fas fa-shield-alt fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No audit logs found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($auditLogs) && $auditLogs->hasPages())
                        <div class="flex items-center justify-between mt-3">
                            <p class="text-gray-500">
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
        <div class="w-full">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Compliance Notice:</strong> Audit logs are retained for {{ $retentionPeriod ?? '7 years' }} as per
                regulatory requirements.
                Logs are immutable and cannot be modified or deleted.
            </div>
        </div>
    </div>
</x-app-layout>
