<x-app-layout>
    <x-slot name="header">Rate Management</x-slot>

    @php
        $rateTypeColors = [
            'standard' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'weekend' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400',
            'seasonal' => 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400',
            'promo' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400',
            'dynamic' => 'bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400',
        ];

        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    @endphp

    <div x-data="rateManagement()" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Rate Management</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">Manage room rates and pricing strategies</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hotel.rates.calendar') }}"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Rate Calendar
                </a>
                <button @click="showAddModal = true"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Rate
                </button>
            </div>
        </div>

        {{-- Rates by Room Type --}}
        @foreach ($roomTypes as $roomType)
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                {{-- Room Type Header --}}
                <button @click="toggleRoomType('{{ $roomType->id }}')"
                    class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $roomType->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400">Base Rate: Rp
                                {{ number_format($roomType->base_rate, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transition-transform"
                        :class="{ 'rotate-180': expandedRoomTypes['{{ $roomType->id }}'] }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Rates Table --}}
                <div x-show="expandedRoomTypes['{{ $roomType->id }}']">
                    <div class="overflow-x-auto border-t border-gray-100 dark:border-white/10">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Name</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                    <th class="px-4 py-3 text-left hidden sm:table-cell">Start Date</th>
                                    <th class="px-4 py-3 text-left hidden sm:table-cell">End Date</th>
                                    <th class="px-4 py-3 text-left hidden md:table-cell">Days</th>
                                    <th class="px-4 py-3 text-center">Active</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse($roomType->rates as $rate)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $rate->name ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full {{ $rateTypeColors[$rate->rate_type] ?? '' }}">
                                                {{ ucfirst($rate->rate_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp
                                            {{ number_format($rate->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-slate-400 hidden sm:table-cell">
                                            {{ $rate->start_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-slate-400 hidden sm:table-cell">
                                            {{ $rate->end_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            @if ($rate->day_of_week)
                                                <div class="flex gap-0.5">
                                                    @foreach ([0, 1, 2, 3, 4, 5, 6] as $day)
                                                        <span
                                                            class="w-5 h-5 text-[10px] flex items-center justify-center rounded {{ in_array($day, $rate->day_of_week) ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400' : 'bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-slate-500' }}">
                                                            {{ substr($dayNames[$day], 0, 1) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 dark:text-slate-500">All days</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($rate->is_active)
                                                <span
                                                    class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">Active</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button @click="openEditModal({{ $rate->id }})"
                                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('hotel.rates.destroy', $rate) }}"
                                                    onsubmit="return confirm('Delete this rate?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-8 text-center text-gray-400 dark:text-slate-500">
                                            No rates configured for this room type.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        @if ($roomTypes->isEmpty())
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-slate-600 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-gray-500 dark:text-slate-400 mb-4">No room types found.</p>
                <a href="{{ route('hotel.room-types.index') }}"
                    class="text-blue-600 dark:text-blue-400 hover:underline">Create room types first</a>
            </div>
        @endif

        {{-- Add Rate Modal --}}
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showAddModal = false">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto"
                @click.stop>
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Add Rate</h3>
                    <button @click="showAddModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
                </div>
                <form method="POST" action="{{ route('hotel.rates.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Room Type
                            *</label>
                        <select name="room_type_id" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Name</label>
                        <input type="text" name="name" placeholder="e.g., High Season Rate"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Rate Type
                                *</label>
                            <select name="rate_type" required
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($rateTypes as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Amount
                                (IDR)
                                *</label>
                            <input type="number" name="amount" required min="0" step="1000"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Start
                                Date</label>
                            <input type="date" name="start_date"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">End
                                Date</label>
                            <input type="date" name="end_date"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Days of
                            Week</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([0, 1, 2, 3, 4, 5, 6] as $i => $day)
                                <label
                                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                                    <input type="checkbox" name="day_of_week[]" value="{{ $day }}"
                                        class="rounded text-blue-600">
                                    <span
                                        class="text-xs text-gray-600 dark:text-slate-400">{{ $dayNames[$day] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Min Stay
                                (nights)</label>
                            <input type="number" name="min_stay" min="1"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Max Stay
                                (nights)</label>
                            <input type="number" name="max_stay" min="1"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="rounded text-blue-600">
                        <label for="is_active" class="text-sm text-gray-700 dark:text-slate-300">Active</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Add
                            Rate</button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- end x-data rateManagement --}}

    {{-- Alpine.js Component - Must be before closing x-app-layout --}}
    <script>
        // Define rateManagement component for Alpine.js
        window.rateManagement = function() {
            return {
                showAddModal: false,
                expandedRoomTypes: {},

                toggleRoomType(id) {
                    this.expandedRoomTypes[id] = !this.expandedRoomTypes[id];
                },

                openEditModal(rateId) {
                    // For now, redirect to edit (or implement inline editing modal)
                    alert('Edit rate ID: ' + rateId + ' (Implement modal or inline edit)');
                },
            }
        };
    </script>
</x-app-layout>
