<x-app-layout>
    <x-slot name="header">Room Change - {{ $reservation->reservation_number }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('hotel.reservations.show', $reservation) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Reservation
            </a>
    </div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('hotel.room-change.process', $reservation) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Current Room Info --}}
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Current Room</h3>
                                    <p class="text-sm text-gray-500">Currently assigned</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Room Number</span>
                                    <span
                                        class="font-semibold text-gray-900">{{ $reservation->room?->number }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Floor</span>
                                    <span
                                        class="font-semibold text-gray-900">{{ $reservation->room?->floor }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Room Type</span>
                                    <span
                                        class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                        {{ $reservation->roomType?->name }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Rate/Night</span>
                                    <span class="font-semibold text-gray-900">Rp
                                        {{ number_format($reservation->rate_per_night, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Status</span>
                                    <span
                                        class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Reservation Details --}}
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Reservation Details</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Guest</span>
                                    <span class="text-gray-900">{{ $reservation->guest?->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Check-in</span>
                                    <span
                                        class="text-gray-900">{{ $reservation->check_in_date->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Check-out</span>
                                    <span
                                        class="text-gray-900">{{ $reservation->check_out_date->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Remaining</span>
                                    <span
                                        class="font-semibold text-blue-600">{{ now()->diffInDays($reservation->check_out_date) }}
                                        nights</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Select New Room --}}
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Select New Room</h3>
                                    <p class="text-sm text-gray-500">{{ $availableRooms->count() }}
                                        rooms available</p>
                                </div>
                            </div>

                            @if ($availableRooms->isEmpty())
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No rooms
                                        available</h3>
                                    <p class="mt-1 text-sm text-gray-500">All rooms are currently
                                        occupied.</p>
                                </div>
                            @else
                                <div class="space-y-6">
                                    @foreach ($groupedRooms as $roomTypeName => $rooms)
                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                {{ $roomTypeName }}
                                            </h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach ($rooms as $room)
                                                    @php
                                                        $rateDiff =
                                                            $room->roomType?->base_rate - $reservation->rate_per_night;
                                                        $changeType =
                                                            $rateDiff > 0
                                                                ? 'upgrade'
                                                                : ($rateDiff < 0
                                                                    ? 'downgrade'
                                                                    : 'same');
                                                        $remainingNights = now()->diffInDays(
                                                            $reservation->check_out_date,
                                                        );
                                                        $totalDiff = $rateDiff * $remainingNights;
                                                    @endphp
                                                    <label class="relative cursor-pointer group">
                                                        <input type="radio" name="new_room_id"
                                                            value="{{ $room->id }}" class="peer sr-only" required>
                                                        <div
                                                            class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all hover:border-gray-300">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div>
                                                                    <p
                                                                        class="font-semibold text-gray-900">
                                                                        Room {{ $room->number }}</p>
                                                                    <p
                                                                        class="text-xs text-gray-500">
                                                                        Floor {{ $room->floor }}</p>
                                                                </div>
                                                                @if ($changeType === 'upgrade')
                                                                    <span
                                                                        class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">
                                                                        ↑ Upgrade
                                                                    </span>
                                                                @elseif($changeType === 'downgrade')
                                                                    <span
                                                                        class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                                                        ↓ Downgrade
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">
                                                                        Same Type
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="space-y-1 text-sm">
                                                                <div class="flex justify-between">
                                                                    <span
                                                                        class="text-gray-600">Rate</span>
                                                                    <span
                                                                        class="font-medium text-gray-900">
                                                                        Rp
                                                                        {{ number_format($room->roomType?->base_rate, 0, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                                @if ($rateDiff != 0)
                                                                    <div class="flex justify-between">
                                                                        <span
                                                                            class="text-gray-600">Difference</span>
                                                                        <span
                                                                            class="font-medium {{ $rateDiff > 0 ? 'text-amber-600' : 'text-blue-600' }}">
                                                                            {{ $rateDiff > 0 ? '+' : '' }}Rp
                                                                            {{ number_format($rateDiff, 0, ',', '.') }}/night
                                                                        </span>
                                                                    </div>
                                                                    <div
                                                                        class="flex justify-between pt-1 border-t border-gray-200">
                                                                        <span
                                                                            class="text-gray-600">Total
                                                                            ({{ $remainingNights }} nights)</span>
                                                                        <span
                                                                            class="font-semibold {{ $totalDiff > 0 ? 'text-amber-600' : 'text-blue-600' }}">
                                                                            {{ $totalDiff > 0 ? '+' : '' }}Rp
                                                                            {{ number_format($totalDiff, 0, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Reason --}}
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-4">
                            <label class="block text-sm font-medium text-gray-900 mb-3">
                                Reason for Room Change *
                            </label>
                            <textarea name="reason" rows="4" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g., Guest requested ocean view, AC malfunction, maintenance required, etc.">{{ old('reason') }}</textarea>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center justify-end gap-3 mt-6">
                            <a href="{{ route('hotel.reservations.show', $reservation) }}"
                                class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Confirm Room Change
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Error Toast --}}
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
            class="fixed top-4 right-4 z-50 max-w-sm">
            <div
                class="bg-red-50 border border-red-200 rounded-lg p-4 shadow-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button @click="show = false"
                        class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
