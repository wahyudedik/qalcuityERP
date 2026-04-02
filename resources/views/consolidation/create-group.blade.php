<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Company Group Baru
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('consolidation.groups.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Company Group *</label>
                        <input type="text" name="name" id="name" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('name') }}"
                            placeholder="Contoh: PT ABC Group">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">Mata Uang Konsolidasi *</label>
                        <select name="currency_code" id="currency_code" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="IDR" {{ old('currency_code') === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                            <option value="USD" {{ old('currency_code') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="EUR" {{ old('currency_code') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="SGD" {{ old('currency_code') === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                            <option value="MYR" {{ old('currency_code') === 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                        </select>
                        @error('currency_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Semua laporan konsolidasi akan menggunakan mata uang ini. Tenant Anda akan otomatis ditambahkan sebagai member pertama.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Buat Company Group
                        </button>
                        <a href="{{ route('consolidation.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
