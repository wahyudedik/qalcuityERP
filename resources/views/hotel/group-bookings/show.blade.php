<x-app-layout>
    <x-slot name="header">{{ $groupBooking->group_name }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('hotel.group-bookings.index') }}"
            class="text-gray-600 hover:text-blue-600 inline-flex items-center gap-1 text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Status</p>
                @php
                    $statusColors = [
                        'pending' => 'bg-gray-100 text-gray-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'active' => 'bg-green-100 text-green-700',
                        'completed' => 'bg-purple-100 text-purple-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <p class="text-lg font-bold mt-1 {{ $statusColors[$groupBooking->status] }}">
                    {{ ucfirst($groupBooking->status) }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Rooms / Guests</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    {{ $groupBooking->total_rooms }} / {{ $groupBooking->total_guests }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Payment Progress</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    {{ number_format($groupBooking->paid_amount, 0) }} /
                    {{ number_format($groupBooking->total_amount, 0) }}
                </p>
                <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full"
                        style="width: {{ $groupBooking->payment_percentage }}%"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Balance Due</p>
                <p class="text-lg font-bold text-red-600 mt-1">
                    {{ number_format($groupBooking->balance, 0) }}
                </p>
            </div>
        </div>

        {{-- Group Details --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Group Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500">Organizer</p>
                    <p class="font-medium text-gray-900">{{ $groupBooking->organizer?->name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $groupBooking->organizer?->email ?? $groupBooking->organizer?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Group Type</p>
                    <p class="font-medium text-gray-900">{{ ucfirst($groupBooking->type) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Stay Period</p>
                    <p class="font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($groupBooking->start_date)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($groupBooking->end_date)->format('d M Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Created By</p>
                    <p class="font-medium text-gray-900">
                        {{ $groupBooking->creator?->name ?? 'System' }}</p>
                </div>
            </div>

            @if ($groupBooking->benefits)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-2">Special Benefits</p>
                    <ul class="space-y-1">
                        @foreach ($groupBooking->benefits as $benefit)
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $benefit }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($groupBooking->notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $groupBooking->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Reservations List --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Reservations
                    ({{ $reservations->count() }})</h3>
                <button onclick="openAddReservationModal()"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Add Reservation
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Guest
                            </th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Room
                                Type</th>
                            <th
                                class="text-left py-3 px-2 text-xs font-medium text-gray-500 hidden sm:table-cell">
                                Check-in</th>
                            <th
                                class="text-left py-3 px-2 text-xs font-medium text-gray-500 hidden sm:table-cell">
                                Check-out</th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Status
                            </th>
                            <th class="text-right py-3 px-2 text-xs font-medium text-gray-500">
                                Amount</th>
                            <th class="text-right py-3 px-2 text-xs font-medium text-gray-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 px-2">
                                    <p class="font-medium text-gray-900">
                                        {{ $reservation->guest?->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $reservation->guest?->email ?? $reservation->guest?->phone }}</p>
                                </td>
                                <td class="py-3 px-2 text-gray-600">
                                    {{ $reservation->roomType?->name }}</td>
                                <td class="py-3 px-2 text-gray-600 hidden sm:table-cell">
                                    {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M') }}
                                </td>
                                <td class="py-3 px-2 text-gray-600 hidden sm:table-cell">
                                    {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M') }}
                                </td>
                                <td class="py-3 px-2">
                                    <span
                                        class="px-2 py-1 rounded-lg text-xs font-medium
                                        @if ($reservation->status === 'confirmed') bg-blue-100 text-blue-700
                                        @elseif ($reservation->status === 'checked_in') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-right font-medium text-gray-900">
                                    {{ number_format($reservation->grand_total, 0) }}
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <a href="{{ route('hotel.reservations.show', $reservation) }}"
                                        class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                        View
                                    </a>
                                    @if ($groupBooking->status !== 'completed' && $groupBooking->status !== 'cancelled')
                                        <form
                                            action="{{ route('hotel.group-bookings.remove-reservation', $reservation) }}"
                                            method="POST" class="inline ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-700 text-xs font-medium"
                                                onclick="return confirm('Remove from group?')">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    No reservations in this group yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Action Buttons --}}
        @if ($groupBooking->status === 'pending')
            <div class="flex items-center justify-end gap-3">
                <form action="{{ route('hotel.group-bookings.confirm', $groupBooking) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                        Confirm Group Booking
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Add Reservation Modal --}}
    <div id="modal-add-reservation" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form action="{{ route('hotel.group-bookings.add-reservation', $groupBooking) }}" method="POST">
                @csrf

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Reservation to Group</h3>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Select
                            Reservation *</label>
                        <select name="reservation_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Choose reservation...</option>
                            <!-- You could load available reservations via AJAX -->
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Create the reservation first, then add it to this group.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-add-reservation').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Add to Group
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openAddReservationModal() {
                document.getElementById('modal-add-reservation').classList.remove('hidden');
            }
        </script>
    @endpush
</x-app-layout>
