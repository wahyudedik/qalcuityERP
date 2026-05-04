<x-app-layout>
    <x-slot name="header">Reservation {{ $reservation->reservation_number }}</x-slot>

    <x-slot name="pageHeader">
        <div class="flex items-center gap-2 flex-wrap">
            @if ($reservation->status === 'pending')
                <form method="POST" action="{{ route('hotel.reservations.confirm', $reservation) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Confirm
                    </button>
                </form>
            @endif
            @if ($reservation->status === 'confirmed')
                <form method="POST" action="{{ route('hotel.checkin.process', $reservation) }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Check In
                    </button>
                </form>
            @endif
            @if ($reservation->status === 'checked_in')
                <form method="POST" action="{{ route('hotel.checkout.process', $reservation) }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Check Out
                    </button>
                </form>
                <a href="{{ route('hotel.room-change.form', $reservation) }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Change Room
                </a>
            @endif
            @if (!in_array($reservation->status, ['cancelled', 'checked_out']))
                <button onclick="document.getElementById('modal-cancel').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-100 text-red-700 text-sm font-medium hover:bg-red-200 transition">
                    Cancel
                </button>
            @endif
            <a href="{{ route('hotel.reservations.edit', $reservation) }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </x-slot>

    @php
        $nights = \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(
            \Carbon\Carbon::parse($reservation->check_out_date),
        );
        $statusColor = match ($reservation->status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-green-100 text-green-700',
            'checked_in' => 'bg-blue-100 text-blue-700',
            'checked_out' => 'bg-gray-100 text-gray-600',
            'cancelled' => 'bg-red-100 text-red-700',
            'no_show' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-500',
        };
        $sourceLabel = match ($reservation->source) {
            'direct' => 'Direct',
            'bookingcom' => 'Booking.com',
            'agoda' => 'Agoda',
            'expedia' => 'Expedia',
            'airbnb' => 'Airbnb',
            'tripadvisor' => 'TripAdvisor',
            default => ucfirst($reservation->source ?? 'Direct'),
        };
    @endphp

    {{-- Status Banner --}}
    <div class="{{ $statusColor }} rounded-2xl p-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="font-semibold text-lg">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</p>
                <p class="text-sm opacity-80">{{ $reservation->reservation_number }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm opacity-80">Created by {{ $reservation->createdBy?->name ?? 'System' }}</p>
            <p class="text-sm opacity-60">{{ $reservation->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Guest Info Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Guest Information
                </h3>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600">
                            {{ substr($reservation->guest?->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <a href="{{ route('hotel.guests.show', $reservation->guest) }}"
                                class="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                {{ $reservation->guest?->name ?? 'N/A' }}
                            </a>
                            <p class="text-sm text-gray-500">
                                {{ $reservation->guest?->email ?? '—' }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $reservation->guest?->phone ?? '—' }}</p>
                        </div>
                    </div>
                    @if ($reservation->guest?->vip_level)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                            {{ strtoupper($reservation->guest?->vip_level) }} VIP
                        </span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                    <div>
                        <p class="text-gray-500">ID Type</p>
                        <p class="font-medium text-gray-900">
                            {{ strtoupper($reservation->guest?->id_type ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">ID Number</p>
                        <p class="font-medium text-gray-900">
                            {{ $reservation->guest?->id_number ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Room Details Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Room Details
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Room Type</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->roomType?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Room Number</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->room?->number ?? 'Not assigned' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Floor</p>
                        <p class="font-medium text-gray-700">
                            {{ $reservation->room?->floor ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Building</p>
                        <p class="font-medium text-gray-700">
                            {{ $reservation->room?->building ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Stay Details Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Stay Details
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Check-in</p>
                        <p class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $reservation->roomType?->default_checkin_time ?? '14:00' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Check-out</p>
                        <p class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $reservation->roomType?->default_checkout_time ?? '12:00' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nights</p>
                        <p class="font-semibold text-gray-900">{{ $nights }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Adults</p>
                        <p class="font-medium text-gray-700">{{ $reservation->adults ?? 1 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Children</p>
                        <p class="font-medium text-gray-700">{{ $reservation->children ?? 0 }}</p>
                    </div>
                </div>
            </div>

            {{-- Special Requests --}}
            @if ($reservation->special_requests)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Special Requests
                    </h3>
                    <p class="text-gray-700 text-sm whitespace-pre-line">
                        {{ $reservation->special_requests }}</p>
                </div>
            @endif

            {{-- Check-in/out History --}}
            @if ($reservation->checkInOuts->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Check-in/out History</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-3 py-2 text-left">Type</th>
                                    <th class="px-3 py-2 text-left">Room</th>
                                    <th class="px-3 py-2 text-left">Processed At</th>
                                    <th class="px-3 py-2 text-left">Processed By</th>
                                    <th class="px-3 py-2 text-left">Key Card</th>
                                    <th class="px-3 py-2 text-right">Deposit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($reservation->checkInOuts as $cio)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cio->type === 'check_in' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                {{ $cio->type === 'check_in' ? 'Check-in' : 'Check-out' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $cio->room?->number ?? '—' }}</td>
                                        <td class="px-3 py-2 text-gray-600">
                                            {{ $cio->processed_at->format('d M Y, H:i') }}</td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ $cio->processedBy?->name ?? 'System' }}</td>
                                        <td class="px-3 py-2 text-gray-600">
                                            {{ $cio->key_card_number ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-700">
                                            {{ $cio->deposit ? 'Rp ' . number_format($cio->deposit, 0, ',', '.') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Financial Summary Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Financial Summary
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Rate/Night</span>
                        <span class="font-medium text-gray-900">Rp
                            {{ number_format($reservation->rate_per_night, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nights</span>
                        <span class="font-medium text-gray-900">{{ $nights }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-900">Rp
                            {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                    </div>
                    @if ($reservation->discount > 0)
                        <div class="flex justify-between text-red-500">
                            <span>Discount</span>
                            <span>- Rp {{ number_format($reservation->discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tax (11%)</span>
                        <span class="font-medium text-gray-900">Rp
                            {{ number_format($reservation->tax, 0, ',', '.') }}</span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex justify-between text-lg">
                        <span class="font-semibold text-gray-900">Grand Total</span>
                        <span class="font-bold text-green-600">Rp
                            {{ number_format($reservation->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Source & Booking Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Booking Info</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">Source</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">{{ $sourceLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Created by</span>
                        <span class="text-gray-700">{{ $reservation->createdBy?->name ?? 'System' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Created at</span>
                        <span class="text-gray-700">{{ $reservation->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($reservation->updated_at != $reservation->created_at)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Last updated</span>
                            <span class="text-gray-700">{{ $reservation->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Booking Timeline --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Booking Timeline</h3>
                <div class="space-y-4">
                    {{-- Created --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Created</p>
                            <p class="text-xs text-gray-500">
                                {{ $reservation->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    {{-- Confirmed --}}
                    @if ($reservation->status !== 'pending')
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Confirmed</p>
                                @if ($reservation->confirmed_at)
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($reservation->confirmed_at)->format('d M Y, H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- Checked In --}}
                    @if (in_array($reservation->status, ['checked_in', 'checked_out']))
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Checked In</p>
                                @if ($reservation->checked_in_at)
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($reservation->checked_in_at)->format('d M Y, H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- Checked Out --}}
                    @if ($reservation->status === 'checked_out')
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Checked Out</p>
                                @if ($reservation->checked_out_at)
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($reservation->checked_out_at)->format('d M Y, H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- Cancelled --}}
                    @if ($reservation->status === 'cancelled')
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Cancelled</p>
                                @if ($reservation->cancelled_at)
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($reservation->cancelled_at)->format('d M Y, H:i') }}
                                    </p>
                                @endif
                                @if ($reservation->cancel_reason)
                                    <p class="text-xs text-red-500 mt-1">{{ $reservation->cancel_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Room Change & Early/Late Request Buttons --}}
    @if (in_array($reservation->status, ['confirmed', 'checked_in']))
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mt-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Special Requests</h3>
            <div class="flex flex-wrap gap-3">
                <button onclick="openRoomChangeModal()"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Room Change
                </button>

                <button onclick="openEarlyLateModal('early_checkin')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Early Check-in
                </button>

                <button onclick="openEarlyLateModal('late_checkout')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    Late Check-out
                </button>
            </div>
        </div>
    @endif

    {{-- Room Change Modal --}}
    <div id="modal-room-change" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Room Change</h3>
                <p class="text-sm text-gray-600 mb-4">
                    This will open a form to select a new room and calculate rate differences.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-room-change').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <a href="{{ route('hotel.reservations.room-change', $reservation) }}"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Continue
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Early Check-in / Late Check-out Modal --}}
    <div id="modal-early-late" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form action="{{ route('hotel.reservations.request-early-late', $reservation) }}" method="POST">
                @csrf

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4" id="early-late-title">Request
                    </h3>

                    <input type="hidden" name="request_type" id="request-type-input">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Requested
                                Time *</label>
                            <input type="datetime-local" name="requested_time" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                            <textarea name="reason" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for this request..."></textarea>
                        </div>

                        <div class="p-3 rounded-xl bg-yellow-50 border border-yellow-200">
                            <p class="text-xs text-yellow-800">
                                <strong>Note:</strong> This request requires approval. Additional charges may apply
                                based on hotel policy.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-early-late').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div id="modal-cancel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Cancel Reservation</h3>
                <button onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('hotel.reservations.cancel', $reservation) }}"
                class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p class="text-sm text-gray-600">Cancel reservation
                    {{ $reservation->reservation_number }}?</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cancellation
                        Reason</label>
                    <textarea name="cancel_reason" rows="3" placeholder="Optional reason..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Back</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Cancel
                        Reservation</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @if (session('success'))
            showToast(@json(session('success')), 'success');
        @endif
        @if (session('error'))
            showToast(@json(session('error')), 'error');
        @endif
        @if ($errors->any())
            showToast(@json($errors->first()), 'error');
        @endif

        <script>
            function openRoomChangeModal() {
                document.getElementById('modal-room-change').classList.remove('hidden');
            }

            function openEarlyLateModal(type) {
                const titles = {
                    'early_checkin': 'Early Check-in Request',
                    'late_checkout': 'Late Check-out Request'
                };

                document.getElementById('early-late-title').textContent = titles[type] || 'Request';
                document.getElementById('request-type-input').value = type;
                document.getElementById('modal-early-late').classList.remove('hidden');
            }

            function showToast(message, type = 'success') {
                const colors = {
                    success: 'bg-green-600',
                    error: 'bg-red-600',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-600'
                };
                const icons = {
                    success: '✓',
                    error: '✕',
                    warning: '⚠',
                    info: 'ℹ'
                };
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
                toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
                document.body.appendChild(toast);
                requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
                setTimeout(() => {
                    toast.classList.add('translate-y-4', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }
        </script>
    @endpush
</x-app-layout>
