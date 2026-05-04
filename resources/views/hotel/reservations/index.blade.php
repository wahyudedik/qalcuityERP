<x-app-layout>
    <x-slot name="header">Reservations</x-slot>

    <x-slot name="pageHeader">
        <a href="{{ route('hotel.reservations.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Reservation
        </a>
        <a href="{{ route('hotel.reservations.calendar') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Calendar
        </a>
    </x-slot>

    @php
        $tid = auth()->user()->tenant_id;
        $totalCount = \App\Models\Reservation::where('tenant_id', $tid)->count();
        $confirmedCount = \App\Models\Reservation::where('tenant_id', $tid)->where('status', 'confirmed')->count();
        $checkedInCount = \App\Models\Reservation::where('tenant_id', $tid)->where('status', 'checked_in')->count();
        $pendingCount = \App\Models\Reservation::where('tenant_id', $tid)->where('status', 'pending')->count();
    @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Reservations</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($confirmedCount) }}
            </p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Checked In</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($checkedInCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($pendingCount) }}
            </p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-3 p-4">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search guest name or reservation #..."
                class="flex-1 min-w-[200px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">All Statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <select name="source" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">All Sources</option>
                @foreach ($sources as $src)
                    <option value="{{ $src }}" @selected(request('source') === $src)>
                        {{ ucfirst(str_replace(['_', 'com'], ['.', ' '], $src)) }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Check-in from"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-gray-400 text-sm">—</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Check-out to"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            @if (request()->anyFilled(['search', 'status', 'source', 'date_from', 'date_to']))
                <a href="{{ route('hotel.reservations.index') }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Reservation #</th>
                        <th class="px-4 py-3 text-left">Guest</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Room Type</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Room #</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Check-in</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Check-out</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Nights</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Grand Total</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Source</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reservations as $rsv)
                        @php
                            $nights = \Carbon\Carbon::parse($rsv->check_in_date)->diffInDays(
                                \Carbon\Carbon::parse($rsv->check_out_date),
                            );
                            $statusColor = match ($rsv->status) {
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-green-100 text-green-700',
                                'checked_in' => 'bg-blue-100 text-blue-700',
                                'checked_out' => 'bg-gray-100 text-gray-600',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'no_show' => 'bg-orange-100 text-orange-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                            $sourceLabel = match ($rsv->source) {
                                'direct' => 'Direct',
                                'bookingcom' => 'Booking.com',
                                'agoda' => 'Agoda',
                                'expedia' => 'Expedia',
                                'airbnb' => 'Airbnb',
                                'tripadvisor' => 'TripAdvisor',
                                default => ucfirst($rsv->source ?? 'Direct'),
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('hotel.reservations.show', $rsv) }}"
                                    class="font-mono text-blue-600 hover:underline text-xs">
                                    {{ $rsv->reservation_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $rsv->guest?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $rsv->guest?->phone ?? '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-700">
                                {{ $rsv->roomType?->name ?? '—' }}</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-700">
                                {{ $rsv->room?->number ?? '—' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($rsv->check_in_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($rsv->check_out_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell text-gray-700">
                                {{ $nights }}</td>
                            <td
                                class="px-4 py-3 text-right hidden md:table-cell font-medium text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($rsv->grand_total ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">{{ $sourceLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $rsv->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('hotel.reservations.show', $rsv) }}"
                                        class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('hotel.reservations.edit', $rsv) }}"
                                        class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @if ($rsv->status === 'pending')
                                        <form method="POST"
                                            action="{{ route('hotel.reservations.confirm', $rsv) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-green-500 hover:bg-green-50"
                                                title="Confirm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($rsv->status === 'confirmed')
                                        <form method="POST" action="{{ route('hotel.checkin.process', $rsv) }}">
                                            @csrf
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"
                                                title="Check In">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($rsv->status === 'checked_in')
                                        <form method="POST" action="{{ route('hotel.checkout.process', $rsv) }}">
                                            @csrf
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-purple-500 hover:bg-purple-50"
                                                title="Check Out">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if (!in_array($rsv->status, ['cancelled', 'checked_out']))
                                        <button
                                            onclick="openCancelModal({{ $rsv->id }}, '{{ $rsv->reservation_number }}')"
                                            class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Cancel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center text-gray-400">
                                No reservations found. <a href="{{ route('hotel.reservations.create') }}"
                                    class="text-blue-500 hover:underline">Create one now.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reservations->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $reservations->links() }}</div>
        @endif
    </div>

    {{-- Cancel Modal --}}
    <div id="modal-cancel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Cancel Reservation</h3>
                <button onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-cancel" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p id="cancel-rsv-label" class="text-sm text-gray-600"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cancellation
                        Reason</label>
                    <textarea name="cancel_reason" rows="3" placeholder="Optional reason..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Back</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Cancel
                        Reservation</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openCancelModal(id, number) {
                document.getElementById('cancel-rsv-label').textContent = 'Cancel reservation ' + number + '?';
                document.getElementById('form-cancel').action = '/hotel/reservations/' + id + '/cancel';
                document.getElementById('modal-cancel').classList.remove('hidden');
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
                    success: '✓',
                    error: '✕',
                    warning: '⚠',
                    info: 'ℹ'
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
