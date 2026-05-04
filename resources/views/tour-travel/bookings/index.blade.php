<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900">Tour Bookings</h1>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_bookings'] }}</p>
                </div>
                <div
                    class="bg-white rounded-xl border border-yellow-200 p-4">
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">
                        {{ $stats['pending_bookings'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-200 p-4">
                    <p class="text-xs text-gray-500">Confirmed</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        {{ $stats['confirmed_bookings'] }}</p>
                </div>
                <div
                    class="bg-white rounded-xl border border-purple-200 p-4">
                    <p class="text-xs text-gray-500">Upcoming Departures</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">
                        {{ $stats['upcoming_departures'] }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-green-200 p-4">
                    <p class="text-xs text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">Rp
                        {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Bookings Table --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">All Bookings</h2>
                    <div class="flex gap-2">
                        <select
                            class="text-sm border border-gray-300 rounded px-3 py-1.5 bg-white text-gray-900">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="paid">Paid</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <input type="date"
                            class="text-sm border border-gray-300 rounded px-3 py-1.5 bg-white text-gray-900"
                            placeholder="Tanggal Keberangkatan">
                    </div>
                </div>

                @if ($bookings->count() === 0)
                    <x-empty-state icon="calendar" title="Belum ada booking"
                        message="Belum ada booking tour travel. Buat booking pertama Anda." actionText="Buat Booking"
                        actionUrl="{{ route('tour-travel.bookings.create') }}" />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Booking #</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Customer</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Package</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Departure</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Pax</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Payment</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($bookings as $booking)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tour-travel.bookings.show', $booking) }}"
                                                class="font-medium text-indigo-600 hover:underline">
                                                {{ $booking->booking_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-900">
                                                    {{ $booking->customer_name }}</p>
                                                @if ($booking->customer_email)
                                                    <p class="text-xs text-gray-500">
                                                        {{ $booking->customer_email }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $booking->tourPackage?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($booking->departure_date)
                                                @if ($booking->departure_date->isPast())
                                                    <span
                                                        class="text-gray-500">{{ $booking->departure_date->format('d M Y') }}</span>
                                                @elseif($booking->departure_date->diffInDays(now()) <= 7)
                                                    <span
                                                        class="text-orange-600 font-medium">{{ $booking->departure_date->format('d M Y') }}</span>
                                                @else
                                                    <span
                                                        class="text-gray-700">{{ $booking->departure_date->format('d M Y') }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <div class="text-sm">
                                                <p>{{ $booking->total_pax }} pax</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $booking->adults }}A / {{ $booking->children }}C /
                                                    {{ $booking->infants }}I
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-900">Rp
                                                    {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                                                @if (!$booking->is_fully_paid)
                                                    <p class="text-xs text-red-600">
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
                                                class="px-2 py-1 text-xs rounded-full bg-{{ $payColor  }}-100 text-{{ $payColor }}-700 $payColor }}-500/20 $payColor }}-400">
                                                {{ ucfirst($booking->payment_status) }}
                                            </span>
                                            @if ($booking->paid_amount > 0)
                                                <p class="text-xs text-gray-500 mt-1">
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
                                                class="px-2 py-1 text-xs rounded-full bg-{{ $statusColor  }}-100 text-{{ $statusColor }}-700 $statusColor }}-500/20 $statusColor }}-400">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="{{ route('tour-travel.bookings.show', $booking) }}"
                                                    class="text-indigo-600 hover:underline text-xs">View</a>

                                                @if ($booking->status === 'pending')
                                                    <form
                                                        action="{{ route('tour-travel.bookings.confirm', $booking) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-blue-600 hover:underline text-xs">Confirm</button>
                                                    </form>
                                                @endif

                                                @if ($booking->status === 'confirmed' || $booking->status === 'paid')
                                                    <form
                                                        action="{{ route('tour-travel.bookings.complete', $booking) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-green-600 hover:underline text-xs">Complete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
