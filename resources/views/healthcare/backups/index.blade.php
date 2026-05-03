<x-app-layout>
    <x-slot name="header">{{ __('Database Backups') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.backups.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Backup</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-database text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Backups</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['completed'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-times-circle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Failed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['failed'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-hdd text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Size</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_size_mb'] }} MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('healthcare.backups.index') }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Status</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="in_progress"
                                    {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Backup Type</label>
                            <select name="backup_type" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                <option value="full" {{ request('backup_type') === 'full' ? 'selected' : '' }}>Full
                                </option>
                                <option value="database" {{ request('backup_type') === 'database' ? 'selected' : '' }}>
                                    Database</option>
                                <option value="files" {{ request('backup_type') === 'files' ? 'selected' : '' }}>Files
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-search mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Backup History</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Backup ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Size</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Initiated By</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($backups as $backup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $backup->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $backup->backup_type === 'full' ? 'bg-blue-100 text-blue-800' : ($backup->backup_type === 'database' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">{{ ucfirst($backup->backup_type) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $backup->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $backup->size_bytes ? round($backup->size_bytes / 1048576, 2) . ' MB' : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($backup->status === 'completed')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i
                                                    class="fas fa-check mr-1"></i>Completed</span>
                                        @elseif($backup->status === 'in_progress')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                                    class="fas fa-spinner fa-spin mr-1"></i>In Progress</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i
                                                    class="fas fa-times mr-1"></i>Failed</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $backup->initiatedBy->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.backups.show', $backup) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        @if ($backup->status === 'completed')
                                            <a href="{{ route('healthcare.backups.download', $backup) }}"
                                                class="text-green-600 hover:text-green-900 mr-3"><i
                                                    class="fas fa-download"></i></a>
                                        @endif
                                        <button onclick="deleteBackup({{ $backup->id }})"
                                            class="text-red-600 hover:text-red-900"><i
                                                class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No backups found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $backups->links() }}
            </div>
        </div>
    </div>

    <script>
        function deleteBackup(id) {
            if (confirm('Are you sure you want to delete this backup?')) {
                fetch(`{{ route('healthcare.backups.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Delete failed'));
            }
        }
    </script>
</x-app-layout>
