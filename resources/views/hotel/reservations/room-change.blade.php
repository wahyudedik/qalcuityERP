<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('hotel.reservations.show', $reservation) }}"
                class="text-gray-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            Room Change - {{ $reservation->reservation_number }}
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- Reservation Summary --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Current Reservation</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Guest</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->guest->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Current Room</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        Room {{ $reservation->room?->number ?? 'Not Assigned' }} ({{ $reservation->roomType->name }})
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Stay Period</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M') }} -
                        {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Current Rate</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ number_format($reservation->rate_per_night, 0) }} / night
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('hotel.reservations.process-room-change', $reservation) }}" method="POST"
            class="space-y-6">
            @csrf

            {{-- Select New Room --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Select New Room</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Room Type
                            *</label>
                        <select id="room-type-select" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Choose room type...</option>
                            @foreach ($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" data-rate="{{ $roomType->base_rate }}">
                                    {{ $roomType->name }} (Base: {{ number_format($roomType->base_rate, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Available Room
                            *</label>
                        <select name="to_room_id" id="room-select" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select room type first...</option>
                        </select>
                    </div>

                    <input type="hidden" name="room_type_id" id="room-type-id-input">
                    <input type="hidden" name="rate_difference" id="rate-difference">
                </div>
            </div>

            {{-- Change Details --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Change Details</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Change Type
                            *</label>
                        <select name="change_type" id="change-type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select change type...</option>
                            <option value="upgrade">Upgrade</option>
                            <option value="downgrade">Downgrade</option>
                            <option value="same_category">Same Category</option>
                        </select>
                    </div>

                    <div id="rate-display"
                        class="hidden p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-slate-300">Rate Difference:</span>
                            <span id="rate-diff-amount"
                                class="text-lg font-bold text-blue-600 dark:text-blue-400"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-slate-300">Total Impact
                                ({{ $reservation->nights }} nights):</span>
                            <span id="total-impact" class="text-lg font-bold text-blue-600 dark:text-blue-400"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Reason *</label>
                        <textarea name="reason" required rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Why is this room change needed?"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Additional notes..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hotel.reservations.show', $reservation) }}"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Process Room Change
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const availableRooms = @json($availableRooms);
            const currentRate = {{ $reservation->rate_per_night }};

            document.getElementById('room-type-select').addEventListener('change', function() {
                const roomTypeId = this.value;
                const roomTypeText = this.options[this.selectedIndex]?.text || '';

                document.getElementById('room-type-id-input').value = roomTypeId;

                // Filter rooms by type
                const rooms = availableRooms.filter(r => r.room_type_id == roomTypeId);
                const roomSelect = document.getElementById('room-select');

                roomSelect.innerHTML = '<option value="">Select a room...</option>';

                rooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `Room ${room.number} (${room.floor ? 'Floor ' + room.floor : ''})`;
                    option.dataset.rate = roomTypeText.match(/\(([^)]+)\)/)?.[1] || '';
                    roomSelect.appendChild(option);
                });

                // Auto-detect change type
                detectChangeType();
            });

            document.getElementById('room-select').addEventListener('change', function() {
                calculateRateDifference();
                detectChangeType();
            });

            function detectChangeType() {
                const roomTypeSelect = document.getElementById('room-type-select');
                const currentRoomTypeName = "{{ $reservation->roomType->name }}";
                const selectedRoomTypeName = roomTypeSelect.options[roomTypeSelect.selectedIndex]?.text || '';

                if (!selectedRoomTypeName) return;

                // Simple comparison - you might want more sophisticated logic
                const select = document.getElementById('change-type');
                if (selectedRoomTypeName.includes('Suite') && !currentRoomTypeName.includes('Suite')) {
                    select.value = 'upgrade';
                } else if (selectedRoomTypeName.includes('Deluxe') && !currentRoomTypeName.includes('Deluxe')) {
                    select.value = 'upgrade';
                }
            }

            function calculateRateDifference() {
                const roomTypeSelect = document.getElementById('room-type-select');
                const option = roomTypeSelect.options[roomTypeSelect.selectedIndex];
                const newRate = parseFloat(option?.dataset?.rate || 0);

                const rateDiff = newRate - currentRate;
                const nights = {{ $reservation->nights }};

                document.getElementById('rate-difference').value = rateDiff.toFixed(2);

                const display = document.getElementById('rate-display');
                const diffAmount = document.getElementById('rate-diff-amount');
                const totalImpact = document.getElementById('total-impact');

                if (rateDiff !== 0) {
                    display.classList.remove('hidden');
                    diffAmount.textContent = (rateDiff > 0 ? '+' : '') + numberFormat(rateDiff, 0);
                    totalImpact.textContent = (rateDiff > 0 ? '+' : '') + numberFormat(rateDiff * nights, 0);

                    if (rateDiff > 0) {
                        diffAmount.className = 'text-lg font-bold text-green-600 dark:text-green-400';
                        totalImpact.className = 'text-lg font-bold text-green-600 dark:text-green-400';
                    } else {
                        diffAmount.className = 'text-lg font-bold text-red-600 dark:text-red-400';
                        totalImpact.className = 'text-lg font-bold text-red-600 dark:text-red-400';
                    }
                } else {
                    display.classList.add('hidden');
                }
            }

            function numberFormat(number, decimals) {
                return Number(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }
        </script>
    @endpush
</x-app-layout>
