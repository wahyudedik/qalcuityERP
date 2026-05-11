<x-app-layout>
    <x-slot name="title">System Settings — Qalcuity ERP</x-slot>
    <x-slot name="header">System Settings — SuperAdmin</x-slot>

    @if (session('success'))
        <div
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ tab: 'ai' }" class="space-y-6">

        {{-- Tab Navigation --}}
        <div class="flex flex-wrap gap-1 bg-gray-100 rounded-2xl p-1.5">
            @php
                $tabs = [
                    'ai' => [
                        'icon' =>
                            'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                        'label' => 'AI / Gemini',
                    ],
                    'mail' => [
                        'icon' =>
                            'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                        'label' => 'Email SMTP',
                    ],
                    'oauth' => [
                        'icon' =>
                            'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
                        'label' => 'Google OAuth',
                    ],
                    'push' => [
                        'icon' =>
                            'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                        'label' => 'Push Notification',
                    ],
                    'alert' => [
                        'icon' =>
                            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        'label' => 'Alert & Error',
                    ],
                    'app' => [
                        'icon' =>
                            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                        'label' => 'App Settings',
                    ],
                    'ai_provider' => [
                        'icon' =>
                            'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'label' => 'AI Provider',
                    ],
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ?
                        'bg-white text-gray-900 shadow-sm' :
                        'text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="{{ $tab['icon'] }}" />
                    </svg>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        {{-- ═══ AI / GEMINI ═══ --}}
        <div x-show="tab === 'ai'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Konfigurasi AI / Gemini
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">API key Gemini digunakan oleh semua
                            tenant. Biaya ditanggung oleh owner/platform.</p>
                    </div>

                    {{-- API Key --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Gemini API Key <span class="text-red-400">*</span>
                                @if ($grouped['ai']['gemini_api_key']['is_set'] ?? false)
                                    <span class="ml-2 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan di DB</span>
                                @elseif($envFallbacks['gemini_api_key'])
                                    <span class="ml-2 text-amber-500 font-normal normal-case tracking-normal">From
                                        .env (fallback)</span>
                                @else
                                    <span class="ml-2 text-red-500 font-normal normal-case tracking-normal">✗ Belum
                                        dikonfigurasi</span>
                                @endif
                            </label>
                            <div class="flex gap-2">
                                <input type="password" id="gemini_api_key_input" name="gemini_api_key"
                                    placeholder="{{ $grouped['ai']['gemini_api_key']['is_set'] ?? false ? '••••••••••••••••••• (sudah diset, kosongkan untuk tidak mengubah)' : 'AIzaSy...' }}"
                                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                <button type="button" id="testGeminiBtn" @click="testGeminiApiKey()"
                                    class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition flex items-center gap-2 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Test API Key
                                </button>
                            </div>
                            <div id="geminiTestResult" class="mt-2 hidden">
                                <div class="p-3 rounded-xl text-sm"></div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Model</label>
                            <select name="gemini_model"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @php
                                    $currentModel =
                                        $grouped['ai']['gemini_model']['value'] ??
                                        config('gemini.model', 'gemini-2.5-flash');
                                    $models = [
                                        'gemini-2.5-flash',
                                        'gemini-2.5-flash-lite',
                                        'gemini-1.5-flash',
                                        'gemini-2.5-pro',
                                    ];
                                @endphp
                                @foreach ($models as $m)
                                    <option value="{{ $m }}" @selected($currentModel === $m)>{{ $m }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Timeout
                                (detik)</label>
                            <input type="number" name="gemini_timeout" min="10" max="300"
                                value="{{ $grouped['ai']['gemini_timeout']['value'] ?? config('gemini.timeout', 60) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Cache & Performance --}}
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Optimasi Cache &
                            Performa</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <input type="hidden" name="ai_response_cache_enabled" value="0">
                                <input type="checkbox" name="ai_response_cache_enabled" id="ai_cache" value="1"
                                    @checked(($grouped['ai']['ai_response_cache_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_cache" class="text-sm text-gray-700">Cache Response
                                    AI</label>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <input type="hidden" name="ai_rule_based_enabled" value="0">
                                <input type="checkbox" name="ai_rule_based_enabled" id="ai_rule" value="1"
                                    @checked(($grouped['ai']['ai_rule_based_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_rule" class="text-sm text-gray-700">Rule-Based
                                    Response</label>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                                <input type="hidden" name="ai_streaming_enabled" value="0">
                                <input type="checkbox" name="ai_streaming_enabled" id="ai_stream" value="1"
                                    @checked(($grouped['ai']['ai_streaming_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_stream" class="text-sm text-gray-700">Streaming
                                    AI</label>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4 mt-3">
                            @foreach ([['ai_cache_short_ttl', 'Cache TTL Pendek (detik)', '300'], ['ai_cache_default_ttl', 'Cache TTL Default (detik)', '3600'], ['ai_cache_long_ttl', 'Cache TTL Panjang (detik)', '86400']] as [$k, $lbl, $def])
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $lbl }}</label>
                                    <input type="number" name="{{ $k }}"
                                        value="{{ $grouped['ai'][$k]['value'] ?? $def }}"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Auto-Switching --}}
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                            Gemini Auto-Switching
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Konfigurasi fallback otomatis ke model alternatif saat
                            model utama mengalami rate limit atau quota habis.</p>

                        <div class="space-y-4">
                            {{-- Fallback Models --}}
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Fallback Models
                                    @if ($grouped['ai']['gemini_fallback_models']['is_set'] ?? false)
                                        <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                            Tersimpan</span>
                                    @endif
                                </label>
                                @php
                                    $fallbackRaw = $grouped['ai']['gemini_fallback_models']['value'] ?? null;
                                    $fallbackDisplay = '';
                                    if ($fallbackRaw) {
                                        $decoded = json_decode($fallbackRaw, true);
                                        $fallbackDisplay = is_array($decoded) ? implode(', ', $decoded) : $fallbackRaw;
                                    } else {
                                        $fallbackDisplay = implode(
                                            ', ',
                                            config('gemini.fallback_models', [
                                                'gemini-2.5-flash',
                                                'gemini-1.5-flash',
                                                'gemini-2.5-flash-lite',
                                            ]),
                                        );
                                    }
                                @endphp
                                <textarea name="gemini_fallback_models" rows="3"
                                    placeholder='["gemini-2.5-flash", "gemini-1.5-flash", "gemini-2.5-flash-lite"] atau: gemini-2.5-flash, gemini-1.5-flash'
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono resize-none">{{ $fallbackDisplay }}</textarea>
                                <p class="text-xs text-gray-400 mt-1">Urutan model fallback — model pertama adalah
                                    prioritas utama. Gunakan JSON array atau comma-separated. Contoh: <code
                                        class="bg-gray-100 px-1 rounded">gemini-2.5-flash, gemini-1.5-flash</code></p>
                            </div>

                            <div class="grid md:grid-cols-3 gap-4">
                                {{-- Rate Limit Cooldown --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        Rate Limit Cooldown (detik)
                                    </label>
                                    <input type="number" name="gemini_rate_limit_cooldown" min="1"
                                        max="86400"
                                        value="{{ $grouped['ai']['gemini_rate_limit_cooldown']['value'] ?? config('gemini.rate_limit_cooldown', 60) }}"
                                        placeholder="60"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">Durasi cooldown saat model kena rate limit
                                        (HTTP 429). Default: 60 detik.</p>
                                </div>

                                {{-- Quota Cooldown --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        Quota Cooldown (detik)
                                    </label>
                                    <input type="number" name="gemini_quota_cooldown" min="1" max="86400"
                                        value="{{ $grouped['ai']['gemini_quota_cooldown']['value'] ?? config('gemini.quota_cooldown', 3600) }}"
                                        placeholder="3600"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">Durasi cooldown saat quota harian habis.
                                        Default: 3600 detik (1 jam).</p>
                                </div>

                                {{-- Log Retention --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        Retensi Log Switch (hari)
                                    </label>
                                    <input type="number" name="gemini_log_retention_days" min="1"
                                        max="365"
                                        value="{{ $grouped['ai']['gemini_log_retention_days']['value'] ?? config('gemini.log_retention_days', 30) }}"
                                        placeholder="30"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-gray-400 mt-1">Berapa hari riwayat switch event disimpan
                                        sebelum dihapus otomatis. Default: 30 hari.</p>
                                </div>
                            </div>

                            <div class="bg-orange-50 rounded-xl p-3 text-xs text-orange-700 flex items-start gap-2">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Menyimpan konfigurasi ini akan mereset cache auto-switching sehingga model aktif
                                    kembali ke model utama. Pantau status model di <a
                                        href="{{ route('super-admin.ai-model.index') }}"
                                        class="underline font-semibold">halaman monitoring AI</a>.</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan Konfigurasi AI
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ MAIL / SMTP ═══ --}}
        <div x-show="tab === 'mail'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Konfigurasi Email SMTP
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Digunakan untuk email system:
                            registrasi, reset password, notifikasi sistem.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        @php
                            $mailFields = [
                                'mail_host' => [
                                    'label' => 'SMTP Host',
                                    'type' => 'text',
                                    'placeholder' => 'smtp.gmail.com',
                                    'encrypt' => false,
                                ],
                                'mail_port' => [
                                    'label' => 'SMTP Port',
                                    'type' => 'number',
                                    'placeholder' => '587',
                                    'encrypt' => false,
                                ],
                                'mail_username' => [
                                    'label' => 'Username',
                                    'type' => 'text',
                                    'placeholder' => 'user@email.com',
                                    'encrypt' => false,
                                ],
                                'mail_password' => [
                                    'label' => 'Password',
                                    'type' => 'password',
                                    'placeholder' => '••••••••',
                                    'encrypt' => true,
                                ],
                                'mail_from_address' => [
                                    'label' => 'From Address',
                                    'type' => 'email',
                                    'placeholder' => 'noreply@domain.com',
                                    'encrypt' => false,
                                ],
                                'mail_from_name' => [
                                    'label' => 'From Name',
                                    'type' => 'text',
                                    'placeholder' => 'Qalcuity ERP',
                                    'encrypt' => false,
                                ],
                            ];
                        @endphp
                        @foreach ($mailFields as $key => $field)
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    {{ $field['label'] }}
                                    @if ($grouped['mail'][$key]['is_set'] ?? false)
                                        <span
                                            class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                    @endif
                                </label>
                                <input type="{{ $field['type'] }}" name="{{ $key }}"
                                    @if (!$field['encrypt']) value="{{ $grouped['mail'][$key]['value'] ?? ($envFallbacks[$key] ?? '') }}" @endif
                                    placeholder="{{ $field['encrypt'] && ($grouped['mail'][$key]['is_set'] ?? false) ? '••• tersimpan — kosongkan untuk tidak mengubah •••' : $field['placeholder'] }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        @endforeach
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Enkripsi</label>
                            <select name="mail_encryption"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @php $currentEnc = $grouped['mail']['mail_encryption']['value'] ?? config('mail.mailers.smtp.encryption', 'tls'); @endphp
                                <option value="tls" @selected($currentEnc === 'tls')>TLS (Recommended)</option>
                                <option value="ssl" @selected($currentEnc === 'ssl')>SSL</option>
                                <option value="starttls" @selected($currentEnc === 'starttls')>STARTTLS</option>
                                <option value="" @selected($currentEnc === '' || $currentEnc === null)>None</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan SMTP
                        </button>
                        {{-- Test Email --}}
                        <form method="POST" action="{{ route('super-admin.settings.test-mail') }}"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="email" name="test_email" placeholder="test@email.com"
                                class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 w-52">
                            <button type="submit"
                                class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition whitespace-nowrap">
                                Test Kirim Email
                            </button>
                        </form>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ GOOGLE OAUTH ═══ --}}
        <div x-show="tab === 'oauth'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Google OAuth (Login dengan Google)
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Diperlukan agar tombol "Login dengan
                            Google" berfungsi. Daftarkan di Google Cloud Console.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Client ID
                                @if ($grouped['oauth']['google_client_id']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan</span>
                                @elseif($envFallbacks['google_client_id'])
                                    <span class="ml-1 text-amber-500 font-normal normal-case tracking-normal">From
                                        .env</span>
                                @endif
                            </label>
                            <input type="text" name="google_client_id"
                                value="{{ $grouped['oauth']['google_client_id']['value'] ?? '' }}"
                                placeholder="123456789-abc.apps.googleusercontent.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Client Secret
                                @if ($grouped['oauth']['google_client_secret']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan</span>
                                @endif
                            </label>
                            <input type="password" name="google_client_secret"
                                placeholder="{{ $grouped['oauth']['google_client_secret']['is_set'] ?? false ? '••• tersimpan — kosongkan untuk tidak mengubah •••' : 'GOCSPX-...' }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 text-xs text-blue-700 space-y-1">
                        <p class="font-semibold">Cara Setup Google OAuth:</p>
                        <ol class="list-decimal list-inside space-y-0.5">
                            <li>Buka <a href="https://console.cloud.google.com/" target="_blank"
                                    class="underline">Google Cloud Console</a></li>
                            <li>Buat project baru atau pilih yang ada</li>
                            <li>Aktifkan "Google+ API" atau "Google Identity"</li>
                            <li>Buat OAuth 2.0 Credentials</li>
                            <li>Tambahkan Authorized redirect URI: <code
                                    class="bg-blue-100 px-1 rounded">{{ config('app.url') }}/auth/google/callback</code>
                            </li>
                        </ol>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan OAuth
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ PUSH NOTIFICATION (VAPID) ═══ --}}
        <div x-show="tab === 'push'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Push Notification (VAPID Keys)
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">VAPID keys diperlukan untuk fitur push
                        notification browser. Generate sekali, berlaku permanen.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    @php
                        $vapidBlocks = [
                            'development' => [
                                'title' => 'Development (Local/Staging)',
                                'public_setting' => 'vapid_public_key_dev',
                                'private_setting' => 'vapid_private_key_dev',
                                'fallback' => $envFallbacks['vapid_public_key_dev'] ?? false,
                                'color' => 'bg-blue-500',
                            ],
                            'production' => [
                                'title' => 'Production',
                                'public_setting' => 'vapid_public_key_prod',
                                'private_setting' => 'vapid_private_key_prod',
                                'fallback' => $envFallbacks['vapid_public_key_prod'] ?? false,
                                'color' => 'bg-emerald-500',
                            ],
                        ];
                    @endphp

                    @foreach ($vapidBlocks as $env => $cfg)
                        <div class="rounded-xl border border-gray-200 p-4 space-y-3">
                            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $cfg['color'] }}"></span>
                                {{ $cfg['title'] }}
                            </h3>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Public Key
                                    @if ($grouped['push'][$cfg['public_setting']]['is_set'] ?? false)
                                        <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                            Tersimpan</span>
                                    @elseif($cfg['fallback'])
                                        <span class="ml-1 text-amber-500 font-normal normal-case tracking-normal">From
                                            .env</span>
                                    @else
                                        <span class="ml-1 text-red-500 font-normal normal-case tracking-normal">✗ Belum
                                            di-generate</span>
                                    @endif
                                </label>
                                <input type="text" readonly
                                    value="{{ $grouped['push'][$cfg['public_setting']]['value'] ?? ($env === 'development' ? config('services.vapid.development.public_key', '') : config('services.vapid.production.public_key', '')) }}"
                                    placeholder="Generate VAPID keys terlebih dahulu..."
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-xs text-gray-600 font-mono cursor-default">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Private
                                    Key</label>
                                <input type="text" readonly
                                    value="{{ $grouped['push'][$cfg['private_setting']]['is_set'] ?? false ? '••••••••••••••••••••••••••••••••••••••••••••' : 'Belum di-generate' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-xs text-gray-600 font-mono cursor-default">
                            </div>
                            <form method="POST"
                                action="{{ route('super-admin.settings.regenerate-vapid', ['environment' => $env]) }}"
                                data-confirm="Generate ulang VAPID keys {{ $cfg['title'] }}? Semua push subscription untuk environment ini akan tidak valid dan user perlu subscribe ulang.">
                                @csrf
                                <button type="submit"
                                    class="px-5 py-2.5 {{ $grouped['push'][$cfg['public_setting']]['is_set'] ?? false ? 'bg-amber-500 hover:bg-amber-600' : 'bg-purple-600 hover:bg-purple-700' }} text-white text-sm font-semibold rounded-xl transition">
                                    {{ $grouped['push'][$cfg['public_setting']]['is_set'] ?? false ? 'Regenerate VAPID ' . $cfg['title'] : 'Generate VAPID ' . $cfg['title'] }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-amber-600">Regenerate key pada environment tertentu hanya membatalkan
                        subscription environment tersebut.</p>
                </div>
            </div>
        </div>

        {{-- ═══ ALERT & ERROR ═══ --}}
        <div x-show="tab === 'alert'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Alert & Error Monitoring
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Notifikasi error critical ke
                            owner/developer via Slack atau email.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Slack Webhook URL
                                @if ($grouped['alert']['slack_error_webhook_url']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                @endif
                            </label>
                            <input type="url" name="slack_error_webhook_url"
                                value="{{ $grouped['alert']['slack_error_webhook_url']['value'] ?? '' }}"
                                placeholder="https://hooks.slack.com/services/..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Email Penerima Error (pisahkan dengan koma)
                                @if ($grouped['alert']['error_alert_email']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                @endif
                            </label>
                            <input type="text" name="error_alert_email"
                                value="{{ $grouped['alert']['error_alert_email']['value'] ?? '' }}"
                                placeholder="admin@example.com, dev@example.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan Alert Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ APP SETTINGS ═══ --}}
        <div x-show="tab === 'app'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            App Settings
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Konfigurasi dasar aplikasi. Perubahan
                            ini override nilai di .env saat runtime.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama
                                Aplikasi</label>
                            <input type="text" name="app_name"
                                value="{{ $grouped['app']['app_name']['value'] ?? $envFallbacks['app_name'] }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">App
                                URL</label>
                            <input type="url" name="app_url"
                                value="{{ $grouped['app']['app_url']['value'] ?? $envFallbacks['app_url'] }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Timezone</label>
                            <select name="app_timezone"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @php $currentTz = $grouped['app']['app_timezone']['value'] ?? $envFallbacks['app_timezone']; @endphp
                                @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC', 'Asia/Singapore', 'Asia/Kuala_Lumpur'] as $tz)
                                    <option value="{{ $tz }}" @selected($currentTz === $tz)>
                                        {{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-4 text-xs text-amber-700">
                        <strong>Catatan:</strong> Perubahan app_name dan app_url hanya berlaku di runtime (saat
                        request). Untuk perubahan permanen, update juga file .env di server.
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan App Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ AI PROVIDER ═══ --}}
        <div x-show="tab === 'ai_provider'" x-transition x-data="{
            testResults: {},
            testingProvider: null,
            async testConnection(provider) {
                this.testingProvider = provider;
                this.testResults[provider] = { loading: true, success: null, message: '', details: null };
                try {
                    const response = await fetch('/superadmin/ai-provider/test-connection', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ provider: provider }),
                        signal: AbortSignal.timeout(10000)
                    });
                    const data = await response.json();
                    this.testResults[provider] = {
                        loading: false,
                        success: data.success,
                        message: data.message || (data.success ? 'Koneksi berhasil' : 'Koneksi gagal'),
                        details: data.details || null
                    };
                } catch (err) {
                    this.testResults[provider] = {
                        loading: false,
                        success: false,
                        message: err.name === 'TimeoutError' ? 'Timeout: tidak ada respons dalam 10 detik' : ('Error: ' + err.message),
                        details: null
                    };
                } finally {
                    this.testingProvider = null;
                }
            },
            async refreshStatus() {
                try {
                    const response = await fetch('{{ route('super-admin.settings.ai-provider.status') }}', {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.liveStatus = data.providers;
                    }
                } catch (e) {}
            },
            liveStatus: @json($aiProviderStatus)
        }">

            {{-- Provider Status Table --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Status Provider AI
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Status ketersediaan real-time setiap provider.</p>
                    </div>
                    <button type="button" @click="refreshStatus()"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th
                                    class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                    Provider</th>
                                <th
                                    class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                    Status</th>
                                <th
                                    class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                    API Key</th>
                                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2">
                                    Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach (['gemini' => 'Gemini', 'anthropic' => 'Anthropic'] as $providerKey => $providerLabel)
                                @php $ps = $aiProviderStatus[$providerKey] ?? null; @endphp
                                <tr x-data>
                                    <td class="py-3 pr-4 font-medium text-gray-800">
                                        <div class="flex items-center gap-2">
                                            @if ($providerKey === 'gemini')
                                                <span
                                                    class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600">G</span>
                                            @else
                                                <span
                                                    class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-600">A</span>
                                            @endif
                                            {{ $providerLabel }}
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <template x-if="liveStatus['{{ $providerKey }}']">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-700': liveStatus['{{ $providerKey }}']
                                                        .status_color === 'green',
                                                    'bg-amber-100 text-amber-700': liveStatus['{{ $providerKey }}']
                                                        .status_color === 'amber',
                                                    'bg-gray-100 text-gray-500': liveStatus['{{ $providerKey }}']
                                                        .status_color === 'gray'
                                                }"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold">
                                                <span
                                                    :class="{
                                                        'bg-green-500': liveStatus['{{ $providerKey }}']
                                                            .status_color === 'green',
                                                        'bg-amber-500': liveStatus['{{ $providerKey }}']
                                                            .status_color === 'amber',
                                                        'bg-gray-400': liveStatus['{{ $providerKey }}']
                                                            .status_color === 'gray'
                                                    }"
                                                    class="w-1.5 h-1.5 rounded-full"></span>
                                                <span x-text="liveStatus['{{ $providerKey }}'].status_label"></span>
                                            </span>
                                        </template>
                                        <template x-if="!liveStatus['{{ $providerKey }}']">
                                            @if (($ps['status_color'] ?? 'gray') === 'green')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    {{ $ps['status_label'] ?? 'Aktif' }}
                                                </span>
                                            @elseif (($ps['status_color'] ?? 'gray') === 'amber')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    {{ $ps['status_label'] ?? 'Cooldown' }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                    {{ $ps['status_label'] ?? 'Tidak Dikonfigurasi' }}
                                                </span>
                                            @endif
                                        </template>
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if ($ps['configured'] ?? false)
                                            <span class="text-green-600 text-xs font-medium">✓ Dikonfigurasi</span>
                                        @else
                                            <span class="text-red-500 text-xs font-medium">✗ Belum diset</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-xs text-gray-400">
                                        @if (!empty($ps['recovers_at']))
                                            Cooldown hingga: {{ $ps['recovers_at'] }}
                                        @elseif (!($ps['configured'] ?? false))
                                            Isi API key di bagian Konfigurasi di bawah
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Configuration Form --}}
            <form method="POST" action="{{ route('super-admin.settings.ai-provider.save') }}">
                @csrf
                <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Konfigurasi AI Provider
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Pengaturan provider AI global untuk seluruh platform.
                            Tenant dapat meng-override di Settings mereka.</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- Default Provider --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Default Provider
                            </label>
                            @php
                                $currentDefaultProvider =
                                    $grouped['ai_provider']['ai_default_provider']['value'] ??
                                    config('ai.default_provider', 'gemini');
                            @endphp
                            <select name="ai_default_provider"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="gemini" @selected($currentDefaultProvider === 'gemini')>Gemini (Google)</option>
                                <option value="anthropic" @selected($currentDefaultProvider === 'anthropic')>Anthropic (Claude)</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Provider yang digunakan secara default untuk semua
                                request AI.</p>
                        </div>

                        {{-- Provider Mode --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Mode Provider
                            </label>
                            @php
                                $currentProviderMode =
                                    $grouped['ai_provider']['ai_provider_mode']['value'] ??
                                    config('ai.mode', 'failover');
                            @endphp
                            <select name="ai_provider_mode"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="failover" @selected($currentProviderMode === 'failover')>Failover (otomatis beralih ke
                                    provider cadangan)</option>
                                <option value="single" @selected($currentProviderMode === 'single')>Single (hanya satu provider, tanpa
                                    fallback)</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Mode <strong>failover</strong> direkomendasikan untuk
                                ketersediaan maksimal.</p>
                        </div>

                        {{-- Fallback Order --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                Urutan Fallback Provider
                                @if ($grouped['ai_provider']['ai_provider_fallback_order']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan</span>
                                @endif
                            </label>
                            @php
                                $fallbackOrderRaw =
                                    $grouped['ai_provider']['ai_provider_fallback_order']['value'] ?? null;
                                $fallbackOrderDisplay =
                                    $fallbackOrderRaw ?:
                                    json_encode(config('ai.fallback_order', ['gemini', 'anthropic']));
                            @endphp
                            <input type="text" name="ai_provider_fallback_order"
                                value="{{ $fallbackOrderDisplay }}" placeholder='["gemini","anthropic"]'
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                            <p class="text-xs text-gray-400 mt-1">JSON array urutan provider fallback. Contoh: <code
                                    class="bg-gray-100 px-1 rounded">["gemini","anthropic"]</code></p>
                        </div>
                    </div>

                    {{-- Anthropic Configuration --}}
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-600">A</span>
                            Konfigurasi Anthropic (Claude)
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Anthropic API Key
                                    @if ($grouped['ai_provider']['anthropic_api_key']['is_set'] ?? false)
                                        <span class="ml-2 text-green-500 font-normal normal-case tracking-normal">✓
                                            Tersimpan di DB (terenkripsi)</span>
                                    @elseif (!empty(config('ai.providers.anthropic.api_key')))
                                        <span class="ml-2 text-amber-500 font-normal normal-case tracking-normal">From
                                            .env (fallback)</span>
                                    @else
                                        <span class="ml-2 text-red-500 font-normal normal-case tracking-normal">✗ Belum
                                            dikonfigurasi</span>
                                    @endif
                                </label>
                                <input type="password" name="anthropic_api_key"
                                    placeholder="{{ $grouped['ai_provider']['anthropic_api_key']['is_set'] ?? false ? '••••••••••••••••••• (sudah diset, kosongkan untuk tidak mengubah)' : 'sk-ant-api03-...' }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                                <p class="text-xs text-gray-400 mt-1">API key akan dienkripsi sebelum disimpan ke
                                    database.</p>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Anthropic Model
                                </label>
                                @php
                                    $currentAnthropicModel =
                                        $grouped['ai_provider']['anthropic_model']['value'] ??
                                        config('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
                                    $anthropicModels = [
                                        'claude-3-5-sonnet-20241022',
                                        'claude-3-5-haiku-20241022',
                                        'claude-3-haiku-20240307',
                                        'claude-3-opus-20240229',
                                    ];
                                @endphp
                                <select name="anthropic_model"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach ($anthropicModels as $m)
                                        <option value="{{ $m }}" @selected($currentAnthropicModel === $m)>
                                            {{ $m }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Model Claude yang digunakan secara default.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan Konfigurasi AI Provider
                        </button>
                    </div>
                </div>
            </form>

            {{-- Test Connection --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mt-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Test Koneksi Provider
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Kirim request uji ke setiap provider dan tampilkan hasilnya
                        dalam 10 detik.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    @foreach (['gemini' => 'Gemini', 'anthropic' => 'Anthropic'] as $providerKey => $providerLabel)
                        <div class="flex-1 min-w-[200px]">
                            <button type="button" @click="testConnection('{{ $providerKey }}')"
                                :disabled="testingProvider === '{{ $providerKey }}'"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition">
                                <template x-if="testingProvider === '{{ $providerKey }}'">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="testingProvider !== '{{ $providerKey }}'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                                <span
                                    x-text="testingProvider === '{{ $providerKey }}' ? 'Menguji...' : 'Test {{ $providerLabel }}'"></span>
                            </button>

                            <div x-show="testResults['{{ $providerKey }}'] && !testResults['{{ $providerKey }}'].loading"
                                class="mt-2">
                                <div :class="{
                                    'bg-green-50 border-green-200 text-green-700': testResults[
                                        '{{ $providerKey }}'] && testResults['{{ $providerKey }}'].success,
                                    'bg-red-50 border-red-200 text-red-700': testResults['{{ $providerKey }}'] && !
                                        testResults['{{ $providerKey }}'].success
                                }"
                                    class="p-3 rounded-xl text-xs border">
                                    <div class="font-semibold"
                                        x-text="testResults['{{ $providerKey }}'] && testResults['{{ $providerKey }}'].success ? '✓ ' + testResults['{{ $providerKey }}'].message : '✗ ' + (testResults['{{ $providerKey }}'] ? testResults['{{ $providerKey }}'].message : '')">
                                    </div>
                                    <template
                                        x-if="testResults['{{ $providerKey }}'] && testResults['{{ $providerKey }}'].details">
                                        <div class="mt-1 opacity-75"
                                            x-text="JSON.stringify(testResults['{{ $providerKey }}'].details)"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Provider Switch Logs --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mt-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Log Peralihan Provider (10 Terakhir)
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Riwayat peralihan otomatis antar provider AI.</p>
                </div>

                @if (count($aiProviderSwitchLogs) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th
                                        class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                        Waktu</th>
                                    <th
                                        class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                        Dari</th>
                                    <th
                                        class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                        Ke</th>
                                    <th
                                        class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2 pr-4">
                                        Alasan</th>
                                    <th
                                        class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide pb-2">
                                        Tenant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($aiProviderSwitchLogs as $log)
                                    <tr>
                                        <td class="py-2.5 pr-4 text-xs text-gray-500 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="py-2.5 pr-4">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                {{ ucfirst($log['from_provider']) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 pr-4">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                                {{ ucfirst($log['to_provider']) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 pr-4 text-xs text-gray-600">
                                            @php
                                                $reasonLabels = [
                                                    'rate_limit' => 'Rate Limit',
                                                    'quota_exceeded' => 'Quota Habis',
                                                    'server_error' => 'Server Error',
                                                    'manual' => 'Manual',
                                                ];
                                            @endphp
                                            {{ $reasonLabels[$log['reason']] ?? $log['reason'] }}
                                        </td>
                                        <td class="py-2.5 text-xs text-gray-400">
                                            {{ $log['tenant_id'] ? 'Tenant #' . $log['tenant_id'] : 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-sm">Belum ada log peralihan provider.</p>
                        <p class="text-xs mt-1">Log akan muncul ketika terjadi fallback otomatis antar provider.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- BUG-AI-003 FIX: JavaScript for testing Gemini API key --}}
    <script>
        function testGeminiApiKey() {
            const btn = document.getElementById('testGeminiBtn');
            const resultDiv = document.getElementById('geminiTestResult');
            const apiKeyInput = document.getElementById('gemini_api_key_input');

            // Show loading state
            btn.disabled = true;
            btn.innerHTML =
                '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';
            resultDiv.classList.remove('hidden');
            resultDiv.querySelector('div').className =
                'p-3 rounded-xl text-sm bg-blue-50 text-blue-700';
            resultDiv.querySelector('div').innerHTML = '⏳ Menguji koneksi ke Gemini API...';

            // Get API key from input or use stored one
            const apiKey = apiKeyInput.value;

            fetch('{{ route('super-admin.settings.test-gemini-api-key') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        gemini_api_key: apiKey || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success
                        resultDiv.querySelector('div').className =
                            'p-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700';
                        resultDiv.querySelector('div').innerHTML = `
                    <div class="font-semibold mb-1">✓ ${data.message}</div>
                    ${data.details ? `
                                                    <div class="text-xs mt-2 space-y-1 opacity-80">
                                                        <div>Model: ${data.details.model}</div>
                                                        <div>API Key: ${data.details.api_key_prefix}</div>
                                                        <div>Response: ${data.details.response}</div>
                                                    </div>
                                                ` : ''}
                `;
                    } else {
                        // Error
                        resultDiv.querySelector('div').className =
                            'p-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';
                        resultDiv.querySelector('div').innerHTML = `
                    <div class="font-semibold mb-1">✗ ${data.message}</div>
                    ${data.details ? `
                                                    <div class="text-xs mt-2 opacity-80">
                                                        Status: ${data.details.status_code || 'N/A'}<br>
                                                        ${data.details.error ? `Error: ${data.details.error.substring(0, 200)}${data.details.error.length > 200 ? '...' : ''}` : ''}
                                                    </div>
                                                ` : ''}
                `;
                    }
                })
                .catch(error => {
                    resultDiv.querySelector('div').className =
                        'p-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';
                    resultDiv.querySelector('div').innerHTML = `
                <div class="font-semibold mb-1">✗ Gagal terhubung ke server</div>
                <div class="text-xs mt-2 opacity-80">${error.message}</div>
            `;
                })
                .finally(() => {
                    // Reset button
                    btn.disabled = false;
                    btn.innerHTML =
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Test API Key';
                });
        }
    </script>
</x-app-layout>
