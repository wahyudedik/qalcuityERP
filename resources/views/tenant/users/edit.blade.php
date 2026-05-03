<x-app-layout>
    <x-slot name="title">Edit Pengguna — Qalcuity ERP</x-slot>
    <x-slot name="header">Edit Pengguna</x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('tenant.users.update', $user) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                               @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-400 cursor-not-allowed">
                    <p class="text-xs text-slate-600 mt-1">Email tidak dapat diubah.</p>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                    <select id="role" name="role"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manajer — akses penuh semua modul</option>
                        <option value="staff"   {{ old('role', $user->role) === 'staff'   ? 'selected' : '' }}>Staff — akses terbatas (baca + POS)</option>
                        <option value="kasir"   {{ old('role', $user->role) === 'kasir'   ? 'selected' : '' }}>Kasir — hanya akses POS & penjualan</option>
                        <option value="gudang"  {{ old('role', $user->role) === 'gudang'  ? 'selected' : '' }}>Gudang — hanya akses inventori & stok</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1.5">Role menentukan menu dan AI tools yang bisa diakses pengguna.</p>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-400 mb-3">Kosongkan jika tidak ingin mengubah password.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                            <input id="password" type="password" name="password" autocomplete="new-password"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                                       @error('password') border-red-500 @enderror"
                                placeholder="••••••••">
                            @error('password')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('tenant.users.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-900 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
