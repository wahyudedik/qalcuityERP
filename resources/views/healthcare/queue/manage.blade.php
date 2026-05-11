<x-app-layout>
    <x-slot name="header">Manajemen Antrian</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        @php
            $totalQueues = \App\Models\QueueManagement::where('tenant_id', $tid)
                ->whereDate('created_at', today())
                ->count();
            $waitingQueues = \App\Models\QueueManagement::where('tenant_id', $tid)->where('status', 'waiting')->count();
            $inProgressQueues = \App\Models\QueueManagement::where('tenant_id', $tid)
                ->where('status', 'in_progress')
                ->count();
            $completedQueues = \App\Models\QueueManagement::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
            $skippedQueues = \App\Models\QueueManagement::where('tenant_id', $tid)->where('status', 'skipped')->count();
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Antrian Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalQueues }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $waitingQueues }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Dipanggil</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $inProgressQueues }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $completedQueues }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Dilewati</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $skippedQueues }}</p>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4">
            <div class="flex flex-col sm:flex-row gap-2 flex-1">
                <select name="department" id="filter-department"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Departemen</option>
                    <option value="Poli Umum">Poli Umum</option>
                    <option value="Poli Gigi">Poli Gigi</option>
                    <option value="Poli Anak">Poli Anak</option>
                    <option value="Laboratorium">Laboratorium</option>
                    <option value="Farmasi">Farmasi</option>
                </select>
                <select name="status" id="filter-status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="waiting">Menunggu</option>
                    <option value="in_progress">Dipanggil</option>
                    <option value="completed">Selesai</option>
                    <option value="skipped">Dilewati</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button onclick="openAddQueueModal()"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah
                    Antrian</button>
                <a href="{{ route('healthcare.queue.display') }}" target="_blank"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    Display TV
                </a>
            </div>
        </div>
    </div>

    {{-- Queue Management Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Antrian</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter/Loket</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="queue-table-body">
                    @forelse($queues ?? [] as $queue)
                        <tr class="hover:bg-gray-50 queue-row" data-queue-id="{{ $queue->id }}">
                            <td class="px-4 py-3">
                                <span class="text-xl font-black text-blue-600">{{ $queue->queue_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $queue->patient ? $queue->patient?->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $queue->created_at ? \Carbon\Carbon::parse($queue->created_at)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                    {{ $queue->department ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900">{{ $queue->counter ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($queue->status === 'waiting')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Menunggu</span>
                                @elseif($queue->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Dipanggil</span>
                                @elseif($queue->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Selesai</span>
                                @elseif($queue->status === 'skipped')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Dilewati</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($queue->status === 'waiting')
                                        <button onclick="callQueue({{ $queue->id }})"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Panggil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button onclick="skipQueue({{ $queue->id }})"
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg" title="Lewati">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    @elseif($queue->status === 'in_progress')
                                        <button onclick="completeQueue({{ $queue->id }})"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="Selesai">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <p>Belum ada antrian hari ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Queue Modal --}}
    <div id="modal-add-queue"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Antrian Baru</h3>
                <button onclick="closeAddQueueModal()" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('healthcare.queue.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pasien
                            *</label>
                        <select name="patient_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Pasien --</option>
                            @if (isset($patients))
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->full_name }} -
                                        {{ $patient->medical_record_number }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Departemen
                            *</label>
                        <select name="department" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Departemen --</option>
                            <option value="Poli Umum">Poli Umum</option>
                            <option value="Poli Gigi">Poli Gigi</option>
                            <option value="Poli Anak">Poli Anak</option>
                            <option value="Laboratorium">Laboratorium</option>
                            <option value="Farmasi">Farmasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dokter/Loket</label>
                        <input type="text" name="counter" placeholder="Contoh: Dr. Ahmad / Loket 1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prioritas</label>
                        <select name="priority"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeAddQueueModal()"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openAddQueueModal() {
                document.getElementById('modal-add-queue').classList.remove('hidden');
            }

            function closeAddQueueModal() {
                document.getElementById('modal-add-queue').classList.add('hidden');
            }

            async function callQueue(queueId) {
                const confirmed = await Dialog.confirm('Panggil antrian ini?');
                if (!confirmed) return;
                fetch(`/healthcare/queue/${queueId}/call`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            async function skipQueue(queueId) {
                const confirmed = await Dialog.confirm('Lewati antrian ini?');
                if (!confirmed) return;
                fetch(`/healthcare/queue/${queueId}/skip`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            async function completeQueue(queueId) {
                const confirmed = await Dialog.confirm('Tandai antrian ini selesai?');
                if (!confirmed) return;
                fetch(`/healthcare/queue/${queueId}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                location.reload();
            }, 30000);
        </script>
    @endpush
</x-app-layout>
