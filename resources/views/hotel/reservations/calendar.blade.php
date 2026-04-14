<x-app-layout>
    <x-slot name="header">Reservation Calendar</x-slot>

    <x-slot name="pageHeader">
        <a href="{{ route('hotel.reservations.index') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white text-sm font-medium hover:bg-gray-200 dark:hover:bg-white/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            List View
        </a>
        <a href="{{ route('hotel.reservations.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Reservation
        </a>
    </x-slot>

    @php
        $currentMonth = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $daysInMonth = $currentMonth->daysInMonth;
        $days = collect(range(1, $daysInMonth));
    @endphp

    {{-- Month Navigation --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('hotel.reservations.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $currentMonth->format('F Y') }}
            </h2>
            <a href="{{ route('hotel.reservations.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Confirmed</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-blue-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Checked In</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-yellow-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Pending</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-gray-400"></span>
                <span class="text-gray-600 dark:text-slate-400">Checked Out</span>
            </div>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        {{-- Day Headers --}}
        <div
            class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
            <div
                class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase sticky left-0 bg-gray-50 dark:bg-white/5">
                Room</div>
            @foreach ($days as $day)
                @php $date = $currentMonth->copy()->day($day); @endphp
                <div
                    class="px-1 py-3 text-center text-xs {{ $date->isWeekend() ? 'text-red-400' : 'text-gray-500 dark:text-slate-400' }} font-medium">
                    <div>{{ $date->format('D') }}</div>
                    <div class="text-sm {{ $date->isToday() ? 'text-blue-600 font-bold' : '' }}">{{ $day }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Room Rows --}}
        <div x-data="{ expandedTypes: {} }" class="divide-y divide-gray-100 dark:divide-white/5">
            @forelse($roomTypes as $roomType)
                {{-- Room Type Header --}}
                <div @click="expandedTypes['{{ $roomType->id }}'] = !expandedTypes['{{ $roomType->id }}']"
                    class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] bg-gray-50 dark:bg-white/5 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10">
                    <div class="px-4 py-3 flex items-center gap-2 sticky left-0 bg-gray-50 dark:bg-white/5">
                        <svg class="w-4 h-4 text-gray-400 transition-transform"
                            :class="{ 'rotate-90': expandedTypes['{{ $roomType->id }}'] }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $roomType->name }}</span>
                        <span class="text-xs text-gray-400">({{ $roomType->rooms->count() }} rooms)</span>
                    </div>
                    @foreach ($days as $day)
                        <div class="px-1 py-3"></div>
                    @endforeach
                </div>

                {{-- Rooms under this type --}}
                <template x-if="expandedTypes['{{ $roomType->id }}']">
                    <div class="divide-y divide-gray-50 dark:divide-white/5">
                        @php $currentRoomType = $roomType; @endphp
                        @foreach ($roomType->rooms as $room)
                            @php
                                $roomTypeId = $currentRoomType->id;
                                $roomReservations = $reservations->filter(function ($r) use ($room, $roomTypeId) {
                                    return $r->room_id == $room->id ||
                                        ($r->room_type_id == $roomTypeId && !$r->room_id);
                                });
                            @endphp
                            <div
                                class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] hover:bg-gray-50 dark:hover:bg-white/5">
                                <div class="px-4 py-2 flex items-center gap-2 sticky left-0 bg-white dark:bg-[#1e293b]">
                                    <span class="text-sm text-gray-700 dark:text-slate-300">{{ $room->number }}</span>
                                    @if ($room->floor)
                                        <span class="text-xs text-gray-400">Floor {{ $room->floor }}</span>
                                    @endif
                                </div>
                                @foreach ($days as $day)
                                    @php
                                        $currentDate = $currentMonth->copy()->day($day);
                                        $dayReservations = $roomReservations->filter(function ($r) use ($currentDate) {
                                            $checkIn = \Carbon\Carbon::parse($r->check_in_date);
                                            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
                                            return $checkIn->lte($currentDate) && $checkOut->gt($currentDate);
                                        });
                                    @endphp
                                    <div class="px-0.5 py-1 relative min-h-[36px]">
                                        @foreach ($dayReservations as $rsv)
                                        @break
                                    @endforeach
                                    @if (isset($rsv))
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($rsv->check_in_date);
                                            $checkOut = \Carbon\Carbon::parse($rsv->check_out_date);
                                            $isStart = $checkIn->eq($currentDate);
                                            $isEnd = $checkOut->eq($currentDate->copy()->addDay());
                                            $statusColor = match ($rsv->status) {
                                                'confirmed' => 'bg-green-500',
                                                'checked_in' => 'bg-blue-500',
                                                'pending' => 'bg-yellow-500',
                                                'checked_out' => 'bg-gray-400',
                                                'cancelled' => 'bg-red-400',
                                                default => 'bg-gray-300',
                                            };
                                        @endphp
                                        @if ($isStart)
                                            <a href="{{ route('hotel.reservations.show', $rsv) }}"
                                                class="absolute left-0 right-0 top-1 h-6 {{ $statusColor }} rounded text-white text-xs flex items-center px-1 truncate hover:opacity-80 transition z-10"
                                                title="{{ $rsv->guest?->name }} ({{ $rsv->check_in_date->format('d M') }} - {{ $rsv->check_out_date->format('d M') }})">
                                                <span class="truncate">{{ $rsv->guest?->name }}</span>
                                            </a>
                                        @endif
                                    @endif
                                    {{-- Reset for next iteration --}}
                                    @php unset($rsv); @endphp
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </template>

            {{-- Fallback when not expanded: show compact view --}}
            <div x-show="!expandedTypes['{{ $roomType->id }}']"
                class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] h-10">
                <div class="px-4 py-2 sticky left-0 bg-white dark:bg-[#1e293b] flex items-center">
                    <span class="text-xs text-gray-400">Click to expand rooms</span>
                </div>
                @foreach ($days as $day)
                    @php
                        $currentDate = $currentMonth->copy()->day($day);
                        $typeReservations = $reservations->filter(function ($r) use ($roomType, $currentDate) {
                            if ($r->room_type_id != $roomType->id) {
                                return false;
                            }
                            $checkIn = \Carbon\Carbon::parse($r->check_in_date);
                            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
                            return $checkIn->lte($currentDate) && $checkOut->gt($currentDate);
                        });
                        $occupiedCount = $typeReservations->count();
                        $occupancyPercent =
                            $roomType->rooms->count() > 0
                                ? round(($occupiedCount / $roomType->rooms->count()) * 100)
                                : 0;
                    @endphp
                    <div class="px-0.5 py-1 relative flex items-center justify-center">
                        @if ($occupiedCount > 0)
                            <div
                                class="w-full h-6 rounded {{ $occupancyPercent >= 80 ? 'bg-red-500' : ($occupancyPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500') }} opacity-70 text-white text-xs flex items-center justify-center font-medium">
                                {{ $occupiedCount }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                No room types configured. <a href="{{ route('hotel.room-types.index') }}"
                    class="text-blue-500 hover:underline">Configure room types</a>
            </div>
        @endforelse
    </div>
</div>

{{-- Quick Info Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Reservations This Month</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $reservations->count() }}</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Currently Checked In</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            {{ $reservations->where('status', 'checked_in')->count() }}</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Arrivals Today</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
            {{ $reservations->filter(fn($r) => \Carbon\Carbon::parse($r->check_in_date)->isToday())->count() }}
        </p>
    </div>
</div>
</x-app-layout>
