@extends('layouts.app')

@section('title', $workflow->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('automation.workflows.index') }}"
                class="text-blue-600 hover:text-blue-900 text-sm">
                ← Kembali ke Workflows
            </a>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mt-2">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $workflow->description }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="testWorkflow({{ $workflow->id }})"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Test Workflow
                    </button>
                    <form action="{{ route('automation.workflows.destroy', $workflow) }}" method="POST"
                        onsubmit="return confirm('Hapus workflow ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Workflow Info Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Tipe Trigger</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900 capitalize">
                    {{ $workflow->trigger_type }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $workflow->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $workflow->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Prioritas</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $workflow->priority }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Total Eksekusi</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">
                    {{ number_format($workflow->execution_count) }}</dd>
            </div>
        </div>

        <!-- Actions List -->
        <div class="bg-white shadow sm:rounded-lg mb-8">
            <div
                class="px-4 py-5 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Aksi ({{ $actions->count() }})
                </h3>
                <button onclick="showAddActionModal()"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Tambah Aksi
                </button>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($actions as $action)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900">#{{ $action->order }}</span>
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', $action->action_type) }}
                                    </span>
                                    @if (!$action->is_active)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                                @if ($action->condition)
                                    <p class="mt-1 text-xs text-gray-500">
                                        Kondisi: {{ $action->condition['field'] ?? '' }}
                                        {{ $action->condition['operator'] ?? '' }} {{ $action->condition['value'] ?? '' }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button onclick="deleteAction({{ $action->id }})"
                                    class="text-red-600 hover:text-red-900 text-sm">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-12 text-center text-sm text-gray-500">
                        Belum ada aksi dikonfigurasi. Klik "Tambah Aksi" untuk memulai.
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Recent Execution Logs -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Eksekusi Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Dipicu Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">
                                Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Waktu Mulai</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">
                                Error</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->triggered_by }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                    {{ $log->duration_ms ? $log->duration_ms . ' ms' : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->started_at?->diffForHumans() ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 hidden md:table-cell">
                                    {{ Str::limit($log->error_message, 50) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada log eksekusi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Test Result Modal -->
    <div id="testResultModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                onclick="closeTestModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Hasil Test
                    </h3>
                    <div class="mt-2">
                        <pre id="testResultContent"
                            class="text-sm text-gray-700 bg-gray-50 p-4 rounded overflow-auto max-h-96"></pre>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeTestModal()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testWorkflow(workflowId) {
            fetch(`/automation/workflows/${workflowId}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('testResultContent').textContent = JSON.stringify(data, null, 2);
                    document.getElementById('testResultModal').classList.remove('hidden');
                });
        }

        function closeTestModal() {
            document.getElementById('testResultModal').classList.add('hidden');
        }

        function deleteAction(actionId) {
            if (!confirm('Hapus aksi ini?')) return;

            fetch(`/automation/workflows/actions/${actionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(() => location.reload());
        }

        function showAddActionModal() {
            alert('Modal tambah aksi — akan diimplementasikan dengan form konfigurasi aksi');
        }
    </script>
@endsection
