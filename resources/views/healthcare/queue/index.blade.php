<x-app-layout title="Antrian Pasien">
    <x-slot name="header">Antrian</x-slot>

    <x-slot name="pageTitle">Manajemen Antrian</x-slot>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $statistics['waiting'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Menunggu</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $statistics['called'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Dipanggil</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $statistics['serving'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Dilayani</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $statistics['completed'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Selesai</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $statistics['skipped'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Dilewati</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua</option>
                    <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Menunggu</option>
                    <option value="called" {{ request('status') === 'called' ? 'selected' : '' }}>Dipanggil</option>
                    <option value="serving" {{ request('status') === 'serving' ? 'selected' : '' }}>Dilayani</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Dilewati</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
                    onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            @if (request()->hasAny(['status', 'queue_type', 'date']))
                <a href="{{ route('healthcare.queue.index') }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    {{-- Queue Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if ($queues->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p>Tidak ada antrian hari ini</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">No. Antrian</th>
                            <th class="px-6 py-3 text-left">Pasien</th>
                            <th class="px-6 py-3 text-left">Dokter</th>
                            <th class="px-6 py-3 text-center">Posisi</th>
                            <th class="px-6 py-3 text-center">Est. Tunggu</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($queues as $queue)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono font-semibold text-gray-900">
                                    {{ $queue->queue_number ?? ($queue->token_number ?? '-') }}
                                </td>
                                <td class="px-6 py-3 text-gray-900">
                                    {{ $queue->patient?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $queue->doctor?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-center text-gray-700">
                                    {{ $queue->queue_position ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-center text-gray-700">
                                    {{ $queue->estimated_wait_minutes ?? 0 }} min
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $statusColors = [
                                            'waiting' => 'bg-yellow-100 text-yellow-700',
                                            'called' => 'bg-blue-100 text-blue-700',
                                            'serving' => 'bg-purple-100 text-purple-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'skipped' => 'bg-red-100 text-red-700',
                                            'cancelled' => 'bg-gray-100 text-gray-700',
                                            'no_show' => 'bg-gray-100 text-gray-700',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$queue->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($queue->status ?? 'unknown') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($queues->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $queues->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
