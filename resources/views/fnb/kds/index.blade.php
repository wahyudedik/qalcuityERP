@extends('layouts.app')

@section('title', 'Kitchen Display System')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Kitchen Display System</h1>
            <p class="mt-1 text-sm text-gray-600">Real-time kitchen order management</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-yellow-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Preparing</div>
                <div class="text-2xl font-bold text-blue-700">{{ $stats['preparing'] }}</div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Ready</div>
                <div class="text-2xl font-bold text-green-700">{{ $stats['ready'] }}</div>
            </div>
            <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-sm text-red-600">Overdue</div>
                <div class="text-2xl font-bold text-red-700">{{ $stats['overdue'] }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="text-sm text-purple-600">Avg Time</div>
                <div class="text-2xl font-bold text-purple-700">{{ round($stats['avg_prep_time']) }}m</div>
            </div>
        </div>

        <!-- Station Filter -->
        <div class="mb-6 flex space-x-2">
            @foreach ($stations as $s)
                <a href="{{ route('fnb.kds.index', ['station' => $s === 'all' ? null : $s]) }}"
                    class="px-4 py-2 rounded-lg {{ ($station ?? 'all') === $s ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                    {{ ucfirst($s) }}
                </a>
            @endforeach
        </div>

        <!-- Tickets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($tickets as $ticket)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $ticket->isOverdue() ? 'border-4 border-red-500' : '' }}"
                    data-ticket-id="{{ $ticket->id }}">
                    <!-- Header -->
                    <div
                        class="p-4 {{ $ticket->priority === 'vip' ? 'bg-purple-100' : ($ticket->priority === 'rush' ? 'bg-red-100' : 'bg-gray-50') }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-lg font-bold">{{ $ticket->ticket_number }}</div>
                                <div class="text-xs text-gray-600">Order #{{ $ticket->fb_order_id }}</div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                        @if ($ticket->priority !== 'normal')
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-red-600 text-white">
                                {{ strtoupper($ticket->priority) }}
                            </span>
                        @endif
                    </div>

                    <!-- Items -->
                    <div class="p-4">
                        <div class="space-y-2">
                            @foreach ($ticket->items as $item)
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium">{{ $item->quantity }}x {{ $item->menuItem->name }}</div>
                                        @if ($item->special_instructions)
                                            <div class="text-xs text-gray-500 italic">{{ $item->special_instructions }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($ticket->chef_notes)
                            <div class="mt-3 p-2 bg-yellow-50 rounded text-xs">
                                <strong>Chef Notes:</strong> {{ $ticket->chef_notes }}
                            </div>
                        @endif

                        <!-- Timer -->
                        @if ($ticket->started_at)
                            <div class="mt-3 flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="{{ $ticket->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    {{ $ticket->getElapsedTime() }}m / {{ $ticket->estimated_time }}m
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="p-4 border-t bg-gray-50 flex space-x-2">
                        @if ($ticket->status === 'pending')
                            <button onclick="startTicket({{ $ticket->id }})"
                                class="flex-1 bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-sm">
                                Start Preparing
                            </button>
                        @elseif($ticket->status === 'preparing')
                            <button onclick="completeTicket({{ $ticket->id }})"
                                class="flex-1 bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm">
                                Mark Ready
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    No active tickets
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
            }).then(() => location.reload());
        }

        function completeTicket(ticketId) {
            fetch(`/fnb/kds/tickets/${ticketId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            }).then(() => location.reload());
        }

        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
    </script>
@endsection
