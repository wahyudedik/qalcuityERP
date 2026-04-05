<x-app-layout>
    <x-slot name="header">Room Management</x-slot>

    <div x-data="roomManager()" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Rooms</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Manage hotel rooms and their status</p>
            </div>
            <button @click="openAddModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Room
            </button>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search room number..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <select name="type"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">All Types</option>
                        @foreach ($roomTypes as $rt)
                            <option value="{{ $rt->id }}" @selected(request('type') == $rt->id)>{{ $rt->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="floor"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">All Floors</option>
                        @foreach ($floors as $f)
                            <option value="{{ $f }}" @selected(request('floor') == $f)>{{ $f }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">All Status</option>
                        @foreach (['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
                    <a href="{{ route('hotel.rooms.index') }}"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 text-center">Reset</a>
                </form>
            </div>
        </div>

        {{-- Room Cards Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @forelse ($rooms as $room)
                @php
                    $statusConfig = [
                        'available' => [
                            'bg' => 'bg-green-500',
                            'border' => 'border-green-500',
                            'text' => 'text-green-600 dark:text-green-400',
                        ],
                        'occupied' => [
                            'bg' => 'bg-red-500',
                            'border' => 'border-red-500',
                            'text' => 'text-red-600 dark:text-red-400',
                        ],
                        'cleaning' => [
                            'bg' => 'bg-yellow-500',
                            'border' => 'border-yellow-500',
                            'text' => 'text-yellow-600 dark:text-yellow-400',
                        ],
                        'maintenance' => [
                            'bg' => 'bg-orange-500',
                            'border' => 'border-orange-500',
                            'text' => 'text-orange-600 dark:text-orange-400',
                        ],
                        'out_of_order' => [
                            'bg' => 'bg-gray-500',
                            'border' => 'border-gray-500',
                            'text' => 'text-gray-600 dark:text-gray-400',
                        ],
                    ];
                    $cfg = $statusConfig[$room->status] ?? $statusConfig['available'];
                @endphp
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden hover:shadow-lg transition-shadow">
                    {{-- Status indicator bar --}}
                    <div class="{{ $cfg['bg'] }} h-1.5"></div>

                    <div class="p-4">
                        {{-- Room Number --}}
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $room->number }}</h3>
                            <span class="w-2.5 h-2.5 rounded-full {{ $cfg['bg'] }}"
                                title="{{ ucfirst($room->status) }}"></span>
                        </div>

                        {{-- Room Info --}}
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">
                            {{ $room->roomType?->name ?? 'No Type' }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">
                            @if ($room->floor)
                                Floor {{ $room->floor }}
                            @endif
                            @if ($room->building)
                                · {{ $room->building }}
                            @endif
                        </p>

                        {{-- Status Badge --}}
                        <div class="mt-3">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs {{ $cfg['text'] }} bg-opacity-10 bg-current">
                                {{ ucfirst(str_replace('_', ' ', $room->status)) }}
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-3 flex items-center gap-1">
                            {{-- Quick Status Change --}}
                            <select @change="changeStatus({{ $room->id }}, $el.value)"
                                class="flex-1 text-xs px-2 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-700 dark:text-slate-300 cursor-pointer">
                                @foreach (['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'] as $s)
                                    <option value="{{ $s }}" @selected($room->status === $s)>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>

                            {{-- Edit Button --}}
                            <button @click="openEditModal({{ $room->id }})"
                                class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            {{-- Delete Button --}}
                            <form method="POST" action="{{ route('hotel.rooms.destroy', $room) }}" class="inline"
                                onsubmit="return confirm('Delete this room?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10"
                                    title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div
                        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-slate-600 mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m4-4h1m-1 4h1" />
                        </svg>
                        <p class="text-gray-500 dark:text-slate-400">No rooms found.</p>
                        <button @click="openAddModal" class="mt-4 text-blue-500 hover:underline">Add your first
                            room</button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($rooms->hasPages())
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 px-4 py-3">
                {{ $rooms->links() }}
            </div>
        @endif

        {{-- Add/Edit Room Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showModal = false"
                class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white"
                        x-text="isEdit ? 'Edit Room' : 'Add Room'"></h3>
                    <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="isEdit ? '{{ url('hotel/rooms') }}/' + form.id : '{{ route('hotel.rooms.store') }}'"
                    method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Room Number
                                *</label>
                            <input type="text" name="number" x-model="form.number" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Room Type
                                *</label>
                            <select name="room_type_id" x-model="form.room_type_id" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                <option value="">Select type...</option>
                                @foreach ($roomTypes as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Floor</label>
                            <input type="text" name="floor" x-model="form.floor"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Building</label>
                            <input type="text" name="building" x-model="form.building"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status
                                *</label>
                            <select name="status" x-model="form.status" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                @foreach (['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'] as $s)
                                    <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Notes</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></textarea>
                        </div>

                        @if (isset($editRoom) && $editRoom)
                            <div class="col-span-2 flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    x-model="form.is_active" class="rounded border-gray-300 dark:border-white/20">
                                <label for="is_active" class="text-sm text-gray-700 dark:text-slate-300">Room is
                                    active</label>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                            <span x-text="isEdit ? 'Update' : 'Create'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component --}}
    <script>
        window.roomManager = function() {
            return {
                showModal: false,
                isEdit: false,
                form: {
                    id: '',
                    number: '',
                    room_type_id: '',
                    floor: '',
                    building: '',
                    status: 'available',
                    description: '',
                    is_active: true,
                },
                rooms: @json($rooms->items()),

                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        id: '',
                        number: '',
                        room_type_id: '',
                        floor: '',
                        building: '',
                        status: 'available',
                        description: '',
                        is_active: true,
                    };
                    this.showModal = true;
                },

                openEditModal(roomId) {
                    const room = this.rooms.find(r => r.id === roomId);
                    if (!room) return;

                    this.isEdit = true;
                    this.form = {
                        id: room.id,
                        number: room.number,
                        room_type_id: String(room.room_type_id),
                        floor: room.floor || '',
                        building: room.building || '',
                        status: room.status,
                        description: room.description || '',
                        is_active: room.is_active,
                    };
                    this.showModal = true;
                },

                changeStatus(roomId, newStatus) {
                    fetch(`{{ url('hotel/rooms') }}/${roomId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                status: newStatus
                            }),
                        })
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showToast(data.message || 'Room status updated', 'success');
                                setTimeout(() => location.reload(), 500);
                            } else {
                                showToast(data.message || 'Failed to update status', 'error');
                            }
                        })
                        .catch(err => showToast('Failed to update status: ' + err.message, 'error'));
                }
            }
        };

        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                warning: 'bg-yellow-500',
                info: 'bg-blue-600',
            };
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            const toast = document.createElement('div');
            toast.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
            toast.innerHTML = `<span class="text-base">${icons[type] || icons.success}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
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
        @if ($errors->any())
            showToast(@json($errors->first()), 'error');
        @endif
    </script>
</x-app-layout>
