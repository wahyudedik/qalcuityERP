<x-app-layout>
    <x-slot name="header">Room Types</x-slot>

    <div x-data="roomTypeManager()" class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Room Types</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Define room categories and pricing</p>
            </div>
            <button @click="openAddModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Room Type
            </button>
        </div>

        {{-- Room Types Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Room Type</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Code</th>
                            <th class="px-4 py-3 text-right">Base Rate</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Occupancy</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Amenities</th>
                            <th class="px-4 py-3 text-center">Rooms</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($roomTypes as $rt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $rt->name }}</p>
                                    @if ($rt->description)
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                            {{ Str::limit($rt->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span
                                        class="px-2 py-0.5 text-xs font-mono bg-gray-100 dark:bg-white/10 rounded text-gray-600 dark:text-slate-400">
                                        {{ $rt->code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($rt->base_rate, 0, ',', '.') }}
                                </td>
                                <td
                                    class="px-4 py-3 text-center hidden md:table-cell text-gray-600 dark:text-slate-300">
                                    <span class="text-sm">{{ $rt->base_occupancy ?? 1 }}</span>
                                    @if ($rt->max_occupancy && $rt->max_occupancy > $rt->base_occupancy)
                                        <span class="text-gray-400 dark:text-slate-500">-
                                            {{ $rt->max_occupancy }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400 dark:text-slate-500 ml-1">guests</span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($rt->amenities ?? [] as $amenity)
                                            <span
                                                class="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded-full">
                                                {{ $amenity }}
                                            </span>
                                        @endforeach
                                        @if (empty($rt->amenities) || count($rt->amenities) === 0)
                                            <span class="text-xs text-gray-400 dark:text-slate-500">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 text-sm font-medium text-gray-700 dark:text-slate-300">
                                        {{ $rt->rooms_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs {{ $rt->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400' }}">
                                        {{ $rt->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openEditModal({{ $rt->id }})"
                                            class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('hotel.room-types.destroy', $rt) }}"
                                            class="inline" onsubmit="return confirm('Delete this room type?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10"
                                                title="Delete">
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
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                    No room types defined yet. <button @click="openAddModal"
                                        class="text-blue-500 hover:underline">Create the first one</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add/Edit Room Type Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showModal = false"
                class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal Header --}}
                <div
                    class="sticky top-0 bg-white dark:bg-[#1e293b] flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white"
                        x-text="isEdit ? 'Edit Room Type' : 'Add Room Type'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form
                    :action="isEdit ? '{{ url('hotel/room-types') }}/' + form.id : '{{ route('hotel.room-types.store') }}'"
                    method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Name
                                *</label>
                            <input type="text" name="name" x-model="form.name" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g. Deluxe Suite">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Code
                                *</label>
                            <input type="text" name="code" x-model="form.code" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g. DLX">
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Description</label>
                            <textarea name="description" x-model="form.description" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                                placeholder="Brief description..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Base
                                Occupancy</label>
                            <input type="number" name="base_occupancy" x-model="form.base_occupancy" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Max
                                Occupancy</label>
                            <input type="number" name="max_occupancy" x-model="form.max_occupancy" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Base Rate
                                (IDR) *</label>
                            <input type="number" name="base_rate" x-model="form.base_rate" min="0"
                                step="1000" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Amenities</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(amenity, index) in form.amenities" :key="index">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded-full">
                                        <span x-text="amenity"></span>
                                        <button type="button" @click="form.amenities.splice(index, 1)"
                                            class="hover:text-red-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" x-model="newAmenity" @keyup.enter="addAmenity"
                                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                                    placeholder="Add amenity...">
                                <button type="button" @click="addAmenity"
                                    class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                    Add
                                </button>
                            </div>
                            {{-- Hidden input for form submission --}}
                            <template x-for="(amenity, index) in form.amenities" :key="'hidden-' + index">
                                <input type="hidden" name="amenities[]" :value="amenity">
                            </template>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2" x-show="isEdit">
                            <input type="checkbox" name="is_active" id="rt_is_active" value="1"
                                x-model="form.is_active" class="rounded border-gray-300 dark:border-white/20">
                            <label for="rt_is_active" class="text-sm text-gray-700 dark:text-slate-300">Room type is
                                active</label>
                        </div>
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

    @push('scripts')
        <script>
            function roomTypeManager() {
                return {
                    showModal: false,
                    isEdit: false,
                    newAmenity: '',
                    form: {
                        id: '',
                        name: '',
                        code: '',
                        description: '',
                        base_occupancy: 2,
                        max_occupancy: 4,
                        base_rate: 0,
                        amenities: [],
                        is_active: true,
                    },
                    roomTypes: @json($roomTypes),

                    openAddModal() {
                        this.isEdit = false;
                        this.newAmenity = '';
                        this.form = {
                            id: '',
                            name: '',
                            code: '',
                            description: '',
                            base_occupancy: 2,
                            max_occupancy: 4,
                            base_rate: 0,
                            amenities: [],
                            is_active: true,
                        };
                        this.showModal = true;
                    },

                    openEditModal(rtId) {
                        const rt = this.roomTypes.find(r => r.id === rtId);
                        if (!rt) return;

                        this.isEdit = true;
                        this.newAmenity = '';
                        this.form = {
                            id: rt.id,
                            name: rt.name,
                            code: rt.code,
                            description: rt.description || '',
                            base_occupancy: rt.base_occupancy || 2,
                            max_occupancy: rt.max_occupancy || 4,
                            base_rate: rt.base_rate,
                            amenities: rt.amenities || [],
                            is_active: rt.is_active,
                        };
                        this.showModal = true;
                    },

                    addAmenity() {
                        const amenity = this.newAmenity.trim();
                        if (amenity && !this.form.amenities.includes(amenity)) {
                            this.form.amenities.push(amenity);
                            this.newAmenity = '';
                        }
                    }
                }
            }

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
    @endpush
</x-app-layout>
