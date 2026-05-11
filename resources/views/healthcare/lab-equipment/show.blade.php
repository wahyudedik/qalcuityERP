<x-app-layout>
    <x-slot name="header">{{ __('Lab Equipment Details') }} -
        {{ $equipment->name }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.lab-equipment.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Equipment Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Equipment Name</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $equipment->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Device ID</dt>
                            <dd class="mt-1 text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $equipment->device_id }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($equipment->type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Connection Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">{{ strtoupper($equipment->connection_type) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-network-wired mr-2 text-green-600"></i>Connection Settings</h3>
                    <dl class="space-y-4">
                        @if ($equipment->ip_address)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                <dd class="mt-1 text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                                    {{ $equipment->ip_address }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Poll Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $equipment->poll_interval }} seconds</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Connection Status</dt>
                            <dd class="mt-1">
                                @if ($equipment->is_connected)
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-circle text-green-500 mr-1"></i>Connected</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                            class="fas fa-circle text-red-500 mr-1"></i>Disconnected</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Auto-Poll</dt>
                            <dd class="mt-1">
                                @if ($equipment->auto_poll_enabled)
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800"><i
                                            class="fas fa-sync mr-1"></i>Enabled</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Disabled</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-cogs mr-2 text-orange-600"></i>Actions</h3>
                    <div class="space-y-3">
                        <button onclick="testConnection()"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-plug mr-2"></i>Test Connection
                        </button>
                    </div>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->created_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $equipment->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            @if ($equipment->connectionLogs && $equipment->connectionLogs->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-history mr-2 text-purple-600"></i>Recent Connection Logs
                        ({{ $equipment->connectionLogs->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($equipment->connectionLogs->take(20) as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $log->success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $log->success ? 'Success' : 'Failed' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">{{ $log->message ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            async function testConnection() {
                const confirmed = await Dialog.confirm('Test connection to {{ $equipment->name }}?');
                if (!confirmed) return;
                fetch('{{ route('healthcare.lab-equipment.test-connection', $equipment) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Dialog.alert(data.message);
                        location.reload();
                    })
                    .catch(error => Dialog.warning('Connection test failed'));
            }
        </script>
    @endpush
</x-app-layout>
