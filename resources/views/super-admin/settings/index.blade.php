<x-app-layout>
    <x-slot name="title">System Settings — Qalcuity ERP</x-slot>
    <x-slot name="header">System Settings — SuperAdmin</x-slot>

    @if (session('success'))
        <div
            class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-2xl text-sm text-green-700 dark:text-green-300 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-2xl text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ tab: 'ai' }" class="space-y-6">

        {{-- Tab Navigation --}}
        <div class="flex flex-wrap gap-1 bg-gray-100 dark:bg-white/5 rounded-2xl p-1.5">
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
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ?
                        'bg-white dark:bg-white/10 text-gray-900 dark:text-white shadow-sm' :
                        'text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200'"
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
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-6">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Konfigurasi AI / Gemini
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">API key Gemini digunakan oleh semua
                            tenant. Biaya ditanggung oleh owner/platform.</p>
                    </div>

                    {{-- API Key --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                Gemini API Key <span class="text-red-400">*</span>
                                @if ($grouped['ai']['gemini_api_key']['is_set'] ?? false)
                                    <span class="ml-2 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan di DB</span>
                                @elseif($envFallbacks['gemini_api_key'])
                                    <span class="ml-2 text-amber-500 font-normal normal-case tracking-normal">⚠ Dari
                                        .env (fallback)</span>
                                @else
                                    <span class="ml-2 text-red-500 font-normal normal-case tracking-normal">✗ Belum
                                        dikonfigurasi</span>
                                @endif
                            </label>
                            <input type="password" name="gemini_api_key"
                                placeholder="{{ $grouped['ai']['gemini_api_key']['is_set'] ?? false ? '••••••••••••••••••• (sudah diset, kosongkan untuk tidak mengubah)' : 'AIzaSy...' }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Model</label>
                            <select name="gemini_model"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Timeout
                                (detik)</label>
                            <input type="number" name="gemini_timeout" min="10" max="300"
                                value="{{ $grouped['ai']['gemini_timeout']['value'] ?? config('gemini.timeout', 60) }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Cache & Performance --}}
                    <div class="border-t border-gray-100 dark:border-white/10 pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-slate-300 mb-3">Optimasi Cache &
                            Performa</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3 bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                                <input type="hidden" name="ai_response_cache_enabled" value="0">
                                <input type="checkbox" name="ai_response_cache_enabled" id="ai_cache" value="1"
                                    @checked(($grouped['ai']['ai_response_cache_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_cache" class="text-sm text-gray-700 dark:text-slate-300">Cache Response
                                    AI</label>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                                <input type="hidden" name="ai_rule_based_enabled" value="0">
                                <input type="checkbox" name="ai_rule_based_enabled" id="ai_rule" value="1"
                                    @checked(($grouped['ai']['ai_rule_based_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_rule" class="text-sm text-gray-700 dark:text-slate-300">Rule-Based
                                    Response</label>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                                <input type="hidden" name="ai_streaming_enabled" value="0">
                                <input type="checkbox" name="ai_streaming_enabled" id="ai_stream" value="1"
                                    @checked(($grouped['ai']['ai_streaming_enabled']['value'] ?? '1') == '1')
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="ai_stream" class="text-sm text-gray-700 dark:text-slate-300">Streaming
                                    AI</label>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4 mt-3">
                            @foreach ([['ai_cache_short_ttl', 'Cache TTL Pendek (detik)', '300'], ['ai_cache_default_ttl', 'Cache TTL Default (detik)', '3600'], ['ai_cache_long_ttl', 'Cache TTL Panjang (detik)', '86400']] as [$k, $lbl, $def])
                                <div>
                                    <label
                                        class="block text-xs text-gray-500 dark:text-slate-400 mb-1">{{ $lbl }}</label>
                                    <input type="number" name="{{ $k }}"
                                        value="{{ $grouped['ai'][$k]['value'] ?? $def }}"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            @endforeach
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
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Konfigurasi Email SMTP
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Digunakan untuk email system:
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
                                    class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                    {{ $field['label'] }}
                                    @if ($grouped['mail'][$key]['is_set'] ?? false)
                                        <span
                                            class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                    @endif
                                </label>
                                <input type="{{ $field['type'] }}" name="{{ $key }}"
                                    @if (!$field['encrypt']) value="{{ $grouped['mail'][$key]['value'] ?? ($envFallbacks[$key] ?? '') }}" @endif
                                    placeholder="{{ $field['encrypt'] && ($grouped['mail'][$key]['is_set'] ?? false) ? '••• tersimpan — kosongkan untuk tidak mengubah •••' : $field['placeholder'] }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        @endforeach
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Enkripsi</label>
                            <select name="mail_encryption"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @php $currentEnc = $grouped['mail']['mail_encryption']['value'] ?? config('mail.mailers.smtp.encryption', 'tls'); @endphp
                                <option value="tls" @selected($currentEnc === 'tls')>TLS (Recommended)</option>
                                <option value="ssl" @selected($currentEnc === 'ssl')>SSL</option>
                                <option value="starttls" @selected($currentEnc === 'starttls')>STARTTLS</option>
                                <option value="" @selected($currentEnc === '' || $currentEnc === null)>None</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-white/10">
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                            Simpan SMTP
                        </button>
                        {{-- Test Email --}}
                        <form method="POST" action="{{ route('super-admin.settings.test-mail') }}"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="email" name="test_email" placeholder="test@email.com"
                                class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 w-52">
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
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Google OAuth (Login dengan Google)
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Diperlukan agar tombol "Login dengan
                            Google" berfungsi. Daftarkan di Google Cloud Console.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                Client ID
                                @if ($grouped['oauth']['google_client_id']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan</span>
                                @elseif($envFallbacks['google_client_id'])
                                    <span class="ml-1 text-amber-500 font-normal normal-case tracking-normal">⚠ Dari
                                        .env</span>
                                @endif
                            </label>
                            <input type="text" name="google_client_id"
                                value="{{ $grouped['oauth']['google_client_id']['value'] ?? '' }}"
                                placeholder="123456789-abc.apps.googleusercontent.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                Client Secret
                                @if ($grouped['oauth']['google_client_secret']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                        Tersimpan</span>
                                @endif
                            </label>
                            <input type="password" name="google_client_secret"
                                placeholder="{{ $grouped['oauth']['google_client_secret']['is_set'] ?? false ? '••• tersimpan — kosongkan untuk tidak mengubah •••' : 'GOCSPX-...' }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                        </div>
                    </div>
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-xs text-blue-700 dark:text-blue-300 space-y-1">
                        <p class="font-semibold">Cara Setup Google OAuth:</p>
                        <ol class="list-decimal list-inside space-y-0.5">
                            <li>Buka <a href="https://console.cloud.google.com/" target="_blank"
                                    class="underline">Google Cloud Console</a></li>
                            <li>Buat project baru atau pilih yang ada</li>
                            <li>Aktifkan "Google+ API" atau "Google Identity"</li>
                            <li>Buat OAuth 2.0 Credentials</li>
                            <li>Tambahkan Authorized redirect URI: <code
                                    class="bg-blue-100 dark:bg-blue-900 px-1 rounded">{{ config('app.url') }}/auth/google/callback</code>
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
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Push Notification (VAPID Keys)
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">VAPID keys diperlukan untuk fitur push
                        notification browser. Generate sekali, berlaku permanen.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                            Public Key
                            @if ($grouped['push']['vapid_public_key']['is_set'] ?? false)
                                <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓
                                    Tersimpan</span>
                            @elseif($envFallbacks['vapid_public_key'])
                                <span class="ml-1 text-amber-500 font-normal normal-case tracking-normal">⚠ Dari
                                    .env</span>
                            @else
                                <span class="ml-1 text-red-500 font-normal normal-case tracking-normal">✗ Belum
                                    di-generate</span>
                            @endif
                        </label>
                        <input type="text" readonly
                            value="{{ $grouped['push']['vapid_public_key']['value'] ?? config('services.vapid.public_key', '') }}"
                            placeholder="Generate VAPID keys terlebih dahulu..."
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 text-xs text-gray-600 dark:text-slate-400 font-mono cursor-default">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Private
                            Key</label>
                        <input type="text" readonly
                            value="{{ $grouped['push']['vapid_private_key']['is_set'] ?? false ? '••••••••••••••••••••••••••••••••••••••••••••' : 'Belum di-generate' }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 text-xs text-gray-600 dark:text-slate-400 font-mono cursor-default">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-white/10">
                    <form method="POST" action="{{ route('super-admin.settings.regenerate-vapid') }}"
                        onsubmit="return confirm('Generate ulang VAPID keys? Semua push subscription yang ada akan tidak valid dan user perlu subscribe ulang.')">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 {{ $grouped['push']['vapid_public_key']['is_set'] ?? false ? 'bg-amber-500 hover:bg-amber-600' : 'bg-purple-600 hover:bg-purple-700' }} text-white text-sm font-semibold rounded-xl transition">
                            {{ $grouped['push']['vapid_public_key']['is_set'] ?? false ? 'Regenerate VAPID Keys' : 'Generate VAPID Keys' }}
                        </button>
                    </form>
                    @if ($grouped['push']['vapid_public_key']['is_set'] ?? false)
                        <p class="text-xs text-amber-600 dark:text-amber-400">⚠ Regenerate akan membatalkan semua push
                            subscription yang ada!</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ ALERT & ERROR ═══ --}}
        <div x-show="tab === 'alert'" x-transition>
            <form method="POST" action="{{ route('super-admin.settings.update') }}">
                @csrf @method('PUT')
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Alert & Error Monitoring
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Notifikasi error critical ke
                            owner/developer via Slack atau email.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                Slack Webhook URL
                                @if ($grouped['alert']['slack_error_webhook_url']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                @endif
                            </label>
                            <input type="url" name="slack_error_webhook_url"
                                value="{{ $grouped['alert']['slack_error_webhook_url']['value'] ?? '' }}"
                                placeholder="https://hooks.slack.com/services/..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                                Email Penerima Error (pisahkan dengan koma)
                                @if ($grouped['alert']['error_alert_email']['is_set'] ?? false)
                                    <span class="ml-1 text-green-500 font-normal normal-case tracking-normal">✓</span>
                                @endif
                            </label>
                            <input type="text" name="error_alert_email"
                                value="{{ $grouped['alert']['error_alert_email']['value'] ?? '' }}"
                                placeholder="admin@example.com, dev@example.com"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            App Settings
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Konfigurasi dasar aplikasi. Perubahan
                            ini override nilai di .env saat runtime.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Nama
                                Aplikasi</label>
                            <input type="text" name="app_name"
                                value="{{ $grouped['app']['app_name']['value'] ?? $envFallbacks['app_name'] }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">App
                                URL</label>
                            <input type="url" name="app_url"
                                value="{{ $grouped['app']['app_url']['value'] ?? $envFallbacks['app_url'] }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Timezone</label>
                            <select name="app_timezone"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @php $currentTz = $grouped['app']['app_timezone']['value'] ?? $envFallbacks['app_timezone']; @endphp
                                @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'UTC', 'Asia/Singapore', 'Asia/Kuala_Lumpur'] as $tz)
                                    <option value="{{ $tz }}" @selected($currentTz === $tz)>
                                        {{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 text-xs text-amber-700 dark:text-amber-300">
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

    </div>
</x-app-layout>
