<x-app-layout>
    <x-slot name="header">{{ __('Backup Details') }} -
        #{{ $backup->id }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.backups.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Backup Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Backup ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">#{{ $backup->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Backup Type</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full {{ $backup->backup_type === 'full' ? 'bg-blue-100 text-blue-800' : ($backup->backup_type === 'database' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">{{ ucfirst($backup->backup_type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($backup->status === 'completed')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-check mr-1"></i>Completed</span>
                                @elseif($backup->status === 'in_progress')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                            class="fas fa-spinner fa-spin mr-1"></i>In Progress</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                            class="fas fa-times mr-1"></i>Failed</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Size</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $backup->size_bytes ? round($backup->size_bytes / 1048576, 2) . ' MB (' . number_format($backup->size_bytes) . ' bytes)' : 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timing Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $backup->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        @if ($backup->started_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Started At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $backup->started_at->format('d/m/Y H:i:s') }}
                                </dd>
                            </div>
                        @endif
                        @if ($backup->completed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $backup->completed_at->format('d/m/Y H:i:s') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $backup->started_at->diffForHumans($backup->completed_at, true) }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Initiated By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $backup->initiatedBy?->name ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($backup->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-yellow-600"></i>Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $backup->notes }}</p>
                </div>
            @endif

            @if ($backup->error_message)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-2 border-red-200">
                    <h3 class="text-lg font-semibold text-red-900 mb-4"><i
                            class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Error Message</h3>
                    <pre class="text-sm bg-red-50 p-4 rounded overflow-x-auto text-red-800">{{ $backup->error_message }}</pre>
                </div>
            @endif

            @if ($backup->status === 'completed')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Actions</h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('healthcare.backups.download', $backup) }}"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-download mr-2"></i>Download Backup</a>
                        <button onclick="restoreBackup()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-undo mr-2"></i>Restore Database</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        async function restoreBackup() {
            const confirmed = await Dialog.danger(
                'WARNING: This will restore the database to this backup point. All current data will be lost. Are you sure?'
                );
            if (!confirmed) return;
            const finalConfirmed = await Dialog.danger(
                'FINAL CONFIRMATION: This will proceed with database restore. Are you absolutely sure?');
            if (!finalConfirmed) return;
            fetch('{{ route('healthcare.backups.restore', $backup) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        confirm: 'OK'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Dialog.alert(data.message);
                    if (data.success) {
                        window.location.href = '{{ route('healthcare.backups.index') }}';
                    }
                })
                .catch(error => Dialog.warning('Restore failed'));
        }
    </script>
</x-app-layout>
