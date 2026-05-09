<x-app-layout title="Jadwal Terapis">
    <x-slot name="header">Jadwal {{ $therapist->name }}</x-slot>

    <x-slot name="pageTitle">Jadwal {{ $therapist->name }}</x-slot>

    {{-- Date Picker --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-xs font-medium text-gray-600">Tanggal:</label>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Schedule --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Jadwal Kerja</h3>
            </div>
            @if ($schedule->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada jadwal untuk tanggal ini</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($schedule as $slot)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <span class="text-sm text-gray-900">
                                {{ $slot->start_time ? \Carbon\Carbon::parse($slot->start_time)->format('H:i') : '-' }}
                                -
                                {{ $slot->end_time ? \Carbon\Carbon::parse($slot->end_time)->format('H:i') : '-' }}
                            </span>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full {{ $slot->is_available ?? true ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $slot->is_available ?? true ? 'Available' : 'Blocked' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bookings --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Booking</h3>
            </div>
            @if ($bookings->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada booking untuk tanggal ini</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($bookings as $booking)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('H:i') : '-' }}
                                </span>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'in_progress' => 'bg-purple-100 text-purple-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                    ];
                                @endphp
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $booking->guest_name ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('hotel.spa.therapists.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr;
            Kembali ke Daftar Terapis</a>
    </div>
</x-app-layout>
