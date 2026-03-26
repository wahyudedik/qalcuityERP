<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('company-groups.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">←</a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Buat Grup Perusahaan</h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto px-4">
        <form method="POST" action="{{ route('company-groups.store') }}"
              class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Grup</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Holding ABC Group"
                       class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Mata Uang Konsolidasi</label>
                <select name="currency_code" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-slate-800 dark:text-white text-sm">
                    <option value="IDR" selected>IDR — Rupiah</option>
                    <option value="USD">USD — US Dollar</option>
                    <option value="SGD">SGD — Singapore Dollar</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('company-groups.index') }}"
                   class="px-4 py-2 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
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
