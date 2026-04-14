<x-app-layout>
    <x-slot name="header">Group Bookings</x-slot>

    <x-slot name="pageHeader">
        <a href="{{ route('hotel.group-bookings.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Group Booking
        </a>
    </x-slot>

    @if (session('success'))
        <div
            class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20">
            <p class="text-green-800 dark:text-green-200 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 p-4">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search group name or code..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="type"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>

    {{-- Groups Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($groupBookings as $group)
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $group->group_name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">{{ $group->group_code }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400',
                            'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                            'active' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'completed' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                        ];
                        $typeIcons = [
                            'corporate' => '🏢',
                            'family' => '👨‍👩‍👧‍👦',
                            'tour' => '🚌',
                            'event' => '🎉',
                            'government' => '🏛️',
                            'other' => '📋',
                        ];
                    @endphp
                    <span
                        class="px-2 py-1 rounded-lg text-xs font-medium {{ $statusColors[$group->status] ?? $statusColors['pending'] }}">
                        {{ ucfirst(str_replace('_', ' ', $group->status)) }}
                    </span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-lg">{{ $typeIcons[$group->type] ?? '📋' }}</span>
                        <span class="text-gray-600 dark:text-slate-300">{{ ucfirst($group->type) }} Group</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-gray-600 dark:text-slate-300">
                            {{ \Carbon\Carbon::parse($group->start_date)->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($group->end_date)->format('d M Y') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-gray-600 dark:text-slate-300">
                            {{ $group->total_rooms }} rooms · {{ $group->total_guests }} guests
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-slate-300">Payment:</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ number_format($group->paid_amount, 0) }} /
                                    {{ number_format($group->total_amount, 0) }}
                                </span>
                            </div>
                            @php
                                $paymentPercent =
                                    $group->total_amount > 0 ? ($group->paid_amount / $group->total_amount) * 100 : 0;
                            @endphp
                            <div class="mt-1 h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                                    style="width: {{ $paymentPercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-white/10">
                    <a href="{{ route('hotel.group-bookings.show', $group) }}"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        View Details →
                    </a>
                    @if ($group->status === 'pending')
                        <form action="{{ route('hotel.group-bookings.confirm', $group) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Confirm
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-slate-600 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-gray-500 dark:text-slate-400 text-sm">No group bookings found</p>
                <a href="{{ route('hotel.group-bookings.create') }}"
                    class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    Create Your First Group Booking
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $groupBookings->links() }}
    </div>
</x-app-layout>
