<x-app-layout>
    <x-slot name="title">Manajemen Role — Qalcuity ERP</x-slot>
    <x-slot name="header">Manajemen Role</x-slot>
    <x-slot name="pageHeader">
        <a href="{{ route('tenant.roles.create') }}"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm shadow-blue-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Role Baru
        </a>
    </x-slot>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Hardcoded Roles Section --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Role Bawaan (Read-Only)
        </h3>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th
                                class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Nama Role</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                Tipe</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($hardcodedRoles as $role)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($role->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $role->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                                        Bawaan
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center justify-end">
                                        <form method="POST" action="{{ route('tenant.roles.store') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="name" value="{{ $role->name }} (Copy)">
                                            <input type="hidden" name="clone_from"
                                                value="hardcoded:{{ $role->slug }}">
                                            <button type="submit" title="Duplikasi sebagai Custom Role"
                                                class="p-2 rounded-lg text-gray-500 hover:text-blue-500 hover:bg-blue-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Custom Roles Section --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Custom Roles
        </h3>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th
                                class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Nama Role</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                Pengguna</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                Status</th>
                            <th
                                class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($customRoles as $role)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($role->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                            @if ($role->description)
                                                <p class="text-xs text-gray-500 truncate max-w-xs">
                                                    {{ $role->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $role->userCount() }} pengguna
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ $role->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $role->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $role->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('tenant.roles.permissions', $role) }}"
                                            class="p-2 rounded-lg text-gray-500 hover:text-green-600 hover:bg-green-50 transition"
                                            title="Izin Akses">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('tenant.roles.edit', $role) }}"
                                            class="p-2 rounded-lg text-gray-500 hover:text-blue-500 hover:bg-blue-50 transition"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('tenant.roles.clone', $role) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit" title="Duplikasi"
                                                class="p-2 rounded-lg text-gray-500 hover:text-purple-500 hover:bg-purple-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('tenant.roles.destroy', $role) }}"
                                            class="inline"
                                            onsubmit="return confirm('Hapus role {{ $role->name }}? Pastikan tidak ada pengguna yang menggunakan role ini.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                class="p-2 rounded-lg text-gray-500 hover:text-red-500 hover:bg-red-50 transition">
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
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <svg class="w-10 h-10 mb-2 text-gray-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <p class="text-sm text-gray-500">Belum ada custom role</p>
                                        <p class="text-xs text-gray-400 mt-1">Klik "Buat Role Baru" untuk membuat role
                                            pertama Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
