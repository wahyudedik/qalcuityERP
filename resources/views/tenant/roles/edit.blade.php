<x-app-layout>
    <x-slot name="title">Edit Role — Qalcuity ERP</x-slot>
    <x-slot name="header">Edit Role: {{ $role->name }}</x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('tenant.roles.update', $role) }}" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Role</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $role->name) }}" required
                        autofocus
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                               @error('name') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1.5">Minimal 3 karakter, maksimal 50 karakter.</p>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi <span
                            class="text-gray-400">(opsional)</span></label>
                    <textarea id="description" name="description" rows="3" placeholder="Jelaskan fungsi role ini..."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none
                               @error('description') border-red-500 @enderror">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role Info --}}
                <div class="border-t border-gray-200 pt-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Dibuat pada</span>
                        <span class="text-gray-700">{{ $role->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($role->creator)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Dibuat oleh</span>
                            <span class="text-gray-700">{{ $role->creator->name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Jumlah pengguna</span>
                        <span class="text-gray-700">{{ $role->userCount() }} pengguna</span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-2">
                    <a href="{{ route('tenant.roles.permissions', $role) }}"
                        class="px-4 py-2.5 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-xl transition">
                        Atur Izin Akses
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tenant.roles.index') }}"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition shadow-sm shadow-blue-200">
                            Perbarui
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
