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
                    Aktifkan atau nonaktifkan modul sesuai kebutuhan bisnis Anda. Modul yang dinonaktifkan tidak akan muncul di menu.
                </p>
            </div>
            <span class="shrink-0 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full font-medium">
                {{ count($enabled) }} / {{ count($all) }} aktif
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.modules.update') }}">
        @csrf
        @method('PUT')

        @php
        $groups = [
            'Penjualan & Operasional' => ['pos', 'sales', 'invoicing', 'crm', 'ecommerce', 'loyalty'],
            'Inventori & Produksi'    => ['inventory', 'purchasing', 'production'],
            'SDM & Keuangan'          => ['hrm', 'payroll', 'accounting', 'budget', 'bank_reconciliation', 'assets'],
            'Manajemen & Analitik'    => ['projects', 'reports'],
        ];
        @endphp

        @foreach($groups as $groupName => $keys)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-4">{{ $groupName }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($keys as $key)
                @php $m = $meta[$key]; $isEnabled = in_array($key, $enabled); @endphp
                <label class="module-card flex items-center gap-4 p-4 rounded-xl border-2 cursor-pointer transition
                    {{ $isEnabled ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/50' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                    <input type="checkbox" name="modules[]" value="{{ $key }}"
                        class="sr-only module-checkbox"
                        {{ $isEnabled ? 'checked' : '' }}
                        onchange="updateCard(this)">
                    <span class="text-2xl shrink-0">{{ $m['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm text-gray-900 dark:text-white">{{ $m['label'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $m['desc'] }}</div>
                    </div>
                    <div class="shrink-0">
                        <div class="w-10 h-5 rounded-full transition-colors {{ $isEnabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-white/20' }} relative toggle-track">
                            <div class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform {{ $isEnabled ? 'translate-x-5' : 'translate-x-0.5' }} toggle-thumb"></div>
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
        if (!cb.checked) { cb.checked = true; updateCard(cb); }
    });
}

function disableAll() {
    document.querySelectorAll('.module-checkbox').forEach(cb => {
        if (cb.checked) { cb.checked = false; updateCard(cb); }
    });
}
</script>
@endsection
