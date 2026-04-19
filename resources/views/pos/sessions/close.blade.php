@extends('layouts.app')

@section('title', 'Tutup Sesi Kasir')

@section('content')
<div class="p-4 sm:p-6 max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <a href="{{ route('pos.sessions.show', $session) }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Detail Sesi
        </a>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Tutup Sesi Kasir</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Kasir: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $session->cashier?->name }}</span>
            &bull; Dibuka: {{ $session->opened_at?->format('d/m/Y H:i') }}
        </p>
    </div>

    {{-- Rekap Transaksi --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Rekap Transaksi Sesi Ini</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            {{-- Total Transaksi --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Jumlah Transaksi</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($recap['total_transactions']) }}</p>
            </div>

            {{-- Total Penjualan --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Total Penjualan</p>
                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($recap['total_sales'], 0, ',', '.') }}</p>
            </div>

            {{-- Modal Awal --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Modal Awal</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($session->opening_balance, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Rincian per metode pembayaran --}}
        <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Rincian per Metode Pembayaran</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span> Tunai (Cash)
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($recap['total_cash'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Kartu Debit/Kredit
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($recap['total_card'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span> QRIS
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($recap['total_qris'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span> Transfer Bank
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($recap['total_transfer'], 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-2 flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Diskon</span>
                    <span class="font-medium text-red-600 dark:text-red-400">- Rp {{ number_format($recap['total_discount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Pajak</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($recap['total_tax'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Kas yang diharapkan --}}
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Kas yang Diharapkan di Laci</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Modal awal + total penjualan tunai</p>
                </div>
                <p class="text-xl font-bold text-amber-800 dark:text-amber-300">
                    Rp {{ number_format($recap['expected_balance'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Form Tutup Sesi --}}
    <form method="POST" action="{{ route('pos.sessions.close', $session) }}"
        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5"
        x-data="{ closingBalance: {{ (int) $recap['expected_balance'] }}, expectedBalance: {{ (int) $recap['expected_balance'] }} }">
        @csrf

        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Input Kas Aktual</h2>

        {{-- Kas Aktual --}}
        <div>
            <label for="closing_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Jumlah Kas di Laci (Aktual) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                <input type="number" id="closing_balance" name="closing_balance"
                    x-model="closingBalance"
                    value="{{ old('closing_balance', (int) $recap['expected_balance']) }}"
                    min="0" step="1000" required
                    class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
            </div>
            @error('closing_balance')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Selisih (dihitung otomatis) --}}
        <div class="rounded-xl p-4 transition"
            :class="(closingBalance - expectedBalance) === 0
                ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
                : (closingBalance - expectedBalance) > 0
                    ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800'
                    : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
            <div class="flex justify-between items-center">
                <p class="text-sm font-medium"
                    :class="(closingBalance - expectedBalance) === 0
                        ? 'text-green-800 dark:text-green-300'
                        : (closingBalance - expectedBalance) > 0
                            ? 'text-blue-800 dark:text-blue-300'
                            : 'text-red-800 dark:text-red-300'">
                    Selisih Kas
                </p>
                <p class="text-lg font-bold"
                    :class="(closingBalance - expectedBalance) === 0
                        ? 'text-green-800 dark:text-green-300'
                        : (closingBalance - expectedBalance) > 0
                            ? 'text-blue-800 dark:text-blue-300'
                            : 'text-red-800 dark:text-red-300'">
                    <span x-text="(closingBalance - expectedBalance) >= 0 ? '+' : ''"></span>
                    Rp <span x-text="new Intl.NumberFormat('id-ID').format(closingBalance - expectedBalance)"></span>
                </p>
            </div>
            <p class="text-xs mt-1"
                :class="(closingBalance - expectedBalance) === 0
                    ? 'text-green-600 dark:text-green-400'
                    : (closingBalance - expectedBalance) > 0
                        ? 'text-blue-600 dark:text-blue-400'
                        : 'text-red-600 dark:text-red-400'"
                x-text="(closingBalance - expectedBalance) === 0
                    ? 'Kas sesuai — tidak ada selisih'
                    : (closingBalance - expectedBalance) > 0
                        ? 'Kelebihan kas'
                        : 'Kekurangan kas'">
            </p>
        </div>

        {{-- Catatan --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Catatan Penutupan (opsional)
            </label>
            <textarea id="notes" name="notes" rows="2"
                placeholder="Catatan untuk sesi ini..."
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition resize-none">{{ old('notes', $session->notes) }}</textarea>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3 pt-2">
            <a href="{{ route('pos.sessions.show', $session) }}"
                class="flex-1 text-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Batal
            </a>
            <button type="submit"
                onclick="return confirm('Yakin ingin menutup sesi kasir ini? Tindakan ini tidak dapat dibatalkan.')"
                class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition">
                Tutup Sesi Kasir
            </button>
        </div>
    </form>

</div>
@endsection
