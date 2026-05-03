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

        {{-- AI Routing Link Card --}}
        <a href="{{ route('settings.ai-routing.index') }}"
            class="block bg-white hover:bg-violet-50/50 rounded-2xl border border-gray-200 hover:border-violet-300 overflow-hidden transition-all group">
            <div class="flex items-center gap-4 px-6 py-4">
                <div
                    class="w-12 h-12 rounded-xl flex items-center justify-center bg-violet-100 group-hover:bg-violet-200 transition">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 text-sm group-hover:text-violet-600 transition">
                        AI Routing & Use Case Management
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kelola routing AI per use case, override provider, dan lihat estimasi biaya penggunaan AI
                    </p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-violet-600 transition" fill="none"
                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>

        <form method="POST" action="{{ route('settings.integrations.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            @foreach ($groups as $groupKey => $fields)
                @php $meta = $groupMeta[$groupKey] ?? ['label' => $groupKey, 'icon' => '', 'color' => 'gray']; @endphp

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    {{-- Group Header --}}
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center
                        @if ($meta['color'] === 'green') bg-green-500/15 @elseif($meta['color'] === 'blue') bg-blue-500/15 @elseif($meta['color'] === 'orange') bg-orange-500/15 @else bg-purple-500/15 @endif">
                            <svg class="w-4.5 h-4.5
                            @if ($meta['color'] === 'green') text-green-400 @elseif($meta['color'] === 'blue') text-blue-400 @elseif($meta['color'] === 'orange') text-orange-400 @else text-purple-400 @endif"
                                fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                {!! $meta['icon'] !!}
                            </svg>
                        </div>
                        <h2 class="font-semibold text-gray-900 text-sm">{{ $meta['label'] }}</h2>

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
                                    <label for="{{ $key }}" class="text-sm font-medium text-gray-700">
                                        {{ $field['label'] }}
                                        @if ($field['encrypted'])
                                            <span class="ml-1 text-xs text-gray-400">
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
                                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition pr-10">
                                        <button type="button" @click="show = !show"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
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
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition">
                                @endif

                                @if ($field['description'])
                                    <p class="text-xs text-gray-400 mt-1.5">
                                        {{ $field['description'] }}</p>
                                @endif
                            </div>
                        @endforeach

                        {{-- Group-specific test button --}}
                        @if ($groupKey === 'communication')
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-2">Uji coba kirim WA (setelah
                                    simpan):</p>
                                <div class="flex gap-2">
                                    <input type="text" id="test_phone" placeholder="08123456789"
                                        class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition">
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
                <p class="text-xs text-gray-400">
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

        {{-- ─── AI Provider Section ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden" x-data="aiProviderSettings()">
            {{-- Section Header --}}
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-violet-100">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-900 text-sm">AI Provider</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Konfigurasi provider AI untuk tenant ini</p>
                </div>
                {{-- Active provider badge --}}
                <div class="ml-auto flex items-center gap-2">
                    <span class="text-xs text-gray-500">Provider aktif:</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                        :class="isOverride ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-600'"
                        x-text="activeProviderLabel"></span>
                    <span x-show="isOverride" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full"
                        style="display:none">Override Tenant</span>
                    <span x-show="!isOverride"
                        class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full">Global Default</span>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5">

                {{-- Provider aktif info --}}
                <div class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <div>
                        <p class="text-gray-700">
                            Provider yang sedang digunakan:
                            <strong x-text="activeProviderLabel" class="text-gray-900"></strong>
                            <span x-show="isOverride" class="text-violet-600 text-xs ml-1"
                                style="display:none">(Override Tenant)</span>
                            <span x-show="!isOverride" class="text-gray-500 text-xs ml-1">(Global Default)</span>
                        </p>
                        <p x-show="!isOverride" class="text-xs text-gray-400 mt-0.5">
                            Menggunakan konfigurasi global platform. Aktifkan override untuk menggunakan provider atau
                            API key sendiri.
                        </p>
                    </div>
                </div>

                {{-- Form AI Provider --}}
                <form method="POST" action="{{ route('settings.ai-provider.save') }}" class="space-y-4"
                    id="ai-provider-form">
                    @csrf

                    {{-- Toggle Override --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Aktifkan Override Provider</p>
                            <p class="text-xs text-gray-400 mt-0.5">Gunakan provider atau API key sendiri, berbeda dari
                                default platform</p>
                        </div>
                        <button type="button" role="switch" :aria-checked="isOverride.toString()"
                            @click="isOverride = !isOverride" :class="isOverride ? 'bg-violet-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                            <span :class="isOverride ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                        </button>
                        <input type="hidden" name="ai_provider_override" :value="isOverride ? '1' : '0'">
                    </div>

                    {{-- Override fields (shown when toggle is ON) --}}
                    <div x-show="isOverride" x-collapse style="display:none" class="space-y-4">

                        {{-- Provider dropdown --}}
                        <div>
                            <label for="ai_provider" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Provider AI
                            </label>
                            <select id="ai_provider" name="ai_provider" x-model="selectedProvider"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                                <option value="gemini">Gemini (Google)</option>
                                <option value="anthropic">Anthropic (Claude)</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1.5">Provider yang akan digunakan untuk semua fitur AI
                                di tenant ini</p>
                        </div>

                        {{-- Gemini API Key --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="gemini_api_key" class="text-sm font-medium text-gray-700">
                                    Gemini API Key
                                    <span class="ml-1 text-xs text-gray-400">
                                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg> Terenkripsi
                                    </span>
                                </label>
                                @if ($aiProviderData['has_gemini_key'])
                                    <span class="text-xs text-green-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 12.75l6 6 9-13.5" />
                                        </svg> Sudah diatur
                                    </span>
                                @endif
                            </div>
                            <div class="relative" x-data="{ showGeminiKey: false }">
                                <input :type="showGeminiKey ? 'text' : 'password'" id="gemini_api_key"
                                    name="gemini_api_key"
                                    placeholder="{{ $aiProviderData['has_gemini_key'] ? '(biarkan kosong untuk tetap pakai nilai lama)' : 'Masukkan Gemini API Key' }}"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition pr-10">
                                <button type="button" @click="showGeminiKey = !showGeminiKey"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                    <svg x-show="!showGeminiKey" class="w-4 h-4" fill="none"
                                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showGeminiKey" class="w-4 h-4" fill="none" stroke="currentColor"
                                        stroke-width="1.5" viewBox="0 0 24 24" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">Opsional — kosongkan untuk menggunakan API key
                                platform</p>
                        </div>

                        {{-- Anthropic API Key --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="anthropic_api_key" class="text-sm font-medium text-gray-700">
                                    Anthropic API Key
                                    <span class="ml-1 text-xs text-gray-400">
                                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg> Terenkripsi
                                    </span>
                                </label>
                                @if ($aiProviderData['has_anthropic_key'])
                                    <span class="text-xs text-green-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 12.75l6 6 9-13.5" />
                                        </svg> Sudah diatur
                                    </span>
                                @endif
                            </div>
                            <div class="relative" x-data="{ showAnthropicKey: false }">
                                <input :type="showAnthropicKey ? 'text' : 'password'" id="anthropic_api_key"
                                    name="anthropic_api_key"
                                    placeholder="{{ $aiProviderData['has_anthropic_key'] ? '(biarkan kosong untuk tetap pakai nilai lama)' : 'Masukkan Anthropic API Key' }}"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition pr-10">
                                <button type="button" @click="showAnthropicKey = !showAnthropicKey"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                    <svg x-show="!showAnthropicKey" class="w-4 h-4" fill="none"
                                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showAnthropicKey" class="w-4 h-4" fill="none"
                                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                        style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">Opsional — kosongkan untuk menggunakan API key
                                platform</p>
                        </div>

                    </div>{{-- end x-show isOverride --}}

                    {{-- Test Koneksi + Simpan --}}
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
                        {{-- Test Koneksi --}}
                        <button type="button" @click="testConnection()" :disabled="testLoading"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-gray-700 rounded-xl text-sm font-medium transition flex items-center gap-2">
                            <svg x-show="testLoading" class="w-4 h-4 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <svg x-show="!testLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                            </svg>
                            <span x-text="testLoading ? 'Menguji…' : 'Test Koneksi'"></span>
                        </button>

                        {{-- Test result --}}
                        <div x-show="testResult" x-transition
                            class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg"
                            :class="testSuccess ? 'bg-green-50 text-green-700 border border-green-200' :
                                'bg-red-50 text-red-600 border border-red-200'"
                            style="display:none">
                            <svg x-show="testSuccess" class="w-3.5 h-3.5 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <svg x-show="!testSuccess" class="w-3.5 h-3.5 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span x-text="testResult"></span>
                        </div>

                        {{-- Spacer --}}
                        <div class="flex-1"></div>

                        {{-- Simpan --}}
                        <p class="text-xs text-gray-400 hidden sm:flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            API key tersimpan terenkripsi
                        </p>
                        <button type="submit" :disabled="saveLoading"
                            class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                            <svg x-show="saveLoading" class="w-4 h-4 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            <svg x-show="!saveLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="saveLoading ? 'Menyimpan…' : 'Simpan'"></span>
                        </button>
                    </div>

                    {{-- Save result flash --}}
                    <div x-show="saveResult" x-transition
                        class="flex items-center gap-2 text-sm px-4 py-2.5 rounded-xl"
                        :class="saveSuccess ? 'bg-green-50 text-green-700 border border-green-200' :
                            'bg-red-50 text-red-600 border border-red-200'"
                        style="display:none">
                        <svg x-show="saveSuccess" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="!saveSuccess" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <span x-text="saveResult"></span>
                    </div>

                </form>
            </div>
        </div>
        {{-- ─── End AI Provider Section ─────────────────────────────────── --}}

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

            function aiProviderSettings() {
                return {
                    isOverride: {{ $aiProviderData['has_override'] ? 'true' : 'false' }},
                    selectedProvider: '{{ $aiProviderData['selected_provider'] }}',
                    activeProvider: '{{ $aiProviderData['active_provider'] }}',
                    globalDefault: '{{ $aiProviderData['global_default'] }}',

                    testLoading: false,
                    testResult: '',
                    testSuccess: false,

                    saveLoading: false,
                    saveResult: '',
                    saveSuccess: false,

                    providerLabels: {
                        gemini: 'Gemini (Google)',
                        anthropic: 'Anthropic (Claude)',
                    },

                    get activeProviderLabel() {
                        const provider = this.isOverride ? this.selectedProvider : this.globalDefault;
                        return this.providerLabels[provider] ?? provider;
                    },

                    testConnection() {
                        this.testLoading = true;
                        this.testResult = '';

                        const timeout = setTimeout(() => {
                            this.testLoading = false;
                            this.testResult = 'Waktu habis (10 detik). Periksa koneksi atau API key.';
                            this.testSuccess = false;
                        }, 10000);

                        fetch('{{ route('settings.ai-provider.test-connection') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    provider: this.isOverride ? this.selectedProvider : this.globalDefault,
                                }),
                            })
                            .then(r => r.json())
                            .then(data => {
                                clearTimeout(timeout);
                                this.testResult = data.message;
                                this.testSuccess = data.success;
                            })
                            .catch(() => {
                                clearTimeout(timeout);
                                this.testResult = 'Gagal terhubung ke server.';
                                this.testSuccess = false;
                            })
                            .finally(() => {
                                this.testLoading = false;
                            });
                    },

                    submitForm(form) {
                        this.saveLoading = true;
                        this.saveResult = '';

                        const formData = new FormData(form);

                        fetch('{{ route('settings.ai-provider.save') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: formData,
                            })
                            .then(r => {
                                if (r.redirected || r.ok) {
                                    this.saveResult = 'Pengaturan AI Provider berhasil disimpan.';
                                    this.saveSuccess = true;
                                    // Update active provider display
                                    this.activeProvider = this.isOverride ? this.selectedProvider : this.globalDefault;
                                } else {
                                    return r.json().then(data => {
                                        this.saveResult = data.message ?? 'Gagal menyimpan pengaturan.';
                                        this.saveSuccess = false;
                                    });
                                }
                            })
                            .catch(() => {
                                this.saveResult = 'Gagal terhubung ke server.';
                                this.saveSuccess = false;
                            })
                            .finally(() => {
                                this.saveLoading = false;
                                // Auto-hide after 5 seconds
                                setTimeout(() => {
                                    this.saveResult = '';
                                }, 5000);
                            });
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
