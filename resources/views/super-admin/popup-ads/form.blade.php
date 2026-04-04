<x-app-layout>
    <x-slot name="title">{{ $ad->exists ? 'Edit' : 'Buat' }} Popup Iklan — Qalcuity ERP</x-slot>
    <x-slot name="header">{{ $ad->exists ? 'Edit' : 'Buat' }} Popup Iklan</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('super-admin.popup-ads.index') }}"
            class="flex items-center gap-2 text-sm text-slate-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/10 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </x-slot>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/30 text-red-400 text-sm rounded-xl space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST"
        action="{{ $ad->exists ? route('super-admin.popup-ads.update', $ad) : route('super-admin.popup-ads.store') }}"
        enctype="multipart/form-data" x-data="popupAdForm()" class="max-w-2xl space-y-6">
        @csrf
        @if ($ad->exists)
            @method('PUT')
        @endif

        {{-- Basic Info --}}
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Konten Iklan</h3>

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Judul <span
                        class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title', $ad->title) }}" required maxlength="200"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Contoh: Promo Ramadan 50% untuk semua tenant!">
            </div>

            {{-- Body --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Deskripsi</label>
                <textarea name="body" rows="3" maxlength="1000"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Teks pendek yang muncul di bawah judul popup...">{{ old('body', $ad->body) }}</textarea>
            </div>

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Gambar Banner</label>
                <div class="space-y-3">
                    @if ($ad->image_path)
                        <div class="relative inline-block">
                            <img src="{{ Storage::url($ad->image_path) }}" id="img-preview"
                                class="h-32 w-auto rounded-xl object-cover border border-white/10">
                        </div>
                    @else
                        <img id="img-preview" src=""
                            class="h-32 w-auto rounded-xl object-cover border border-white/10 hidden">
                    @endif
                    <input type="file" name="image" accept="image/*" id="image-input"
                        class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer"
                        onchange="previewImage(this)">
                    <p class="text-xs text-slate-500">Maks 2MB — JPG, PNG, GIF, WEBP. Resolusi ideal: 600×300px.</p>
                </div>
            </div>

            {{-- CTA Button --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Label Tombol CTA</label>
                    <input type="text" name="button_label" value="{{ old('button_label', $ad->button_label) }}"
                        maxlength="100"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Lihat Promo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">URL Tombol CTA</label>
                    <input type="url" name="button_url" value="{{ old('button_url', $ad->button_url) }}"
                        maxlength="500"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="https://...">
                </div>
            </div>
        </div>

        {{-- Targeting --}}
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Target & Jadwal</h3>

            {{-- Target --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Target Tenant <span
                        class="text-red-400">*</span></label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                        :class="target === 'all' ? 'border-blue-500 bg-blue-500/10' :
                            'border-white/10 bg-white/5 hover:border-white/20'">
                        <input type="radio" name="target" value="all" x-model="target"
                            class="text-blue-600 border-white/20 bg-transparent">
                        <div>
                            <p class="text-sm font-medium text-white">Semua Tenant</p>
                            <p class="text-xs text-slate-400">Tampil ke semua pengguna aktif</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                        :class="target === 'specific' ? 'border-blue-500 bg-blue-500/10' :
                            'border-white/10 bg-white/5 hover:border-white/20'">
                        <input type="radio" name="target" value="specific" x-model="target"
                            class="text-blue-600 border-white/20 bg-transparent">
                        <div>
                            <p class="text-sm font-medium text-white">Tenant Tertentu</p>
                            <p class="text-xs text-slate-400">Pilih tenant yang ditarget</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Tenant multi-select (shown only when specific) --}}
            <div x-show="target === 'specific'" x-transition>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Pilih Tenant</label>
                <div
                    class="max-h-52 overflow-y-auto rounded-xl border border-white/10 bg-white/5 divide-y divide-white/5">
                    @foreach ($tenants as $tenant)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 cursor-pointer transition">
                            <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}"
                                class="rounded border-white/20 text-blue-600 bg-transparent"
                                {{ in_array($tenant->id, old('tenant_ids', $ad->tenant_ids ?? [])) ? 'checked' : '' }}>
                            <span class="text-sm text-slate-200">{{ $tenant->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Frequency --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Frekuensi Tampil <span
                        class="text-red-400">*</span></label>
                <select name="frequency"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="once" {{ old('frequency', $ad->frequency) === 'once' ? 'selected' : '' }}
                        class="bg-slate-800">Sekali saja (per user)</option>
                    <option value="daily" {{ old('frequency', $ad->frequency) === 'daily' ? 'selected' : '' }}
                        class="bg-slate-800">Setiap hari</option>
                    <option value="always" {{ old('frequency', $ad->frequency) === 'always' ? 'selected' : '' }}
                        class="bg-slate-800">Selalu (setiap kunjungan)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1.5">
                    <strong class="text-slate-400">Sekali saja</strong> — user yang sudah dismiss tidak akan lihat
                    lagi. |
                    <strong class="text-slate-400">Harian</strong> — muncul kembali keesokan harinya. |
                    <strong class="text-slate-400">Selalu</strong> — muncul setiap kali buka dashboard.
                </p>
            </div>

            {{-- Date range --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mulai Tanggal</label>
                    <input type="date" name="starts_at"
                        value="{{ old('starts_at', $ad->starts_at?->format('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan = tidak ada batas mulai</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Berakhir Tanggal</label>
                    <input type="date" name="ends_at"
                        value="{{ old('ends_at', $ad->ends_at?->format('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan = tidak ada batas akhir</p>
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-sm font-medium text-slate-200">Status Aktif</p>
                    <p class="text-xs text-slate-500">Iklan hanya tampil jika diaktifkan</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                        {{ old('is_active', $ad->is_active ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                {{ $ad->exists ? 'Simpan Perubahan' : 'Buat Iklan' }}
            </button>
            <a href="{{ route('super-admin.popup-ads.index') }}"
                class="px-5 py-2.5 text-sm text-slate-400 hover:text-white border border-white/10 rounded-xl hover:bg-white/5 transition">
                Batal
            </a>
        </div>
    </form>

    @push('scripts')
        <script>
            function previewImage(input) {
                const preview = document.getElementById('img-preview');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function popupAdForm() {
                return {
                    target: '{{ old('target', $ad->target ?? 'all') }}',
                };
            }
        </script>
    @endpush
</x-app-layout>
