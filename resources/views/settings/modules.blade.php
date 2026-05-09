@extends('layouts.app')

@section('title', 'Pengaturan Modul')

<x-slot name="header">Pengaturan Modul</x-slot>

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Flash messages --}}
        @if (session('success'))
            <div
                class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 text-sm">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-4 text-sm">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <p class="font-medium">Tidak dapat menyimpan pengaturan</p>
                    <p class="mt-0.5 text-red-700">{{ session('error') }}</p>
                    @if (session('upgrade_required'))
                        <a href="{{ route('subscription.index') }}"
                            class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            Upgrade Paket Sekarang
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Modul Aktif</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Aktifkan atau nonaktifkan modul sesuai kebutuhan bisnis Anda. Modul yang dinonaktifkan tidak akan
                        muncul di menu.
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium">
                        {{ count($enabled) }} / {{ count($all) }} aktif
                    </span>
                    @php
                        $planLabels = [
                            'trial' => ['label' => 'Trial 14 Hari', 'color' => 'yellow'],
                            'starter' => ['label' => 'Starter', 'color' => 'gray'],
                            'business' => ['label' => 'Business', 'color' => 'blue'],
                            'professional' => ['label' => 'Professional', 'color' => 'purple'],
                            'enterprise' => ['label' => 'Enterprise', 'color' => 'green'],
                        ];
                        $planInfo = $planLabels[$planSlug] ?? [
                            'label' => strtoupper($planSlug ?? 'Unknown'),
                            'color' => 'gray',
                        ];
                        $colorMap = [
                            'yellow' => 'bg-yellow-100 text-yellow-700',
                            'gray' => 'bg-gray-100 text-gray-600',
                            'blue' => 'bg-blue-100 text-blue-700',
                            'purple' => 'bg-purple-100 text-purple-700',
                            'green' => 'bg-green-100 text-green-700',
                        ];
                    @endphp
                    <span class="text-xs px-3 py-1 rounded-full font-medium {{ $colorMap[$planInfo['color']] }}">
                        Paket: {{ $planInfo['label'] }}
                    </span>
                </div>
            </div>

            {{-- Trial upgrade banner --}}
            @if ($planSlug === 'trial')
                <div class="mt-4 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-amber-800 flex-1">
                        Paket trial hanya mencakup modul dasar. Modul dengan ikon 🔒 memerlukan upgrade paket.
                    </p>
                    <a href="{{ route('subscription.index') }}"
                        class="shrink-0 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg transition">
                        Upgrade
                    </a>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('settings.modules.update') }}" id="module-form">
            @csrf
            @method('PUT')

            {{-- BUG-SET-002 FIX: Cleanup Strategy Selector --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Strategi Cleanup Data</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Pilih bagaimana data modul yang dinonaktifkan akan ditangani.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy', 'keep') === 'keep' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                        onclick="selectStrategy('keep')">
                        <input type="radio" name="cleanup_strategy" value="keep" class="sr-only"
                            {{ old('cleanup_strategy', 'keep') === 'keep' ? 'checked' : '' }}>
                        <div class="text-2xl">💾</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900">Simpan Data</div>
                            <div class="text-xs text-gray-500 mt-0.5">Data tetap di database, hanya hide
                                dari UI</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy') === 'archive' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                        onclick="selectStrategy('archive')">
                        <input type="radio" name="cleanup_strategy" value="archive" class="sr-only"
                            {{ old('cleanup_strategy') === 'archive' ? 'checked' : '' }}>
                        <div class="text-2xl">📦</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900">Archive Data</div>
                            <div class="text-xs text-gray-500 mt-0.5">Pindah ke tabel archive, bisa
                                di-restore</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy') === 'soft_delete' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                        onclick="selectStrategy('soft_delete')">
                        <input type="radio" name="cleanup_strategy" value="soft_delete" class="sr-only"
                            {{ old('cleanup_strategy') === 'soft_delete' ? 'checked' : '' }}>
                        <div class="text-2xl">🗑️</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900">Soft Delete</div>
                            <div class="text-xs text-gray-500 mt-0.5">Mark as deleted, tetap bisa
                                di-restore</div>
                        </div>
                    </label>
                </div>
            </div>

            @php
                $groups = [
                    'Penjualan & Operasional' => [
                        'pos',
                        'sales',
                        'invoicing',
                        'crm',
                        'contracts',
                        'ecommerce',
                        'loyalty',
                        'commission',
                        'helpdesk',
                        'subscription_billing',
                    ],
                    'Inventori & Produksi' => [
                        'inventory',
                        'purchasing',
                        'production',
                        'manufacturing',
                        'fleet',
                        'consignment',
                        'wms',
                    ],
                    'SDM & Keuangan' => [
                        'hrm',
                        'payroll',
                        'accounting',
                        'budget',
                        'bank_reconciliation',
                        'assets',
                        'landed_cost',
                        'reimbursement',
                    ],
                    'Manajemen & Analitik' => ['projects', 'reports', 'project_billing'],
                    'Industri & Vertikal' => ['hotel', 'fnb', 'spa', 'agriculture', 'livestock', 'telecom'],
                    'Healthcare & SimRS' => ['healthcare'],
                    'Tour, Konstruksi & Industri Lain' => ['tour_travel', 'construction', 'cosmetic', 'printing'],
                ];
            @endphp

            @foreach ($groups as $groupName => $keys)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                        {{ $groupName }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($keys as $key)
                            @php
                                $m = $meta[$key];
                                $isEnabled = in_array($key, $enabled);
                                $isLocked = !in_array($key, $allowedByPlan);
                            @endphp
                            <label
                                class="module-card flex items-center gap-4 p-4 rounded-xl border-2 transition
                                {{ $isLocked
                                    ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed'
                                    : 'cursor-pointer ' . ($isEnabled ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300') }}">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    class="sr-only module-checkbox" {{ $isEnabled && !$isLocked ? 'checked' : '' }}
                                    {{ $isLocked ? 'disabled' : '' }} onchange="updateCard(this)">
                                <span class="text-2xl shrink-0">{{ $m['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-gray-900 flex items-center gap-1.5">
                                        {{ $m['label'] }}
                                        @if ($isLocked)
                                            <span
                                                class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Upgrade</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $m['desc'] }}</div>
                                </div>
                                <div class="shrink-0">
                                    @if ($isLocked)
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    @else
                                        <div
                                            class="w-10 h-5 rounded-full transition-colors {{ $isEnabled ? 'bg-blue-600' : 'bg-gray-300' }} relative toggle-track">
                                            <div
                                                class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform {{ $isEnabled ? 'translate-x-5' : 'translate-x-0.5' }} toggle-thumb">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-between">
                <div class="flex gap-4">
                    <button type="button" onclick="enableAll()" class="text-sm text-blue-600 hover:underline">
                        Aktifkan semua
                    </button>
                    <button type="button" onclick="disableAll()" class="text-sm text-gray-500 hover:underline">
                        Nonaktifkan semua
                    </button>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>

    </div>

    <script>
        // BUG-SET-002 FIX: Strategy selector
        function selectStrategy(strategy) {
            document.querySelectorAll('input[name="cleanup_strategy"]').forEach(radio => {
                radio.checked = radio.value === strategy;
            });

            // Update UI
            document.querySelectorAll('[onclick^="selectStrategy"]').forEach(label => {
                const isSelected = label.getAttribute('onclick').includes(strategy);
                if (isSelected) {
                    label.classList.add('border-blue-500', 'bg-blue-50');
                    label.classList.remove('border-gray-200');
                } else {
                    label.classList.remove('border-blue-500', 'bg-blue-50');
                    label.classList.add('border-gray-200');
                }
            });
        }

        function updateCard(checkbox) {
            const label = checkbox.closest('label');
            const track = label.querySelector('.toggle-track');
            const thumb = label.querySelector('.toggle-thumb');
            if (checkbox.checked) {
                label.classList.add('border-blue-500', 'bg-blue-50');
                label.classList.remove('border-gray-200');
                label.setAttribute('data-enabled', '1');
                track.classList.replace('bg-gray-300', 'bg-blue-600');
                thumb.classList.replace('translate-x-0.5', 'translate-x-5');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-gray-200');
                label.removeAttribute('data-enabled');
                track.classList.replace('bg-blue-600', 'bg-gray-300');
                thumb.classList.replace('translate-x-5', 'translate-x-0.5');
            }
        }

        function enableAll() {
            document.querySelectorAll('.module-checkbox:not(:disabled)').forEach(cb => {
                if (!cb.checked) {
                    cb.checked = true;
                    updateCard(cb);
                }
            });
        }

        function disableAll() {
            document.querySelectorAll('.module-checkbox:not(:disabled)').forEach(cb => {
                if (cb.checked) {
                    cb.checked = false;
                    updateCard(cb);
                }
            });
        }

        // BUG-SET-002 FIX: Show impact analysis on form submit
        document.getElementById('module-form').addEventListener('submit', function(e) {
            const checkedModules = Array.from(document.querySelectorAll('.module-checkbox:checked'))
                .map(cb => cb.value);

            const currentModules = @json($enabled);
            const disabledModules = currentModules.filter(m => !checkedModules.includes(m));

            if (disabledModules.length > 0) {
                const message = `Anda akan menonaktifkan ${disabledModules.length} modul:\n` +
                    disabledModules.map(m => `• ${m}`).join('\n') +
                    '\n\nData akan ditangani sesuai strategi yang dipilih.';

                if (!confirm(message)) {
                    e.preventDefault();
                }
            }
        });
    </script>
@endsection
