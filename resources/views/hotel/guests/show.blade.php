<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            {{ $guest->name }}
            @php
                $vipColor = match ($guest->vip_level) {
                    'platinum' => 'bg-purple-100 text-purple-700',
                    'gold' => 'bg-yellow-100 text-yellow-700',
                    'silver' => 'bg-slate-100 text-slate-600',
                    default => '',
                };
            @endphp
            @if ($guest->vip_level && $guest->vip_level !== 'regular')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $vipColor }}">
                    {{ strtoupper($guest->vip_level) }} VIP
                </span>
            @endif
        </div>
    </x-slot>

    <x-slot name="pageHeader">
        <div class="flex items-center gap-2">
            <button onclick="openEditGuest()"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </button>
            <a href="{{ route('hotel.reservations.create', ['guest_id' => $guest->id]) }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Reservation
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Info Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-start gap-6 mb-6">
                    <div
                        class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-3xl font-bold text-blue-600 shrink-0">
                        {{ substr($guest->name ?? '?', 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900">{{ $guest->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $guest->guest_code }}</p>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @if ($guest->email)
                                <a href="mailto:{{ $guest->email }}"
                                    class="text-sm text-blue-600 hover:underline">{{ $guest->email }}</a>
                            @endif
                            @if ($guest->phone)
                                <a href="tel:{{ $guest->phone }}"
                                    class="text-sm text-gray-600">{{ $guest->phone }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ID Type</p>
                        <p class="font-medium text-gray-900">
                            {{ $guest->id_type ? strtoupper($guest->id_type) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ID Number</p>
                        <p class="font-medium text-gray-900">{{ $guest->id_number ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Date of Birth</p>
                        <p class="font-medium text-gray-900">
                            {{ $guest->date_of_birth ? \Carbon\Carbon::parse($guest->date_of_birth)->format('d M Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nationality</p>
                        <p class="font-medium text-gray-900">{{ $guest->nationality ?? '—' }}</p>
                    </div>
                </div>

                @if ($guest->address || $guest->city || $guest->country)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Address</p>
                        <p class="text-gray-900">
                            {{ trim(($guest->address ?? '') . ', ' . ($guest->city ?? '') . ', ' . ($guest->country ?? ''), ', ') }}
                        </p>
                    </div>
                @endif

                @if ($guest->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Notes</p>
                        <p class="text-gray-700 text-sm whitespace-pre-line">{{ $guest->notes }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Stay History Table --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Stay History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Reservation #</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Room Type</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Room #</th>
                                <th class="px-4 py-3 text-left">Check-in</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Check-out</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right hidden md:table-cell">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reservations as $rsv)
                                @php
                                    $statusColor = match ($rsv->status) {
                                        'pending'
                                            => 'bg-yellow-100 text-yellow-700',
                                        'confirmed'
                                            => 'bg-green-100 text-green-700',
                                        'checked_in'
                                            => 'bg-blue-100 text-blue-700',
                                        'checked_out'
                                            => 'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'no_show'
                                            => 'bg-orange-100 text-orange-700',
                                        default => 'bg-gray-100 text-gray-500',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('hotel.reservations.show', $rsv) }}"
                                            class="font-mono text-blue-600 hover:underline text-xs">
                                            {{ $rsv->reservation_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell text-gray-700">
                                        {{ $rsv->roomType?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 hidden lg:table-cell text-gray-600">
                                        {{ $rsv->room?->number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($rsv->check_in_date)->format('d M Y') }}</td>
                                    <td
                                        class="px-4 py-3 hidden sm:table-cell text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($rsv->check_out_date)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $rsv->status)) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right hidden md:table-cell font-medium text-gray-900 whitespace-nowrap">
                                        Rp {{ number_format($rsv->grand_total ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                        No stay history yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($reservations->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">{{ $reservations->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Stats Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Guest Statistics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">Total Stays</span>
                        <span
                            class="text-2xl font-bold text-gray-900">{{ $guest->total_stays ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">Last Stay</span>
                        <span class="font-medium text-gray-700">
                            {{ $guest->last_stay_date ? \Carbon\Carbon::parse($guest->last_stay_date)->format('d M Y') : '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">VIP Level</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-medium {{ $guest->vip_level && $guest->vip_level !== 'regular' ? $vipColor : 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst($guest->vip_level ?? 'regular') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('hotel.reservations.create', ['guest_id' => $guest->id]) }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Create Reservation
                    </a>
                    <button onclick="openEditGuest()"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </button>
                    <form method="POST" action="{{ route('hotel.guests.destroy', $guest) }}" data-confirm="Delete this guest? This action cannot be undone." data-confirm-type="danger">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Guest
                        </button>
                    </form>
                </div>
            </div>

            {{-- Metadata --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Record Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Created</span>
                        <span
                            class="text-gray-700">{{ $guest->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($guest->updated_at != $guest->created_at)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Last Updated</span>
                            <span
                                class="text-gray-700">{{ $guest->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Guest Modal --}}
    <div id="modal-edit-guest" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Edit Guest</h3>
                <button onclick="document.getElementById('modal-edit-guest').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form method="POST" action="{{ route('hotel.guests.update', $guest) }}" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full Name
                            *</label>
                        <input type="text" name="name" value="{{ $guest->name }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ $guest->email }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="tel" name="phone" value="{{ $guest->phone }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID Type</label>
                        <select name="id_type"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select...</option>
                            <option value="ktp" @selected($guest->id_type === 'ktp')>KTP</option>
                            <option value="passport" @selected($guest->id_type === 'passport')>Passport</option>
                            <option value="sim" @selected($guest->id_type === 'sim')>SIM</option>
                            <option value="kitas" @selected($guest->id_type === 'kitas')>KITAS</option>
                            <option value="other" @selected($guest->id_type === 'other')>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID
                            Number</label>
                        <input type="text" name="id_number" value="{{ $guest->id_number }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of
                            Birth</label>
                        <input type="date" name="date_of_birth" value="{{ $guest->date_of_birth }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <input type="text" name="nationality" value="{{ $guest->nationality }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $guest->address }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                        <input type="text" name="city" value="{{ $guest->city }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                        <input type="text" name="country" value="{{ $guest->country }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">VIP
                            Level</label>
                        <select name="vip_level"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="regular" @selected($guest->vip_level === 'regular' || !$guest->vip_level)>Regular</option>
                            <option value="silver" @selected($guest->vip_level === 'silver')>Silver</option>
                            <option value="gold" @selected($guest->vip_level === 'gold')>Gold</option>
                            <option value="platinum" @selected($guest->vip_level === 'platinum')>Platinum</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $guest->notes }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-guest').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update
                        Guest</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openEditGuest() {
                document.getElementById('modal-edit-guest').classList.remove('hidden');
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

            function showToast(message, type = 'success') {
                const colors = {
                    success: 'bg-green-600',
                    error: 'bg-red-600',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-600'
                };
                const icons = {
                    success: '?',
                    error: '?',
                    warning: '?',
                    info: '?'
                };
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
                toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
                document.body.appendChild(toast);
                requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
                setTimeout(() => {
                    toast.classList.add('translate-y-4', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }
        </script>
    @endpush
</x-app-layout>
