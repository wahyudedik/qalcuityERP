<x-app-layout>
    <x-slot name="header">Room Status Map</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('hotel.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
    </div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Status Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600">Available</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['available'] }}</p>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-sm text-gray-600">Occupied</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['occupied'] }}</p>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <span class="text-sm text-gray-600">Dirty</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['dirty'] }}</p>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm text-gray-600">Cleaning</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['cleaning'] }}</p>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                        <span class="text-sm text-gray-600">Clean</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['clean'] }}</p>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-gray-800"></div>
                        <span class="text-sm text-gray-600">OOO</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['out_of_order'] }}</p>
                </div>
            </div>

            {{-- Filter Controls --}}
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Filter by Floor
                        </label>
                        <select id="floorFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Floors</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>
                                    Floor {{ $floor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Filter by Status
                        </label>
                        <select id="statusFilter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                                Available</option>
                            <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied
                            </option>
                            <option value="dirty" {{ request('status') == 'dirty' ? 'selected' : '' }}>Dirty</option>
                            <option value="cleaning" {{ request('status') == 'cleaning' ? 'selected' : '' }}>Cleaning
                            </option>
                            <option value="clean" {{ request('status') == 'clean' ? 'selected' : '' }}>Clean</option>
                            <option value="out_of_order" {{ request('status') == 'out_of_order' ? 'selected' : '' }}>
                                Out of Order</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button id="resetFilters"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- Room Grid by Floor --}}
            @php
                $groupedRooms = $rooms->groupBy('floor');
            @endphp

            @foreach ($groupedRooms as $floor => $floorRooms)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
                    data-floor="{{ $floor }}">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Floor {{ $floor }}
                        </h3>
                        <span class="text-sm text-gray-500">{{ $floorRooms->count() }} rooms</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                        @foreach ($floorRooms as $room)
                            @php
                                $statusColors = [
                                    'available' =>
                                        'bg-green-50 border-green-500 text-green-700',
                                    'occupied' =>
                                        'bg-red-50 border-red-500 text-red-700',
                                    'dirty' =>
                                        'bg-yellow-50 border-yellow-500 text-yellow-700',
                                    'cleaning' =>
                                        'bg-blue-50 border-blue-500 text-blue-700',
                                    'clean' =>
                                        'bg-gray-50 border-gray-400 text-gray-700',
                                    'out_of_order' =>
                                        'bg-gray-100 border-gray-600 text-gray-800 opacity-60',
                                ];
                                $colorClass = $statusColors[$room->status] ?? 'bg-gray-50 border-gray-300';
                            @endphp

                            <div class="relative group cursor-pointer" data-status="{{ $room->status }}"
                                data-room-id="{{ $room->id }}">
                                <div
                                    class="p-4 rounded-lg border-2 {{ $colorClass }} transition-all hover:shadow-md hover:scale-105">
                                    <div class="text-center">
                                        <p class="text-lg font-bold mb-1">{{ $room->number }}</p>
                                        <p class="text-xs opacity-75 mb-2">{{ $room->roomType->name }}</p>

                                        @if ($room->status === 'occupied' && $room->currentReservation)
                                            <div class="mt-2 pt-2 border-t border-current border-opacity-30">
                                                <p class="text-xs truncate">
                                                    {{ $room->currentReservation->guest->name ?? 'Guest' }}
                                                </p>
                                            </div>
                                        @endif

                                        @if ($room->status === 'available')
                                            <div class="mt-2 pt-2 border-t border-current border-opacity-30">
                                                <p class="text-xs font-medium">Available</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Tooltip --}}
                                <div
                                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg">
                                    <p class="font-semibold">Room {{ $room->number }}</p>
                                    <p>{{ $room->roomType->name }}</p>
                                    <p class="capitalize">{{ ucfirst(str_replace('_', ' ', $room->status)) }}</p>
                                    @if ($room->status === 'occupied' && $room->currentReservation)
                                        <p class="mt-1 pt-1 border-t border-gray-600">
                                            {{ $room->currentReservation->guest->name ?? 'N/A' }}
                                        </p>
                                    @endif
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                                        <div class="w-2 h-2 bg-gray-900 rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Legend --}}
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Legend</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-green-500"></div>
                        <span class="text-sm text-gray-700">Available</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-red-500"></div>
                        <span class="text-sm text-gray-700">Occupied</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-yellow-500"></div>
                        <span class="text-sm text-gray-700">Dirty</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-blue-500"></div>
                        <span class="text-sm text-gray-700">Cleaning</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-400"></div>
                        <span class="text-sm text-gray-700">Clean</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-800"></div>
                        <span class="text-sm text-gray-700">Out of Order</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const floorFilter = document.getElementById('floorFilter');
                const statusFilter = document.getElementById('statusFilter');
                const resetBtn = document.getElementById('resetFilters');

                function applyFilters() {
                    const floor = floorFilter.value;
                    const status = statusFilter.value;

                    // Update URL
                    const params = new URLSearchParams();
                    if (floor) params.set('floor', floor);
                    if (status) params.set('status', status);

                    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', url);

                    // Show/hide floors
                    document.querySelectorAll('[data-floor]').forEach(floorEl => {
                        if (floor && floorEl.dataset.floor !== floor) {
                            floorEl.style.display = 'none';
                        } else {
                            floorEl.style.display = 'block';
                        }
                    });

                    // Show/hide rooms
                    document.querySelectorAll('[data-room-id]').forEach(roomEl => {
                        const roomStatus = roomEl.dataset.status;
                        if (status && roomStatus !== status) {
                            roomEl.style.display = 'none';
                        } else {
                            roomEl.style.display = 'block';
                        }
                    });
                }

                floorFilter.addEventListener('change', applyFilters);
                statusFilter.addEventListener('change', applyFilters);

                resetBtn.addEventListener('click', function() {
                    floorFilter.value = '';
                    statusFilter.value = '';
                    window.history.pushState({}, '', window.location.pathname);

                    document.querySelectorAll('[data-floor]').forEach(el => el.style.display = 'block');
                    document.querySelectorAll('[data-room-id]').forEach(el => el.style.display = 'block');
                });
            });
        </script>
    @endpush
</x-app-layout>
