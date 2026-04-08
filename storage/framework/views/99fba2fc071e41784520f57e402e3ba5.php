<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> Buat Simulasi Baru <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if($errors->any()): ?>
            <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg text-sm">
                <ul class="list-disc list-inside"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('simulations.store')); ?>" x-data="simForm()" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Simulasi</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                           placeholder="Contoh: Kenaikan harga Q2 2026"
                           class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tipe Skenario</label>
                    <select name="scenario_type" x-model="type" required
                            class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-slate-800 dark:text-white text-sm">
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
            <div x-show="type === 'price_increase'" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white">Parameter Kenaikan Harga</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Kenaikan Harga (%)</label>
                        <input type="number" name="parameters[price_change_pct]" value="<?php echo e(old('parameters.price_change_pct', 10)); ?>"
                               min="1" max="100" step="0.5"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="<?php echo e(old('parameters.period_days', 30)); ?>"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: New Branch -->
            <div x-show="type === 'new_branch'" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white">Parameter Cabang Baru</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Biaya Tetap/Bulan (Rp)</label>
                        <input type="number" name="parameters[fixed_cost_monthly]" value="<?php echo e(old('parameters.fixed_cost_monthly', 10000000)); ?>"
                               min="0" step="500000"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Proyeksi Omzet/Bulan (Rp)</label>
                        <input type="number" name="parameters[revenue_projection]" value="<?php echo e(old('parameters.revenue_projection', 0)); ?>"
                               min="0" step="500000" placeholder="0 = estimasi otomatis"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Periode Proyeksi (bulan)</label>
                        <input type="number" name="parameters[months]" value="<?php echo e(old('parameters.months', 12)); ?>"
                               min="1" max="60"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: Stock Out -->
            <div x-show="type === 'stock_out'" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white">Parameter Simulasi Stok Habis</h3>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Periode Simulasi (hari)</label>
                    <input type="number" name="parameters[days]" value="<?php echo e(old('parameters.days', 30)); ?>"
                           min="1" max="90"
                           class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan product_ids untuk simulasi top 5 produk terlaris.</p>
                </div>
            </div>

            <!-- Parameters: Cost Reduction -->
            <div x-show="type === 'cost_reduction'" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white">Parameter Efisiensi Biaya</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Pengurangan Biaya (%)</label>
                        <input type="number" name="parameters[cost_reduction_pct]" value="<?php echo e(old('parameters.cost_reduction_pct', 10)); ?>"
                               min="1" max="100" step="0.5"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="<?php echo e(old('parameters.period_days', 30)); ?>"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Parameters: Demand Change -->
            <div x-show="type === 'demand_change'" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white">Parameter Perubahan Demand</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Perubahan Demand (%) — negatif = turun</label>
                        <input type="number" name="parameters[demand_change_pct]" value="<?php echo e(old('parameters.demand_change_pct', 20)); ?>"
                               min="-100" max="200" step="1"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-slate-400 mb-1">Periode Historis (hari)</label>
                        <input type="number" name="parameters[period_days]" value="<?php echo e(old('parameters.period_days', 30)); ?>"
                               min="7" max="365"
                               class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-[#0f172a] dark:text-white text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('simulations.index')); ?>"
                   class="px-4 py-2 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700">
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
            return { type: '<?php echo e(old('scenario_type', '')); ?>' };
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/simulations/create.blade.php ENDPATH**/ ?>