<x-app-layout>
    <x-slot name="header">Rate Calendar</x-slot>

    @php
        $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        $prevMonth = $month - 1 < 1 ? 12 : $month - 1;
        $prevYear = $month - 1 < 1 ? $year - 1 : $year;
        $nextMonth = $month + 1 > 12 ? 1 : $month + 1;
        $nextYear = $month + 1 > 12 ? $year + 1 : $year;
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    @endphp

    <div class="space-y-6">
        {{-- Header with Navigation --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Rate Calendar</h1>
                <p class="text-sm text-gray-500">View and manage rates across dates</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hotel.rates.calendar', ['month' => $prevMonth, 'year' => $prevYear]) }}"
                    class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span
                    class="px-4 py-2 font-medium text-gray-900 min-w-[160px] text-center">{{ $monthName }}</span>
                <a href="{{ route('hotel.rates.calendar', ['month' => $nextMonth, 'year' => $nextYear]) }}"
                    class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('hotel.rates.calendar') }}"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Today
                </a>
            </div>
        </div>

        {{-- Rate Calendar Grid --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th
                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50 z-10">
                                Room Type</th>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $day);
                                    $isWeekend = $date->isWeekend();
                                    $isToday = $date->isToday();
                                @endphp
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium {{ $isWeekend ? 'bg-blue-50' : '' }} {{ $isToday ? 'bg-green-50 text-green-600' : 'text-gray-500' }}">
                                    <div>{{ $dayNames[$date->dayOfWeek] }}</div>
                                    <div class="text-base font-semibold">{{ $day }}</div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($roomTypes as $roomType)
                            <tr class="hover:bg-gray-50">
                                <td
                                    class="px-3 py-3 font-medium text-gray-900 sticky left-0 bg-white z-10">
                                    {{ $roomType->name }}
                                    <p class="text-xs text-gray-500">Base: Rp
                                        {{ number_format($roomType->base_rate, 0, ',', '.') }}</p>
                                </td>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $date = \Carbon\Carbon::create($year, $month, $day);
                                        $isWeekend = $date->isWeekend();
                                        $isToday = $date->isToday();
                                        $dayData = $calendar[$dateStr]['rates'][$roomType->id] ?? null;
                                        $effectiveRate = $dayData['effective_rate'] ?? $roomType->base_rate;
                                        $hasCustomRate = $effectiveRate != $roomType->base_rate;
                                    @endphp
                                    <td
                                        class="px-1 py-2 text-center {{ $isWeekend ? 'bg-blue-50/50' : '' }} {{ $isToday ? 'bg-green-50/50' : '' }}">
                                        <div
                                            class="px-1 py-1 rounded-lg {{ $hasCustomRate ? 'bg-blue-100 font-medium text-blue-700' : 'text-gray-600' }}">
                                            <span class="text-xs">{{ number_format($effectiveRate / 1000, 0) }}K</span>
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 1 }}"
                                    class="px-4 py-8 text-center text-gray-400">
                                    No room types found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-4 text-xs text-gray-500">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-100"></div>
                <span>Base Rate</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-100"></div>
                <span>Custom Rate</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-4 h-4 rounded bg-blue-50 border border-blue-200">
                </div>
                <span>Weekend</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-4 h-4 rounded bg-green-50 border border-green-200">
                </div>
                <span>Today</span>
            </div>
        </div>

        {{-- Bulk Update Section --}}
        <div x-data="bulkUpdateForm()"
            class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Bulk Rate
                Update</h3>
            <form method="POST" action="{{ route('hotel.rates.bulk-update') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf
                <input type="hidden" name="rates[0][rate_type]" value="standard">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Room Type</label>
                    <select name="rates[0][room_type_id]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($roomTypes as $roomType)
                            <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Start Date</label>
                    <input type="date" name="rates[0][start_date]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">End Date</label>
                    <input type="date" name="rates[0][end_date]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">New Rate
                        (IDR)</label>
                    <input type="number" name="rates[0][amount]" required min="0" step="1000"
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl">
                        Apply Rate
                    </button>
                </div>
            </form>
        </div>

        {{-- Back Link --}}
        <div class="pt-4">
            <a href="{{ route('hotel.rates.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700">
                ← Back to Rate Management
            </a>
        </div>
    </div>

    {{-- Alpine.js Component --}}
    <script>
        window.bulkUpdateForm = function() {
            return {};
        };
    </script>
</x-app-layout>
