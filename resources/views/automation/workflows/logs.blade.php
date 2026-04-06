@extends('layouts.app')

@section('title', 'Workflow Execution Logs')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('automation.workflows.show', $workflow) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                ← Back to Workflow
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Execution Logs</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $workflow->name }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow sm:rounded-lg mb-6 p-4">
            <form method="GET" action="{{ route('automation.workflows.logs', $workflow) }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Triggered
                            By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started
                            At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed
                            At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="showLogDetails({{ $log->id }})">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->triggered_by }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->duration_ms ? $log->duration_ms . ' ms' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->started_at->format('Y-m-d H:i:s') }}
                                <div class="text-xs text-gray-400">{{ $log->started_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->completed_at ? $log->completed_at->format('Y-m-d H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate">
                                {{ Str::limit($log->error_message, 50) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                No execution logs found matching your filters
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>

    <!-- Log Details Modal -->
    <div id="logDetailsModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeLogModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Execution Details</h3>
                    <div id="logDetailsContent" class="space-y-4">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeLogModal()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const logData = @json(
            $logs->map(fn($log) => [
                    'id' => $log->id,
                    'triggered_by' => $log->triggered_by,
                    'status' => $log->status,
                    'duration_ms' => $log->duration_ms,
                    'started_at' => $log->started_at->format('Y-m-d H:i:s'),
                    'completed_at' => $log->completed_at?->format('Y-m-d H:i:s'),
                    'error_message' => $log->error_message,
                    'context_data' => $log->context_data,
                ]));

        function showLogDetails(logId) {
            const log = logData.find(l => l.id === logId);
            if (!log) return;

            const content = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500">Triggered By</label>
                <p class="mt-1 text-sm text-gray-900">${log.triggered_by}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Status</label>
                <p class="mt-1 text-sm text-gray-900 capitalize">${log.status}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Started At</label>
                <p class="mt-1 text-sm text-gray-900">${log.started_at}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Completed At</label>
                <p class="mt-1 text-sm text-gray-900">${log.completed_at || '-'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Duration</label>
                <p class="mt-1 text-sm text-gray-900">${log.duration_ms ? log.duration_ms + ' ms' : '-'}</p>
            </div>
        </div>
        
        ${log.error_message ? `
            <div>
                <label class="block text-sm font-medium text-gray-500">Error Message</label>
                <pre class="mt-1 text-sm text-red-600 bg-red-50 p-3 rounded overflow-auto">${log.error_message}</pre>
            </div>
            ` : ''}
        
        ${log.context_data ? `
            <div>
                <label class="block text-sm font-medium text-gray-500">Context Data</label>
                <pre class="mt-1 text-sm text-gray-700 bg-gray-50 p-3 rounded overflow-auto max-h-64">${JSON.stringify(log.context_data, null, 2)}</pre>
            </div>
            ` : ''}
    `;

            document.getElementById('logDetailsContent').innerHTML = content;
            document.getElementById('logDetailsModal').classList.remove('hidden');
        }

        function closeLogModal() {
            document.getElementById('logDetailsModal').classList.add('hidden');
        }
    </script>
@endsection
