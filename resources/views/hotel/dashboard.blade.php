<x-app-layout>
    <x-slot name="header">Hotel Dashboard</x-slot>

    {{-- KPI Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        {{-- Occupancy Rate --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Occupancy Rate</p>
            <div class="flex items-center gap-3 mt-2">
                <div class="relative w-12 h-12">
                    <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-200" stroke="currentColor" stroke-width="3"
                            fill="none"
                            d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path
                            class="{{ $occupancyRate >= 80 ? 'text-green-500' : ($occupancyRate >= 50 ? 'text-blue-500' : 'text-amber-500') }}"
                            stroke="currentColor" stroke-width="3" fill="none"
                            stroke-dasharray="{{ $occupancyRate }}, 100"
                            d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span
                        class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-900">
                        {{ $occupancyRate }}%
                    </span>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900">{{ $occupiedRooms }}/{{ $totalRooms }}
                    </p>
                    <p class="text-xs text-gray-500">rooms occupied</p>
                </div>
            </div>
        </div>

        {{-- Today's Arrivals --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Today's Arrivals</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $expectedArrivals->count() }}</p>
            <a href="{{ route('hotel.reservations.index', ['status' => 'confirmed', 'date' => today()->toDateString()]) }}"
                class="text-xs text-blue-500 hover:underline">View reservations</a>
        </div>

        {{-- Today's Departures --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Today's Departures</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $expectedDepartures->count() }}</p>
            <span class="text-xs text-gray-500">check-outs expected</span>
        </div>

        {{-- Month Revenue --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Revenue (This Month)</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
                Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
            </p>
            <span class="text-xs text-gray-500">{{ now()->format('F Y') }}</span>
        </div>
    </div>

    {{-- Second Row: Room Status & Housekeeping --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Room Status Overview --}}
        <div
            class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Room Status Overview</h3>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                @php
                    $statusConfig = [
                        'available' => [
                            'label' => 'Available',
                            'color' => 'bg-green-500',
                            'text' => 'text-green-600',
                        ],
                        'occupied' => [
                            'label' => 'Occupied',
                            'color' => 'bg-red-500',
                            'text' => 'text-red-600',
                        ],
                        'cleaning' => [
                            'label' => 'Cleaning',
                            'color' => 'bg-yellow-500',
                            'text' => 'text-yellow-600',
                        ],
                        'maintenance' => [
                            'label' => 'Maintenance',
                            'color' => 'bg-orange-500',
                            'text' => 'text-orange-600',
                        ],
                        'out_of_order' => [
                            'label' => 'Blocked',
                            'color' => 'bg-gray-500',
                            'text' => 'text-gray-600',
                        ],
                    ];
                @endphp
                @foreach ($statusConfig as $status => $config)
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 rounded-xl {{ $config['color'] }} bg-opacity-20 mb-2">
                            <span class="text-xl font-bold {{ $config['text'] }}">
                                {{ $roomStatusSummary[$status] ?? 0 }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">{{ $config['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pending Housekeeping --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Housekeeping Tasks</h3>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/20 flex items-center justify-center">
                    <span
                        class="text-2xl font-bold text-amber-600">{{ $pendingHousekeeping }}</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending tasks</p>
                    <a href="{{ route('hotel.housekeeping.room-board') }}"
                        class="text-sm text-blue-500 hover:underline">View board</a>
                </div>
            </div>
            @if ($expectedDepartures->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Rooms to clean after checkout:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($expectedDepartures->take(5) as $dep)
                            @if ($dep->room)
                                <span
                                    class="px-2 py-0.5 text-xs bg-gray-100 rounded-full text-gray-700">
                                    {{ $dep->room?->number }}
                                </span>
                            @endif
                        @endforeach
                        @if ($expectedDepartures->count() > 5)
                            <span class="px-2 py-0.5 text-xs text-gray-500">+{{ $expectedDepartures->count() - 5 }}
                                more</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('hotel.reservations.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Reservation
        </a>
        <a href="{{ route('hotel.rooms.availability') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Room Availability
        </a>
        <a href="{{ route('hotel.housekeeping.room-board') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m4-4h1m-1 4h1" />
            </svg>
            Housekeeping Board
        </a>
    </div>

    {{-- Recent Reservations Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Recent Reservations</h3>
            <a href="{{ route('hotel.reservations.index') }}" class="text-sm text-blue-500 hover:underline">View
                all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Reservation #</th>
                        <th class="px-4 py-3 text-left">Guest</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Room Type</th>
                        <th class="px-4 py-3 text-left">Check-in</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Check-out</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentReservations as $res)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('hotel.reservations.show', $res) }}"
                                    class="font-medium text-blue-600 hover:underline">
                                    {{ $res->reservation_number ?? '#' . $res->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $res->guest?->name ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $res->guest?->phone ?? '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600">
                                {{ $res->roomType?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ \Carbon\Carbon::parse($res->check_in_date)->format('d M') }}
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-600">
                                {{ \Carbon\Carbon::parse($res->check_out_date)->format('d M') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-600',
                                        'confirmed' =>
                                            'bg-blue-100 text-blue-700',
                                        'checked_in' =>
                                            'bg-green-100 text-green-700',
                                        'checked_out' =>
                                            'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'checked_in' => 'Checked In',
                                        'checked_out' => 'Checked Out',
                                        'cancelled' => 'Cancelled',
                                    ];
                                @endphp
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$res->status] ?? $statusColors['pending'] }}">
                                    {{ $statusLabels[$res->status] ?? $res->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-500 capitalize">
                                {{ $res->source ?? 'Direct' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                No reservations yet. <a href="{{ route('hotel.reservations.create') }}"
                                    class="text-blue-500 hover:underline">Create the first one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            // Toast notification for flash messages
            function showToast(message, type = 'success') {
                const colors = {
                    success: 'bg-green-600',
                    error: 'bg-red-600',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-600',
                };
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
                toast.innerHTML = `<span>${message}</span>`;
                document.body.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-4', 'opacity-0');
                });
                setTimeout(() => {
                    toast.classList.add('translate-y-4', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }

            @if (session('success'))
                showToast(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                showToast(@json(session('error')), 'error');
            @endif
        </script>
    @endpush
</x-app-layout>
