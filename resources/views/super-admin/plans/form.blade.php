<x-app-layout>
    <x-slot name="title">{{ $plan->exists ? 'Edit Paket' : 'Tambah Paket' }} — Qalcuity ERP</x-slot>
    <x-slot name="header">{{ $plan->exists ? 'Edit Paket: ' . $plan->name : 'Tambah Paket Baru' }}</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('super-admin.plans.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white px-3 py-2 rounded-xl hover:bg-[#f8f8f8] dark:bg-white/10 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST"
              action="{{ $plan->exists ? route('super-admin.plans.update', $plan) : route('super-admin.plans.store') }}"
              class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
            @csrf
            @if($plan->exists) @method('PUT') @endif

            @if($errors->any())
                <div class="px-4 py-3 bg-red-500/20 border border-red-500/30 text-red-400 text-sm rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Nama Paket</label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="Basic, Pro, Enterprise">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white font-mono"
                        placeholder="basic, pro, enterprise">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Harga Bulanan (Rp)</label>
                    <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" required min="0"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="299000">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Harga Tahunan (Rp)</label>
                    <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" required min="0"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="2990000">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Maks. User</label>
                    <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users ?? 5) }}" required min="-1"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="-1 = tak terbatas">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Maks. Pesan AI/Bln</label>
                    <input type="number" name="max_ai_messages" value="{{ old('max_ai_messages', $plan->max_ai_messages ?? 100) }}" required min="-1"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="-1 = tak terbatas">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Hari Trial</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 14) }}" required min="0"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                        placeholder="14">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Fitur (satu per baris)</label>
                <textarea name="features" rows="5"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"
                    placeholder="Inventori&#10;Penjualan&#10;Pembelian&#10;AI Chat">{{ old('features', $plan->features ? implode("\n", $plan->features) : '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" required min="0"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </div>
                <div class="flex items-end pb-2.5">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-white/20 text-blue-600 focus:ring-blue-500 bg-gray-50 dark:bg-[#0f172a]">
                        <span class="text-sm text-gray-700 dark:text-slate-300 font-medium">Paket aktif (tersedia untuk tenant)</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-white/10">
                <a href="{{ route('super-admin.plans.index') }}"
                   class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white px-4 py-2.5 rounded-xl hover:bg-[#f8f8f8] dark:bg-white/10 transition">
                    Batal
                </a>
                <button type="submit"
                    class="text-sm bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    {{ $plan->exists ? 'Simpan Perubahan' : 'Buat Paket' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
