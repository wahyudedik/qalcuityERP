<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Tour Bookings</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Action Button --}}
            <div class="mb-4 flex justify-end">
                <a href="{{ route('tour-travel.bookings.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + New Booking
                </a>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_bookings'] }}</p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-yellow-200 dark:border-yellow-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
                        {{ $stats['pending_bookings'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-blue-200 dark:border-blue-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Confirmed</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                        {{ $stats['confirmed_bookings'] }}</p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-purple-200 dark:border-purple-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Upcoming Departures</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                        {{ $stats['upcoming_departures'] }}
                    </p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-green-200 dark:border-green-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">Rp
                        {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Bookings Table --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">All Bookings</h2>
                    <div class="flex gap-2">
                        <select
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="paid">Paid</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <input type="date"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="Departure Date">
                    </div>
                </div>

                @if ($bookings->count() === 0)
                    <x-empty-state icon="calendar" title="Belum ada booking"
                        message="Belum ada booking tour travel. Buat booking pertama Anda." actionText="Buat Booking"
                        actionUrl="{{ route('tour-travel.bookings.create') }}" />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Booking #</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Package</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Departure</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Pax</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Total</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Payment</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($bookings as $booking)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('tour-travel.bookings.show', $booking) }}"
                                        class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $booking->booking_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $booking->customer_name }}</p>
                                        @if ($booking->customer_email)
                                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                                {{ $booking->customer_email }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    {{ $booking->tourPackage?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($booking->departure_date)
                                        @if ($booking->departure_date->isPast())
                                            <span
                                                class="text-gray-500 dark:text-slate-400">{{ $booking->departure_date->format('d M Y') }}</span>
                                        @elseif($booking->departure_date->diffInDays(now()) <= 7)
                                            <span
                                                class="text-orange-600 dark:text-orange-400 font-medium">{{ $booking->departure_date->format('d M Y') }}</span>
                                        @else
                                            <span
                                                class="text-gray-700 dark:text-slate-300">{{ $booking->departure_date->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <div class="text-sm">
                                        <p>{{ $booking->total_pax }} pax</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $booking->adults }}A / {{ $booking->children }}C /
                                            {{ $booking->infants }}I
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">Rp
                                            {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                                        @if (!$booking->is_fully_paid)
                                            <p class="text-xs text-red-600 dark:text-red-400">
                                                Due: Rp {{ number_format($booking->balance_due, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $payColor = match ($booking->payment_status) {
                                            'unpaid' => 'red',
                                            'partial' => 'yellow',
                                            'paid' => 'green',
                                            'refunded' => 'gray',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $payColor }}-100 text-{{ $payColor }}-700 dark:bg-{{ $payColor }}-500/20 dark:text-{{ $payColor }}-400">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                    @if ($booking->paid_amount > 0)
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                            Paid: Rp {{ number_format($booking->paid_amount, 0, ',', '.') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColor = match ($booking->status) {
                                            'pending' => 'yellow',
                                            'confirmed' => 'blue',
                                            'paid' => 'green',
                                            'cancelled' => 'red',
                                            'completed' => 'gray',
                                            'refunded' => 'orange',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 dark:bg-{{ $statusColor }}-500/20 dark:text-{{ $statusColor }}-400">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('tour-travel.bookings.show', $booking) }}"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">View</a>

                                        @if ($booking->status === 'pending')
                                            <form action="{{ route('tour-travel.bookings.confirm', $booking) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Confirm</button>
                                            </form>
                                        @endif

                                        @if ($booking->status === 'confirmed' || $booking->status === 'paid')
                                            <form action="{{ route('tour-travel.bookings.complete', $booking) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 dark:text-green-400 hover:underline text-xs">Complete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $bookings->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
