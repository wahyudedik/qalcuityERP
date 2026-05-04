<x-app-layout>
    <x-slot name="header">Tiket — {{ $ticket->ticket_number ?? '#' . $ticket->id }}</x-slot>

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('customer-portal.tickets.index') }}"
            class="hover:text-blue-600">Tiket</a>
        <span>/</span>
        <span class="text-gray-900">{{ $ticket->ticket_number ?? '#' . $ticket->id }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Conversation --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Original Ticket --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $ticket->subject }}</h3>
                    @php
                        $tc = match ($ticket->status) {
                            'open' => 'amber',
                            'in_progress' => 'blue',
                            'resolved' => 'green',
                            'closed' => 'gray',
                            default => 'gray',
                        };
                    @endphp
                    <span
                        class="px-2 py-0.5 rounded-full text-xs bg-{{ $tc  }}-100 text-{{ $tc }}-700 $tc }}-500/20 $tc }}-400">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $ticket->description }}</p>
                <p class="text-xs text-gray-400 mt-3">
                    {{ $ticket->created_at?->format('d/m/Y H:i') }}</p>
            </div>

            {{-- Replies --}}
            @foreach ($ticket->replies ?? [] as $reply)
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-6 {{ $reply->is_internal ? 'opacity-50' : '' }}">
                    <div class="flex items-center gap-2 mb-2">
                        <span
                            class="text-sm font-medium text-gray-900">{{ $reply->user?->name ?? 'Anda' }}</span>
                        @if ($reply->user && $reply->user?->role !== 'customer')
                            <span
                                class="px-1.5 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Staff</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $reply->body }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $reply->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            @endforeach

            {{-- Reply Form --}}
            @if (!in_array($ticket->status, ['closed']))
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <form method="POST" action="{{ route('customer-portal.tickets.reply', $ticket) }}">
                        @csrf
                        <label class="block text-xs font-medium text-gray-600 mb-1">Balas</label>
                        <textarea name="message" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"
                            placeholder="Tulis balasan..."></textarea>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim
                            Balasan</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Ticket Info --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Info Tiket</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">No. Tiket</p>
                        <p class="font-medium text-gray-900">
                            {{ $ticket->ticket_number ?? '#' . $ticket->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Prioritas</p>
                        @php
                            $pc = match ($ticket->priority ?? 'medium') {
                                'urgent' => 'red',
                                'high' => 'orange',
                                'medium' => 'blue',
                                'low' => 'gray',
                                default => 'gray',
                            };
                        @endphp
                        <span
                            class="px-2 py-0.5 rounded-full text-xs bg-{{ $pc  }}-100 text-{{ $pc }}-700 $pc }}-500/20 $pc }}-400">{{ ucfirst($ticket->priority ?? 'medium') }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kategori</p>
                        <p class="font-medium text-gray-900">
                            {{ ucfirst($ticket->category ?? 'general') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Dibuat</p>
                        <p class="font-medium text-gray-900">
                            {{ $ticket->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
