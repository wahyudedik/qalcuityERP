<x-app-layout>
    <x-slot name="header">Keamanan — Dashboard</x-slot>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">2FA Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $overview['two_factor_enabled_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Sesi Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overview['active_sessions'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">IP Whitelist</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overview['whitelisted_ips'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Permintaan Data</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $overview['pending_data_requests'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('cctv.index') }}"
            class="bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p
                        class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600">
                        CCTV Monitoring</p>
                    <p class="text-xs text-gray-500">Pantau kamera keamanan</p>
                </div>
            </div>
        </a>

        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Two-Factor Auth</p>
                    <p class="text-xs text-gray-500">Kelola autentikasi 2 faktor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Audit Log</p>
                    <p class="text-xs text-gray-500">Riwayat aktivitas keamanan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Security Events --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Event Keamanan Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Tipe Event</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">IP Address</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse(($overview['recent_security_events'] ?? collect()) as $event)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 whitespace-nowrap">
                                {{ $event->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $event->event_type ?? '-' }}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 hidden sm:table-cell max-w-xs truncate">
                                {{ $event->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                {{ $event->ip_address ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($event->success ?? false)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Sukses
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Gagal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                Belum ada event keamanan tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
