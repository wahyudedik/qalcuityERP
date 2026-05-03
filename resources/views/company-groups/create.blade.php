<x-app-layout>
    <x-slot name="header">Buat Grup Perusahaan</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('company-groups.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
    </div>

    <div class="py-6 max-w-lg mx-auto px-4">
        <form method="POST" action="{{ route('company-groups.store') }}"
              class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Grup</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Holding ABC Group"
                       class="w-full rounded-lg border-gray-300 text-sm">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang Konsolidasi</label>
                <select name="currency_code" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="IDR" selected>IDR — Rupiah</option>
                    <option value="USD">USD — US Dollar</option>
                    <option value="SGD">SGD — Singapore Dollar</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('company-groups.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Buat Grup
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
