@extends('layouts.app')

@section('title', 'Pengaturan Modul')

<x-slot name="header">Pengaturan Modul</x-slot>

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Modul Aktif</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Aktifkan atau nonaktifkan modul sesuai kebutuhan bisnis Anda. Modul yang dinonaktifkan tidak akan
                        muncul di menu.
                    </p>
                </div>
                <span
                    class="shrink-0 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full font-medium">
                    {{ count($enabled) }} / {{ count($all) }} aktif
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.modules.update') }}" id="module-form">
            @csrf
            @method('PUT')

            {{-- BUG-SET-002 FIX: Cleanup Strategy Selector --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Strategi Cleanup Data</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                            Pilih bagaimana data modul yang dinonaktifkan akan ditangani.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy', 'keep') === 'keep' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300' }}"
                        onclick="selectStrategy('keep')">
                        <input type="radio" name="cleanup_strategy" value="keep" class="sr-only"
                            {{ old('cleanup_strategy', 'keep') === 'keep' ? 'checked' : '' }}>
                        <div class="text-2xl">💾</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Simpan Data</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Data tetap di database, hanya hide
                                dari UI</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy') === 'archive' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300' }}"
                        onclick="selectStrategy('archive')">
                        <input type="radio" name="cleanup_strategy" value="archive" class="sr-only"
                            {{ old('cleanup_strategy') === 'archive' ? 'checked' : '' }}>
                        <div class="text-2xl">📦</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Archive Data</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Pindah ke tabel archive, bisa
                                di-restore</div>
                        </div>
                    </label>

                    <label
                        class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                        {{ old('cleanup_strategy') === 'soft_delete' ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : 'border-gray-200 dark:border-white/10 hover:border-gray-300' }}"
                        onclick="selectStrategy('soft_delete')">
                        <input type="radio" name="cleanup_strategy" value="soft_delete" class="sr-only"
                            {{ old('cleanup_strategy') === 'soft_delete' ? 'checked' : '' }}>
                        <div class="text-2xl">🗑️</div>
                        <div class="flex-1">
                            <div class="font-medium text-sm text-gray-900 dark:text-white">Soft Delete</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Mark as deleted, tetap bisa
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
                ];
            @endphp

            @foreach ($groups as $groupName => $keys)
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                        {{ $groupName }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($keys as $key)
                            @php
                                $m = $meta[$key];
                                $isEnabled = in_array($key, $enabled);
                            @endphp
                            <label
                                class="module-card flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition
                    {{ $isEnabled ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/50' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                                <input type="checkbox" name="modules[]" value="{{ $key }}"
                                    class="sr-only module-checkbox" {{ $isEnabled ? 'checked' : '' }}
                                    onchange="updateCard(this)">
                                <span class="text-2xl shrink-0">{{ $m['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">{{ $m['label'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $m['desc'] }}</div>
                                </div>
                                <div class="shrink-0">
                                    <div
                                        class="w-10 h-5 rounded-full transition-colors {{ $isEnabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-white/20' }} relative toggle-track">
                                        <div
                                            class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform {{ $isEnabled ? 'translate-x-5' : 'translate-x-0.5' }} toggle-thumb">
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-between">
                <div class="flex gap-4">
                    <button type="button" onclick="enableAll()"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        Aktifkan semua
                    </button>
                    <button type="button" onclick="disableAll()"
                        class="text-sm text-gray-500 dark:text-slate-400 hover:underline">
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
                    label.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                    label.classList.remove('border-gray-200', 'dark:border-white/10');
                } else {
                    label.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-500/10');
                    label.classList.add('border-gray-200', 'dark:border-white/10');
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
                track.classList.remove('dark:bg-white/20');
                thumb.classList.replace('translate-x-0.5', 'translate-x-5');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-gray-200');
                label.removeAttribute('data-enabled');
                track.classList.replace('bg-blue-600', 'bg-gray-300');
                track.classList.add('dark:bg-white/20');
                thumb.classList.replace('translate-x-5', 'translate-x-0.5');
            }
        }

        function enableAll() {
            document.querySelectorAll('.module-checkbox').forEach(cb => {
                if (!cb.checked) {
                    cb.checked = true;
                    updateCard(cb);
                }
            });
        }

        function disableAll() {
            document.querySelectorAll('.module-checkbox').forEach(cb => {
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
