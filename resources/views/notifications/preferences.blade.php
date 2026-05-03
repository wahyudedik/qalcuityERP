@extends('layouts.app')
@section('title', 'Preferensi Notifikasi')

@section('content')
    <div class="p-6 space-y-8 max-w-4xl">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('notifications.index') }}"
                        class="text-sm text-blue-500 hover:text-blue-600 transition">← Pusat Notifikasi</a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Preferensi Notifikasi</h1>
                <p class="text-sm text-gray-500 mt-1">Pilih jenis notifikasi yang ingin Anda terima dan
                    melalui channel apa.</p>
            </div>
        </div>

        @if (session('success'))
            <div
                class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('notifications.preferences.update') }}" class="space-y-6">
            @csrf

            @php
                $moduleLabels = [
                    'inventory' => ['label' => 'Inventori', 'icon' => '📦'],
                    'finance' => ['label' => 'Keuangan', 'icon' => '💰'],
                    'hrm' => ['label' => 'HRM', 'icon' => '👥'],
                    'ai' => ['label' => 'AI', 'icon' => '🤖'],
                    'system' => ['label' => 'Sistem', 'icon' => '⚙️'],
                ];
            @endphp

            {{-- Notification Types by Module --}}
            @foreach ($availableTypes as $module => $types)
                <div
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <span>{{ $moduleLabels[$module]['icon'] ?? '🔔' }}</span>
                            <span>{{ $moduleLabels[$module]['label'] ?? ucfirst($module) }}</span>
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th
                                        class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 w-full">
                                        Tipe Notifikasi</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 text-center whitespace-nowrap">
                                        In-App</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 text-center whitespace-nowrap">
                                        Email</th>
                                    <th
                                        class="px-4 py-2.5 text-xs font-medium text-gray-500 text-center whitespace-nowrap">
                                        Push</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($types as $type => $label)
                                    @php
                                        $pref = $userPrefs->get($type);
                                        $inApp = $pref ? $pref->in_app : true;
                                        $email = $pref ? $pref->email : true;
                                        $push = $pref ? $pref->push : true;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-5 py-3 text-gray-800 font-medium">
                                            {{ $label }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[{{ $type }}][in_app]"
                                                    value="1" {{ $inApp ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[{{ $type }}][email]"
                                                    value="1" {{ $email ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="preferences[{{ $type }}][push]"
                                                    value="1" {{ $push ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- Digest Preferences --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <span>📋</span>
                        <span>Pengaturan Ringkasan (Digest)</span>
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5">Terima ringkasan aktivitas secara berkala
                        melalui email.</p>
                </div>

                <div class="p-5 space-y-5">
                    {{-- Frequency --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Frekuensi</label>
                        <select name="digest_frequency" id="digestFrequency" onchange="toggleDigestDay(this.value)"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="daily"
                                {{ ($user->digest_frequency ?? 'weekly') === 'daily' ? 'selected' : '' }}>Harian</option>
                            <option value="weekly"
                                {{ ($user->digest_frequency ?? 'weekly') === 'weekly' ? 'selected' : '' }}>Mingguan
                            </option>
                            <option value="monthly"
                                {{ ($user->digest_frequency ?? 'weekly') === 'monthly' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="off"
                                {{ ($user->digest_frequency ?? 'weekly') === 'off' ? 'selected' : '' }}>Nonaktif
                            </option>
                        </select>
                    </div>

                    {{-- Day selector (only for weekly) --}}
                    <div id="digestDayWrapper"
                        class="{{ ($user->digest_frequency ?? 'weekly') !== 'weekly' ? 'hidden' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Hari
                            Pengiriman</label>
                        <select name="digest_day"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach (['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'] as $val => $dayLabel)
                                <option value="{{ $val }}"
                                    {{ ($user->digest_day ?? 'friday') === $val ? 'selected' : '' }}>{{ $dayLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Time --}}
                    <div id="digestTimeWrapper"
                        class="{{ ($user->digest_frequency ?? 'weekly') === 'off' ? 'hidden' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Waktu
                            Pengiriman</label>
                        <input type="time" name="digest_time" value="{{ $user->digest_time ?? '17:00' }}"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Zona waktu server digunakan.</p>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end gap-3 pb-4">
                <a href="{{ route('notifications.index') }}"
                    class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                    Simpan Preferensi
                </button>
            </div>

        </form>
    </div>

    <script>
        function toggleDigestDay(value) {
            const dayWrapper = document.getElementById('digestDayWrapper');
            const timeWrapper = document.getElementById('digestTimeWrapper');
            dayWrapper.classList.toggle('hidden', value !== 'weekly');
            timeWrapper.classList.toggle('hidden', value === 'off');
        }
    </script>
@endsection
