<x-app-layout>
    <x-slot name="header">Pengaturan Integrasi</x-slot>

    <div class="max-w-4xl mx-auto space-y-2" x-data="integrationSettings()">

        {{-- Flash success --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-2xl px-4 py-3 text-sm mb-4">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Info banner --}}
        <div
            class="flex items-start gap-3 bg-blue-500/10 border border-blue-500/20 rounded-2xl px-4 py-3 text-sm text-blue-400 mb-4">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div>
                <p class="font-medium text-blue-300">Integrasi khusus bisnis Anda</p>
                <p class="text-blue-400/80 mt-0.5">API key di sini sepenuhnya tanggungan Anda. Platform kami tidak
                    menyediakan layanan-layanan berikut. Semua key disimpan terenkripsi per-tenant.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.integrations.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            @foreach ($groups as $groupKey => $fields)
                @php $meta = $groupMeta[$groupKey] ?? ['label' => $groupKey, 'icon' => '', 'color' => 'gray']; @endphp

                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                    {{-- Group Header --}}
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-white/5">
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center
                        @if ($meta['color'] === 'green') bg-green-500/15 @elseif($meta['color'] === 'blue') bg-blue-500/15 @elseif($meta['color'] === 'orange') bg-orange-500/15 @else bg-purple-500/15 @endif">
                            <svg class="w-4.5 h-4.5
                            @if ($meta['color'] === 'green') text-green-400 @elseif($meta['color'] === 'blue') text-blue-400 @elseif($meta['color'] === 'orange') text-orange-400 @else text-purple-400 @endif"
                                fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                {!! $meta['icon'] !!}
                            </svg>
                        </div>
                        <h2 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $meta['label'] }}</h2>

                        {{-- Status indicator --}}
                        @php
                            $allSet = collect($fields)->every(fn($f) => $f['has_value']);
                            $anySet = collect($fields)->contains(fn($f) => $f['has_value']);
                        @endphp
                        @if ($allSet)
                            <span
                                class="ml-auto px-2 py-0.5 bg-green-500/15 text-green-400 text-xs rounded-full">Terkonfigurasi</span>
                        @elseif($anySet)
                            <span
                                class="ml-auto px-2 py-0.5 bg-yellow-500/15 text-yellow-400 text-xs rounded-full">Sebagian</span>
                        @else
                            <span class="ml-auto px-2 py-0.5 bg-gray-500/15 text-gray-400 text-xs rounded-full">Belum
                                diatur</span>
                        @endif
                    </div>

                    {{-- Fields --}}
                    <div class="px-6 py-5 space-y-4">
                        @foreach ($fields as $key => $field)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="{{ $key }}"
                                        class="text-sm font-medium text-gray-700 dark:text-slate-300">
                                        {{ $field['label'] }}
                                        @if ($field['encrypted'])
                                            <span class="ml-1 text-xs text-gray-400 dark:text-slate-500">
                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor"
                                                    stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg> Terenkripsi
                                            </span>
                                        @endif
                                    </label>
                                    @if ($field['has_value'])
                                        <span class="text-xs text-green-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg> Sudah diatur
                                        </span>
                                    @endif
                                </div>

                                @if ($field['encrypted'])
                                    {{-- Password/token field with toggle --}}
                                    <div class="relative" x-data="{ show: false }">
                                        <input :type="show ? 'text' : 'password'" id="{{ $key }}"
                                            name="{{ $key }}"
                                            placeholder="{{ $field['has_value'] ? '(biarkan kosong untuk tetap pakai nilai lama)' : 'Masukkan ' . $field['label'] }}"
                                            class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition pr-10">
                                        <button type="button" @click="show = !show"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition">
                                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24" style="display:none">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <input type="text" id="{{ $key }}" name="{{ $key }}"
                                        value="{{ old($key, $field['value']) }}"
                                        placeholder="{{ 'Masukkan ' . $field['label'] }}"
                                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition">
                                @endif

                                @if ($field['description'])
                                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1.5">
                                        {{ $field['description'] }}</p>
                                @endif
                            </div>
                        @endforeach

                        {{-- Group-specific test button --}}
                        @if ($groupKey === 'communication')
                            <div class="pt-2 border-t border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 dark:text-slate-500 mb-2">Uji coba kirim WA (setelah
                                    simpan):</p>
                                <div class="flex gap-2">
                                    <input type="text" id="test_phone" placeholder="08123456789"
                                        class="flex-1 bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500 transition">
                                    <button type="button" @click="testFonnte()" :disabled="testLoading"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-500 disabled:opacity-50 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                                        <svg x-show="testLoading" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        <span x-show="!testLoading">Test WA</span>
                                        <span x-show="testLoading" style="display:none">Mengirim…</span>
                                    </button>
                                </div>
                                <p x-show="testResult" x-text="testResult"
                                    :class="testSuccess ? 'text-green-400' : 'text-red-400'" class="text-xs mt-2"
                                    style="display:none"></p>
                            </div>
                        @endif

                        @if ($groupKey === 'weather')
                            <div
                                class="bg-blue-500/5 border border-blue-500/20 rounded-xl px-4 py-3 text-xs text-blue-400/80">
                                <p class="font-medium text-blue-400 mb-1">Cara mendapatkan API Key:</p>
                                <ol class="list-decimal list-inside space-y-0.5">
                                    <li>Daftar gratis di <a href="https://openweathermap.org/api" target="_blank"
                                            class="text-blue-400 hover:underline">openweathermap.org</a></li>
                                    <li>Buka menu API Keys di akun Anda</li>
                                    <li>Salin Default API Key dan paste di sini</li>
                                </ol>
                                <p class="mt-1.5 text-blue-400/60">Plan gratis: 1.000 call/hari — cukup untuk modul
                                    pertanian/peternakan.</p>
                            </div>
                        @endif

                        @if ($groupKey === 'cctv')
                            <div
                                class="bg-orange-500/5 border border-orange-500/20 rounded-xl px-4 py-3 text-xs text-orange-400/80">
                                Pastikan NVR/DVR Anda bisa diakses dari server. Gunakan IP lokal jika server dan NVR
                                dalam satu jaringan.
                            </div>
                        @endif

                        @if ($groupKey === 'face')
                            <div
                                class="bg-purple-500/5 border border-purple-500/20 rounded-xl px-4 py-3 text-xs text-purple-400/80">
                                Service face recognition adalah Python Flask/FastAPI yang berjalan terpisah. Pastikan
                                sudah terinstall dan berjalan sebelum mengaktifkan fitur absensi wajah.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Save Button --}}
            <div class="flex items-center justify-end gap-3 py-2">
                <p class="text-xs text-gray-400 dark:text-slate-500">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Semua API key tersimpan terenkripsi
                </p>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function integrationSettings() {
                return {
                    testLoading: false,
                    testResult: '',
                    testSuccess: false,

                    testFonnte() {
                        const phone = document.getElementById('test_phone').value;
                        if (!phone) {
                            this.testResult = 'Masukkan nomor tujuan terlebih dahulu.';
                            this.testSuccess = false;
                            return;
                        }

                        this.testLoading = true;
                        this.testResult = '';

                        fetch('{{ route('settings.integrations.test-fonnte') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    phone
                                }),
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.testResult = data.message;
                                this.testSuccess = data.success;
                            })
                            .catch(() => {
                                this.testResult = 'Gagal terhubung ke server.';
                                this.testSuccess = false;
                            })
                            .finally(() => {
                                this.testLoading = false;
                            });
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
