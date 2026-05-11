<x-app-layout>
    <x-slot name="header">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-0">
                    <i class="fas fa-database text-blue-600"></i> Backup Logs
                </h1>
                <p class="text-gray-500">System backup history and status</p>
            </div>
            <div>
                <button
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition"
                    data-bs-toggle="modal" data-bs-target="#createBackupModal">
                    <i class="fas fa-plus"></i> Create Backup
                </button>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['total_backups'] ?? 0 }}</h3>
                    <small class="text-gray-500">Total Backups</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['completed'] ?? 0 }}</h3>
                    <small class="text-gray-500">Completed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['in_progress'] ?? 0 }}</h3>
                    <small class="text-gray-500">In Progress</small>
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
                    <h5 class="mb-0">Backup History</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr>
                                        <td>
                                            <strong>{{ $backup->created_at->format('d/m/Y H:i') }}</strong>
                                            <br><small
                                                class="text-gray-500">{{ $backup->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if ($backup->type == 'full')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Full
                                                    Backup</span>
                                            @elseif($backup->type == 'incremental')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Incremental</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($backup->type) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($backup->size)
                                                @php
                                                    $size = $backup->size;
                                                    $unit = 'B';
                                                    if ($size >= 1073741824) {
                                                        $size = round($size / 1073741824, 2);
                                                        $unit = 'GB';
                                                    } elseif ($size >= 1048576) {
                                                        $size = round($size / 1048576, 2);
                                                        $unit = 'MB';
                                                    } elseif ($size >= 1024) {
                                                        $size = round($size / 1024, 2);
                                                        $unit = 'KB';
                                                    }
                                                @endphp
                                                <strong>{{ $size }} {{ $unit }}</strong>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($backup->duration)
                                                {{ $backup->duration }} seconds
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($backup->status == 'completed')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Completed
                                                </span>
                                            @elseif($backup->status == 'in_progress')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    <i class="fas fa-spinner fa-spin"></i> In Progress
                                                </span>
                                            @elseif($backup->status == 'failed')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times-circle"></i> Failed
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($backup->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                @if ($backup->location == 'local')
                                                    <i class="fas fa-hdd"></i> Local
                                                @elseif($backup->location == 'cloud')
                                                    <i class="fas fa-cloud"></i> Cloud
                                                @else
                                                    {{ ucfirst($backup->location) }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if ($backup->status == 'completed')
                                                <div class="flex gap-1">
                                                    <button
                                                        class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition"
                                                        title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button
                                                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs transition"
                                                        title="Restore" data-bs-toggle="modal"
                                                        data-bs-target="#restoreModal{{ $backup->id }}">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button
                                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs transition"
                                                        title="Delete"
                                                        onclick="Dialog.danger('Are you sure?').then(ok => { if(ok) this.closest('tr').remove() })">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Restore Modal -->
                                                <div class="modal fade" id="restoreModal{{ $backup->id }}"
                                                    tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle"></i> Confirm
                                                                    Restore
                                                                </h5>
                                                                <button type="button"
                                                                    class="text-gray-400 hover:text-gray-600"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <strong>Warning!</strong> Restoring from backup will
                                                                    replace current data.
                                                                    This action cannot be undone.
                                                                </div>
                                                                <p><strong>Backup Date:</strong>
                                                                    {{ $backup->created_at->format('d/m/Y H:i') }}</p>
                                                                <p><strong>Type:</strong> {{ ucfirst($backup->type) }}
                                                                </p>
                                                                <p><strong>Size:</strong> {{ $size ?? '-' }}
                                                                    {{ $unit ?? '' }}</p>
                                                                <form
                                                                    action="{{ route('compliance.backup-logs.restore', $backup->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="form-check mb-3">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            id="confirm{{ $backup->id }}" required>
                                                                        <label class="form-check-label"
                                                                            for="confirm{{ $backup->id }}">
                                                                            I understand the risks and want to proceed
                                                                        </label>
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-medium transition w-full">
                                                                        <i class="fas fa-undo"></i> Restore Now
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10">
                                            <i class="fas fa-database fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No backups found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($backups) && $backups->hasPages())
                        <div class="mt-3">
                            {{ $backups->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div class="modal fade" id="createBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Backup</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600"
                        data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('compliance.backup-logs.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Backup Type <span class="text-red-600">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="full">Full Backup</option>
                                <option value="incremental">Incremental Backup</option>
                                <option value="differential">Differential Backup</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Storage Location <span class="text-red-600">*</span></label>
                            <select name="location" class="form-select" required>
                                <option value="">Select Location</option>
                                <option value="local">Local Storage</option>
                                <option value="cloud">Cloud Storage</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-database"></i> Create Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
