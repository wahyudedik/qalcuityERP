<x-app-layout title="Spa Bookings">
    <x-slot name="header">Spa Bookings</x-slot>

    <x-slot name="pageTitle">Daftar Booking Spa</x-slot>

    <x-slot name="pageHeader">
        <a href="{{ route('hotel.spa.bookings.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Booking Baru
        </a>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed
                    </option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                    </option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Terapis</label>
                <select name="therapist_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Terapis</option>
                    @foreach ($therapists as $therapist)
                        <option value="{{ $therapist->id }}"
                            {{ request('therapist_id') == $therapist->id ? 'selected' : '' }}>
                            {{ $therapist->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                @if (request()->hasAny(['status', 'date', 'therapist_id']))
                    <a href="{{ route('hotel.spa.bookings.index') }}"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bookings Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if ($bookings->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>Tidak ada booking ditemukan</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Waktu</th>
                            <th class="px-6 py-3 text-left">Tamu</th>
                            <th class="px-6 py-3 text-left">Treatment/Paket</th>
                            <th class="px-6 py-3 text-left">Terapis</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 whitespace-nowrap text-gray-900">
                                    {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-gray-700">
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
                                <td class="px-6 py-3 text-right text-gray-900 whitespace-nowrap">
                                    {{ $booking->total_amount ? 'Rp ' . number_format($booking->total_amount, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($bookings->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
