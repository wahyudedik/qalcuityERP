<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Audit Trail') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-clipboard-list text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Logs</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_logs'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-calendar-day text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['today'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-calendar-week text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Week</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['this_week'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Critical Actions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['critical_actions'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('healthcare.audit-trail.index') }}"
                        class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                            <select name="action_type" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Actions</option>
                                <option value="create" {{ request('action_type') === 'create' ? 'selected' : '' }}>
                                    Create</option>
                                <option value="update" {{ request('action_type') === 'update' ? 'selected' : '' }}>
                                    Update</option>
                                <option value="delete" {{ request('action_type') === 'delete' ? 'selected' : '' }}>
                                    Delete</option>
                                <option value="view" {{ request('action_type') === 'view' ? 'selected' : '' }}>View
                                </option>
                                <option value="export" {{ request('action_type') === 'export' ? 'selected' : '' }}>
                                    Export</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                            <input type="text" name="module" value="{{ request('module') }}"
                                placeholder="e.g., patients" class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select name="user_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
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
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Audit Logs</h3>
                        <button onclick="exportAuditTrail()"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-file-export mr-2"></i>Export</button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Module
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($logs as $log)
                                    <tr class="{{ $log->severity === 'critical' ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->user?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $log->module }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $log->action_type === 'create' ? 'bg-green-100 text-green-800' : ($log->action_type === 'update' ? 'bg-yellow-100 text-yellow-800' : ($log->action_type === 'delete' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">{{ ucfirst($log->action_type) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($log->severity === 'critical')
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i
                                                        class="fas fa-exclamation-circle mr-1"></i>Critical</span>
                                            @elseif($log->severity === 'warning')
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                                        class="fas fa-exclamation-triangle mr-1"></i>Warning</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i
                                                        class="fas fa-check-circle mr-1"></i>Info</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('healthcare.audit-trail.show', $log) }}"
                                                class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i>
                                                View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No audit logs
                                            found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    <script>
        function exportAuditTrail() {
            const url = '{{ route('healthcare.audit-trail.export') }}' +
                '?date_from={{ request('date_from') }}' +
                '&date_to={{ request('date_to') }}';

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const blob = new Blob([JSON.stringify(data, null, 2)], {
                        type: 'application/json'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'audit-trail-export.json';
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => alert('Export failed'));
        }
    </script>
</x-app-layout>
