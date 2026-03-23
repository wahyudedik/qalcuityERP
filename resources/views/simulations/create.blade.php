<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('simulations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">←</a>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Buat Simulasi Baru</h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('simulations.store') }}" x-data="simForm()" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Simulasi</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Contoh: Kenaikan harga Q2 2026"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Skenario</label>
                    <select name="scenario_type" x-model="type" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="">-- Pilih Skenario --</option>
                        <option value="price_increase">📈 Kenaikan Harga</option>
                        <option value="new_branch">🏪 Buka Cabang Baru</option>
                        <option value="stock_out">📦 Simulasi Stok Habis</option>
                        <option value="cost_reduction">✂️ Efisiensi Biaya</option>
                        <option value="demand_change">📊 Perubahan Demand</option>
                    </select>
                </div>
            </div>

            <!-- Parameters: Price Increase -->
            <div x-show="type === 'price_increase'" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <h3 class="font-medium text-gray-800 dark:text-gray-200">Parameter Kenaikan Harga</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Kenaikan Harga (%)</label>
                        <input type="number" name="parameters[price_change_pct]" value="{{ old('parameters.price_change_pct', 10) }}"
                               min="1" max="100" step="0.5"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="{{ old('parameters.period_days', 30) }}"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: New Branch -->
            <div x-show="type === 'new_branch'" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <h3 class="font-medium text-gray-800 dark:text-gray-200">Parameter Cabang Baru</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Biaya Tetap/Bulan (Rp)</label>
                        <input type="number" name="parameters[fixed_cost_monthly]" value="{{ old('parameters.fixed_cost_monthly', 10000000) }}"
                               min="0" step="500000"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Proyeksi Omzet/Bulan (Rp)</label>
                        <input type="number" name="parameters[revenue_projection]" value="{{ old('parameters.revenue_projection', 0) }}"
                               min="0" step="500000" placeholder="0 = estimasi otomatis"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Periode Proyeksi (bulan)</label>
                        <input type="number" name="parameters[months]" value="{{ old('parameters.months', 12) }}"
                               min="1" max="60"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: Stock Out -->
            <div x-show="type === 'stock_out'" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <h3 class="font-medium text-gray-800 dark:text-gray-200">Parameter Simulasi Stok Habis</h3>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Periode Simulasi (hari)</label>
                    <input type="number" name="parameters[days]" value="{{ old('parameters.days', 30) }}"
                           min="1" max="90"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan product_ids untuk simulasi top 5 produk terlaris.</p>
                </div>
            </div>

            <!-- Parameters: Cost Reduction -->
            <div x-show="type === 'cost_reduction'" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <h3 class="font-medium text-gray-800 dark:text-gray-200">Parameter Efisiensi Biaya</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Pengurangan Biaya (%)</label>
                        <input type="number" name="parameters[cost_reduction_pct]" value="{{ old('parameters.cost_reduction_pct', 10) }}"
                               min="1" max="100" step="0.5"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="{{ old('parameters.period_days', 30) }}"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: Demand Change -->
            <div x-show="type === 'demand_change'" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
                <h3 class="font-medium text-gray-800 dark:text-gray-200">Parameter Perubahan Demand</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Perubahan Demand (%) — negatif = turun</label>
                        <input type="number" name="parameters[demand_change_pct]" value="{{ old('parameters.demand_change_pct', 20) }}"
                               min="-100" max="200" step="1"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="{{ old('parameters.period_days', 30) }}"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('simulations.index') }}"
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    Batal
                </a>
                <button type="submit" x-bind:disabled="!type"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    Jalankan Simulasi
                </button>
            </div>
        </form>
    </div>

    <script>
        function simForm() {
            return { type: '{{ old('scenario_type', '') }}' };
        }
    </script>
</x-app-layout>
