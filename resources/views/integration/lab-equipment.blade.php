<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-microscope text-blue-600"></i> Lab Equipment Integration
            </h1>
            <p class="text-gray-500">Laboratory equipment connectivity and auto-polling</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="fas fa-plus"></i> Add Equipment
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['connected'] ?? 0 }}</h3>
                    <small class="text-gray-500">Connected</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['disconnected'] ?? 0 }}</h3>
                    <small class="text-gray-500">Disconnected</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['polling_active'] ?? 0 }}</h3>
                    <small class="text-gray-500">Auto-Polling Active</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['results_today'] ?? 0 }}</h3>
                    <small class="text-gray-500">Results Today</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Equipment Status</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
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
                                            <div class="flex items-center">
                                                <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-microscope"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $device->name ?? '-' }}</strong>
                                                    <br><small class="text-gray-500">ID:
                                                        {{ $device->device_id ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-50 text-dark">{{ $device->type ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($device->connection_type == 'hl7')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">HL7</span>
                                            @elseif($device->connection_type == 'astm')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">ASTM</span>
                                            @elseif($device->connection_type == 'serial')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Serial (RS-232)</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($device->connection_type ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->is_connected)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Connected
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times-circle"></i> Disconnected
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->auto_poll_enabled)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-sync fa-spin"></i> Every
                                                    {{ $device->poll_interval ?? 5 }}s
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Disabled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->last_poll_at)
                                                <small>{{ $device->last_poll_at->diffForHumans() ?? '-' }}</small>
                                            @else
                                                <small class="text-gray-500">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" title="Test Connection">
                                                    <i class="fas fa-plug"></i>
                                                </button>
                                                @if (!$device->auto_poll_enabled)
                                                    <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Enable Auto-Poll">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                @else
                                                    <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs transition" title="Disable Auto-Poll">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @endif
                                                <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition" title="View Logs"
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
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-sm text-left">
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
                                                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Connected</span>
                                                                            @elseif($log['event'] == 'disconnected')
                                                                                <span
                                                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Disconnected</span>
                                                                            @elseif($log['event'] == 'poll_success')
                                                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Poll
                                                                                    Success</span>
                                                                            @else
                                                                                <span
                                                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $log['event'] ?? '-' }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td><small>{{ $log['details'] ?? '-' }}</small>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="3" class="text-center text-gray-400">No
                                                                            logs available</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
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
                                            <i class="fas fa-microscope fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No lab equipment configured</p>
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
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.lab-equipment.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Equipment Name <span class="text-red-600">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g., Hematology Analyzer">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Device ID <span class="text-red-600">*</span></label>
                                <input type="text" name="device_id" class="form-control" required
                                    placeholder="e.g., HEM-001">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Equipment Type <span class="text-red-600">*</span></label>
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
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Connection Type <span class="text-red-600">*</span></label>
                                <select name="connection_type" class="form-select" required>
                                    <option value="">Select Connection</option>
                                    <option value="hl7">HL7</option>
                                    <option value="astm">ASTM</option>
                                    <option value="serial">Serial (RS-232)</option>
                                    <option value="tcp">TCP/IP</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">IP Address / Port</label>
                                <input type="text" name="ip_address" class="form-control"
                                    placeholder="e.g., 192.168.1.100:5000">
                            </div>
                            <div class="w-full md:w-1/2">
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
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-plus"></i> Add Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
