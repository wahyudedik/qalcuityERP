<x-app-layout>
    <x-slot name="header">Check-in — Reservation #{{ $reservation->reservation_number }}</x-slot>

    @php
        $settings = \App\Models\HotelSetting::where('tenant_id', $reservation->tenant_id)->first();
        $guest = $reservation->guest;
        $roomType = $reservation->roomType;
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Guest Info Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Guest
                Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Name</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guest?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Phone</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guest?->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Email</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guest?->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">ID Type / Number</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $guest?->id_type ?? '-' }}
                        {{ $guest?->id_number ?? '' }}</p>
                </div>
            </div>
        </div>

        {{-- Reservation Summary Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Reservation
                Summary</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Room Type</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $roomType?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Check-in</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $reservation->check_in_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Check-out</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ $reservation->check_out_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Nights</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->nights }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Rate/Night</p>
                    <p class="font-medium text-gray-900 dark:text-white">Rp
                        {{ number_format($reservation->rate_per_night, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Grand Total</p>
                    <p class="font-bold text-blue-600 dark:text-blue-400">Rp
                        {{ number_format($reservation->grand_total, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Check-in Form --}}
        <form method="POST" action="{{ route('hotel.checkin.process', $reservation) }}"
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            @csrf
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Check-in
                Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                {{-- Room Assignment --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Room Assignment
                        *</label>
                    <select name="room_id" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a room</option>
                        @forelse($availableRooms as $room)
                            <option value="{{ $room->id }}" @selected($reservation->room_id === $room->id)>
                                Room {{ $room->number }} — Floor {{ $room->floor }}
                                {{ $room->building ? '(' . $room->building . ')' : '' }}
                            </option>
                        @empty
                            <option value="" disabled>No available rooms</option>
                        @endforelse
                    </select>
                    @if ($availableRooms->isEmpty())
                        <p class="mt-1 text-xs text-red-500">No rooms available for this room type.</p>
                    @endif
                </div>

                {{-- Key Card Number --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Key Card
                        Number</label>
                    <input type="text" name="key_card_number" value="{{ old('key_card_number') }}"
                        placeholder="e.g., KC-00123"
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Deposit Section --}}
            @if ($settings?->deposit_required)
                <div
                    class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                    <h4 class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Deposit Required
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Deposit
                                Amount (IDR)</label>
                            <input type="number" name="deposit_amount" step="1000" min="0"
                                value="{{ old('deposit_amount', $settings->default_deposit_amount ?? 0) }}"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Deposit
                                Method</label>
                            <select name="deposit_method"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Notes</label>
                <textarea name="notes" rows="3" placeholder="Any special notes for this check-in..."
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div
                class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-4 border-t border-gray-100 dark:border-white/10">
                <a href="{{ route('hotel.reservations.show', $reservation) }}"
                    class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                    ← Back to Reservation
                </a>
                <button type="submit" {{ $availableRooms->isEmpty() ? 'disabled' : '' }}
                    class="w-full sm:w-auto px-8 py-3 text-base font-medium bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Process Check-in
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
