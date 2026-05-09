<x-app-layout>
    <x-slot name="title">Buat Role Baru — Qalcuity ERP</x-slot>
    <x-slot name="header">Buat Role Baru</x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('tenant.roles.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Role</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Contoh: Supervisor Gudang"
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
                               @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="clone_from" class="block text-sm font-medium text-gray-700 mb-1.5">Salin Izin Dari <span
                            class="text-gray-400">(opsional)</span></label>
                    <select id="clone_from" name="clone_from"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">— Mulai tanpa izin (kosong) —</option>
                        <optgroup label="Role Bawaan">
                            @foreach ($hardcodedRoles as $role)
                                <option value="hardcoded:{{ $role }}"
                                    {{ old('clone_from') === "hardcoded:{$role}" ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </optgroup>
                        @if ($customRoles->isNotEmpty())
                            <optgroup label="Custom Roles">
                                @foreach ($customRoles as $role)
                                    <option value="custom:{{ $role->id }}"
                                        {{ old('clone_from') === "custom:{$role->id}" ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1.5">Pilih role yang ingin disalin izin aksesnya sebagai titik
                        awal. Anda bisa mengubahnya nanti.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('tenant.roles.index') }}"
                        class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition shadow-sm shadow-blue-200">
                        Simpan & Atur Izin
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
