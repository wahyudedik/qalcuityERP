<x-app-layout title="Spa Dashboard">
    <x-slot name="header">Spa & Wellness</x-slot>

    <x-slot name="pageTitle">Spa Dashboard</x-slot>

    <x-slot name="pageHeader">
        <a href="{{ route('hotel.spa.bookings.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Booking Baru
        </a>
    </x-slot>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Booking Hari Ini</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['today_bookings'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pendapatan Hari Ini</p>
                    <p class="text-xl font-bold text-gray-900">Rp
                        {{ number_format($stats['today_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Menunggu Konfirmasi</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['pending_bookings'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Terapis Tersedia</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['available_therapists'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Bookings --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Booking Hari Ini</h2>
        </div>
        <div class="overflow-x-auto">
            @if ($todayBookings->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>Tidak ada booking hari ini</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Waktu</th>
                            <th class="px-6 py-3 text-left">Tamu</th>
                            <th class="px-6 py-3 text-left">Treatment/Paket</th>
                            <th class="px-6 py-3 text-left">Terapis</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($todayBookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 whitespace-nowrap text-gray-900">
                                    {{ $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-gray-900">
                                    {{ $booking->guest?->name ?? ($booking->guest_name ?? '-') }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $booking->treatment?->name ?? ($booking->package?->name ?? '-') }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $booking->therapist?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'confirmed' => 'bg-blue-100 text-blue-700',
                                            'in_progress' => 'bg-purple-100 text-purple-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            'no_show' => 'bg-gray-100 text-gray-700',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Upcoming Bookings --}}
    <div class="bg-white rounded-2xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Booking Mendatang</h2>
        </div>
        <div class="overflow-x-auto">
            @if ($upcomingBookings->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <p>Tidak ada booking mendatang</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Waktu</th>
                            <th class="px-6 py-3 text-left">Treatment/Paket</th>
                            <th class="px-6 py-3 text-left">Terapis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($upcomingBookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 whitespace-nowrap text-gray-900">
                                    {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-gray-700">
                                    {{ $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $booking->treatment?->name ?? ($booking->package?->name ?? '-') }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ $booking->therapist?->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
