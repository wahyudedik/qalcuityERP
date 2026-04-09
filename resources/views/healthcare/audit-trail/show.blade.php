<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Audit Log Details') }}</h2>
            <a href="{{ route('healthcare.audit-trail.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Log Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->user->name ?? 'N/A' }}
                                ({{ $log->user->email ?? 'N/A' }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Module</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ $log->module }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action Type</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full {{ $log->action_type === 'create' ? 'bg-green-100 text-green-800' : ($log->action_type === 'update' ? 'bg-yellow-100 text-yellow-800' : ($log->action_type === 'delete' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">{{ ucfirst($log->action_type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Severity</dt>
                            <dd class="mt-1">
                                @if ($log->severity === 'critical')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                            class="fas fa-exclamation-circle mr-1"></i>Critical</span>
                                @elseif($log->severity === 'warning')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                            class="fas fa-exclamation-triangle mr-1"></i>Warning</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-check-circle mr-1"></i>Info</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-laptop mr-2 text-gray-600"></i>Request Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $log->ip_address ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-xs text-gray-700">{{ $log->user_agent ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Request Method</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->request_method ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">URL</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $log->url ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-align-left mr-2 text-purple-600"></i>Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $log->description }}</p>
            </div>

            @if ($log->old_values || $log->new_values)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($log->old_values)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-history mr-2 text-yellow-600"></i>Old Values</h3>
                            <pre class="text-xs bg-gray-50 p-4 rounded overflow-x-auto">{{ json_encode(json_decode($log->old_values), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif

                    @if ($log->new_values)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-arrow-right mr-2 text-green-600"></i>New Values</h3>
                            <pre class="text-xs bg-gray-50 p-4 rounded overflow-x-auto">{{ json_encode(json_decode($log->new_values), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
