<x-app-layout>
    <x-slot name="title">Kelola Pengguna — Qalcuity ERP</x-slot>
    <x-slot name="header">Kelola Pengguna</x-slot>
    <x-slot name="pageHeader">
        <a href="{{ route('tenant.users.create') }}"
           class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm shadow-blue-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </x-slot>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Pengguna</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Role</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Status</th>
                    <th class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    <td class="px-4 sm:px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                                {{-- Role & status shown inline on mobile --}}
                                <div class="flex items-center gap-1.5 mt-1 sm:hidden">
                                    @php
                                    $roleStyle = ['admin' => 'bg-purple-500/20 text-purple-400', 'manager' => 'bg-blue-500/20 text-blue-400', 'staff' => 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300'];
                                    $roleLabel = ['admin' => 'Admin', 'manager' => 'Manager', 'staff' => 'Staff'];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $roleStyle[$user->role] ?? '' }}">{{ $roleLabel[$user->role] ?? $user->role }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium {{ $user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                        @php
                        $roleStyle = ['admin' => 'bg-purple-500/20 text-purple-400', 'manager' => 'bg-blue-500/20 text-blue-400', 'staff' => 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300'];
                        $roleLabel = ['admin' => 'Admin', 'manager' => 'Manager', 'staff' => 'Staff'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $roleStyle[$user->role] ?? 'bg-[#f8f8f8] dark:bg-white/10 text-slate-300' }}">
                            {{ $roleLabel[$user->role] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ $user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            @unless($user->isAdmin())
                            <a href="{{ route('tenant.users.permissions', $user) }}"
                               class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-green-400 hover:bg-green-500/10 transition" title="Izin Akses">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </a>
                            <a href="{{ route('tenant.users.edit', $user) }}"
                               class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('tenant.users.toggle', $user) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($user->is_active)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tenant.users.destroy', $user) }}" class="inline"
                                  onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                    class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-600 px-2">Admin Utama</span>
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center text-gray-400 dark:text-slate-500">
                            <svg class="w-10 h-10 mb-2 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm">Belum ada pengguna</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>


