<x-app-layout>
    <x-slot name="header">Profil Saya</x-slot>

    <div class="max-w-2xl mx-auto space-y-5">

        @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('self-service.profile.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- Avatar --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Foto Profil</h2>
                <div class="flex items-center gap-5">
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="avatar"
                        class="w-20 h-20 rounded-full object-cover ring-2 ring-blue-500/30" id="avatar-preview">
                    <div>
                        <input type="file" name="avatar" id="avatar-input" accept=".jpg,.jpeg,.png" class="hidden"
                            onchange="previewAvatar(this)">
                        <label for="avatar-input"
                            class="cursor-pointer px-4 py-2 text-sm bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition">
                            Ganti Foto
                        </label>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">JPG/PNG, maks 2MB</p>
                    </div>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Data Pribadi</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" value="{{ auth()->user()->email }}" disabled
                            class="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-400 dark:text-slate-500 cursor-not-allowed">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Email tidak dapat diubah sendiri. Hubungi admin.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone ?? $employee?->phone) }}"
                            placeholder="cth: 08123456789"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @if($employee)
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea name="address" rows="3" placeholder="Alamat lengkap"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('address', $employee->address) }}</textarea>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Info Karyawan (read-only) --}}
            @if($employee)
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Kepegawaian</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">ID Karyawan</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $employee->employee_id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Jabatan</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $employee->position ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Departemen</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $employee->department ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Bergabung</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $employee->join_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Status</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5 capitalize">{{ $employee->status }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Bank</p>
                        <p class="font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ $employee->bank_name ? $employee->bank_name . ' — ' . $employee->bank_account : '-' }}
                        </p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-4">Data kepegawaian hanya dapat diubah oleh HRD.</p>
            </div>
            @endif

            {{-- Ganti Password --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-1">Ganti Password</h2>
                <p class="text-xs text-gray-400 dark:text-slate-500 mb-4">Kosongkan jika tidak ingin mengganti password.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('self-service.dashboard') }}"
                    class="px-4 py-2.5 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</x-app-layout>
