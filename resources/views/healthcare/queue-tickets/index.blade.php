<x-app-layout>
    <x-slot name="header">{{ __('Queue Tickets') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.queue-tickets.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Issue New Ticket
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-600 rounded-md p-3">
                            <i class="fas fa-ticket-alt text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Today</p>
                            <p class="text-2xl font-semibold text-blue-600">{{ $statistics['total'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Waiting</p>
                            <p class="text-2xl font-semibold text-yellow-600">{{ $statistics['waiting'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Served</p>
                            <p class="text-2xl font-semibold text-green-600">{{ $statistics['served'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <i class="fas fa-times text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Cancelled</p>
                            <p class="text-2xl font-semibold text-red-600">{{ $statistics['cancelled'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('healthcare.queue-tickets.index') }}" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="waiting" @selected(request('status') === 'waiting')>Waiting</option>
                            <option value="called" @selected(request('status') === 'called')>Called</option>
                            <option value="served" @selected(request('status') === 'served')>Served</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Queue Tickets Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Queue Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Issued At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-lg font-bold rounded-full bg-blue-100 text-blue-800">
                                        {{ $ticket->queue_number }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ticket->patient->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $ticket->department->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->issued_at ? $ticket->issued_at->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($ticket->status === 'waiting') bg-yellow-100 text-yellow-800
                                    @elseif($ticket->status === 'called') bg-blue-100 text-blue-800
                                    @elseif($ticket->status === 'served') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('healthcare.queue-tickets.show', $ticket) }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($ticket->status === 'waiting')
                                        <form action="{{ route('healthcare.queue-tickets.update', $ticket) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="called">
                                            <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                                <i class="fas fa-bullhorn"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="deleteTicket({{ $ticket->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-ticket-{{ $ticket->id }}"
                                        action="{{ route('healthcare.queue-tickets.destroy', $ticket) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No queue tickets found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($tickets->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteTicket(id) {
                if (confirm('Are you sure you want to delete this ticket?')) {
                    document.getElementById(`delete-ticket-${id}`).submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
