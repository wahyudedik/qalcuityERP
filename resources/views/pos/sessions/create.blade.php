@extends('layouts.app')

@section('title', 'Buka Sesi Kasir')

@section('content')
<div class="p-4 sm:p-6 max-w-lg mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <a href="{{ route('pos.sessions.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Buka Sesi Kasir</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Masukkan modal awal dan informasi kasir</p>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('pos.sessions.store') }}"
        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        @csrf

        {{-- Nama Kasir (readonly) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kasir</label>
            <input type="text" value="{{ auth()->user()->name }}" readonly
                class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-not-allowed">
        </div>

        {{-- Nama Register --}}
        <div>
            <label for="register_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Nama Register / Terminal
            </label>
            <input type="text" id="register_name" name="register_name"
                value="{{ old('register_name', 'Kasir Utama') }}"
                placeholder="Contoh: Kasir 1, Terminal A"
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
            @error('register_name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Gudang --}}
        @if($warehouses->isNotEmpty())
        <div>
            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Gudang / Lokasi
            </label>
            <select id="warehouse_id" name="warehouse_id"
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
                <option value="">— Pilih Gudang (opsional) —</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            @error('warehouse_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Modal Awal --}}
        <div>
            <label for="opening_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Modal Awal (Kas di Laci) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                <input type="number" id="opening_balance" name="opening_balance"
                    value="{{ old('opening_balance', 0) }}"
                    min="0" step="1000" required
                    class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
            </div>
            @error('opening_balance')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Catatan --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Catatan (opsional)
            </label>
            <textarea id="notes" name="notes" rows="2"
                placeholder="Catatan tambahan untuk sesi ini..."
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition resize-none">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Waktu buka (informasi) --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3">
            <p class="text-xs text-blue-700 dark:text-blue-300">
                <span class="font-medium">Waktu buka:</span> {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-3 pt-2">
            <a href="{{ route('pos.sessions.index') }}"
                class="flex-1 text-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Batal
            </a>
            <button type="submit"
                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition">
                Buka Sesi Kasir
            </button>
        </div>
    </form>

</div>
@endsection
