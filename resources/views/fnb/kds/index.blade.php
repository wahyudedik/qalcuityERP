@extends('layouts.app')

@section('title', 'Kitchen Display System — KOT')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Kitchen Display System</h1>
            <p class="mt-1 text-sm text-gray-600">Manajemen pesanan dapur secara real-time</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-yellow-600">Menunggu</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] ?? 0 }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Diproses</div>
                <div class="text-2xl font-bold text-blue-700">{{ $stats['preparing'] ?? 0 }}</div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Siap</div>
                <div class="text-2xl font-bold text-green-700">{{ $stats['ready'] ?? 0 }}</div>
            </div>
            <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-sm text-red-600">Terlambat</div>
                <div class="text-2xl font-bold text-red-700">{{ $stats['overdue'] ?? 0 }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="text-sm text-purple-600">Rata-rata Waktu</div>
                <div class="text-2xl font-bold text-purple-700">{{ round($stats['avg_prep_time'] ?? 0) }}m</div>
            </div>
        </div>

        <!-- Station Filter -->
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($stations as $s)
                <a href="{{ route('fnb.kds.index', ['station' => $s === 'all' ? null : $s]) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ ($station ?? 'all') === $s ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    {{ ucfirst($s === 'all' ? 'Semua' : $s) }}
                </a>
            @endforeach
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tickets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($tickets as $ticket)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $ticket->isOverdue() ? 'border-4 border-red-500' : 'border border-gray-200' }}"
                    data-ticket-id="{{ $ticket->id }}">
                    <!-- Header -->
                    <div
                        class="p-4 {{ $ticket->priority === 'vip' ? 'bg-purple-100' : ($ticket->priority === 'rush' ? 'bg-red-100' : 'bg-gray-50') }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ $ticket->ticket_number }}</div>
                                <div class="text-xs text-gray-600">Pesanan #{{ $ticket->fb_order_id }}</div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                @switch($ticket->status)
                                    @case('pending') Menunggu @break
                                    @case('preparing') Diproses @break
                                    @case('ready') Siap @break
                                    @case('served') Disajikan @break
                                    @case('cancelled') Dibatalkan @break
                                    @default {{ ucfirst($ticket->status) }}
                                @endswitch
                            </span>
                        </div>
                        @if ($ticket->priority !== 'normal')
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-red-600 text-white">
                                {{ $ticket->priority === 'rush' ? 'SEGERA' : 'VIP' }}
                            </span>
                        @endif
                    </div>

                    <!-- Items -->
                    <div class="p-4">
                        <div class="space-y-2">
                            @foreach ($ticket->items as $item)
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->quantity }}x {{ $item->menuItem?->name ?? $item->item_name ?? 'Item tidak diketahui' }}</div>
                                        @if ($item->special_instructions)
                                            <div class="text-xs text-gray-500 italic">{{ $item->special_instructions }}</div>
                                        @endif
                                        @if (!empty($item->modifiers))
                                            <div class="text-xs text-blue-600">
                                                {{ implode(', ', $item->modifiers) }}
                                            </div>
                                        @endif
                                    </div>
                                    @if ($item->is_completed)
                                        <span class="text-green-500 text-xs">✓</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($ticket->chef_notes)
                            <div class="mt-3 p-2 bg-yellow-50 rounded text-xs text-yellow-800">
                                <strong>Catatan Chef:</strong> {{ $ticket->chef_notes }}
                            </div>
                        @endif

                        <!-- Timer -->
                        @if ($ticket->started_at)
                            <div class="mt-3 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="{{ $ticket->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    {{ $ticket->getElapsedTime() }}m / {{ $ticket->estimated_time ?? '?' }}m
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50 flex space-x-2">
                        @if ($ticket->status === 'pending')
                            <button onclick="startTicket({{ $ticket->id }})"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors min-h-[44px]">
                                Mulai Proses
                            </button>
                        @elseif($ticket->status === 'preparing')
                            <button onclick="completeTicket({{ $ticket->id }})"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors min-h-[44px]">
                                Tandai Siap
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Tidak ada tiket aktif saat ini</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function startTicket(ticketId) {
            fetch(`/fnb/kds/tickets/${ticketId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            }).then(res => res.json()).then(() => location.reload()).catch(() => location.reload());
        }

        function completeTicket(ticketId) {
            fetch(`/fnb/kds/tickets/${ticketId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            }).then(res => res.json()).then(() => location.reload()).catch(() => location.reload());
        }

        // Auto-refresh setiap 30 detik
        setTimeout(() => location.reload(), 30000);
    </script>
@endsection
