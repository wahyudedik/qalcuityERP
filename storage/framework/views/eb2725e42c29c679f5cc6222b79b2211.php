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
     <?php $__env->slot('header', null, []); ?> Buat Deferred Item Baru <?php $__env->endSlot(); ?>

    <div class="max-w-2xl">
        <?php if($errors->any()): ?>
        <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-sm text-red-700 dark:text-red-400">
            <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('deferred.store')); ?>" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5">
            <?php echo csrf_field(); ?>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                <select name="type" required id="type-select" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="deferred_revenue" <?php echo e(old('type') === 'deferred_revenue' ? 'selected' : ''); ?>>Pendapatan Diterima di Muka (Deferred Revenue)</option>
                    <option value="prepaid_expense" <?php echo e(old('type') === 'prepaid_expense' ? 'selected' : ''); ?>>Biaya Dibayar di Muka (Prepaid Expense)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label>
                <input type="text" name="description" value="<?php echo e(old('description')); ?>" required placeholder="Contoh: Sewa dibayar di muka Jan-Des 2026"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Total Jumlah (Rp) *</label>
                <input type="number" name="total_amount" value="<?php echo e(old('total_amount')); ?>" required min="1" step="1000"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="<?php echo e(old('start_date')); ?>" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Selesai *</label>
                    <input type="date" name="end_date" value="<?php echo e(old('end_date')); ?>" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Akun Deferred (Neraca) *</label>
                <p id="hint-deferred" class="text-xs text-gray-400 dark:text-slate-500 mb-1">Untuk Deferred Revenue: pilih akun Kewajiban (misal: Pendapatan Diterima di Muka). Untuk Prepaid: pilih akun Aset (misal: Biaya Dibayar di Muka).</p>
                <select name="deferred_account_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Akun --</option>
                    <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($acc->id); ?>" <?php echo e(old('deferred_account_id') == $acc->id ? 'selected' : ''); ?>>
                        <?php echo e($acc->code); ?> - <?php echo e($acc->name); ?> (<?php echo e($acc->getTypeLabel()); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Akun Pengakuan (Laba Rugi) *</label>
                <p class="text-xs text-gray-400 dark:text-slate-500 mb-1">Untuk Deferred Revenue: akun Pendapatan. Untuk Prepaid: akun Beban.</p>
                <select name="recognition_account_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Akun --</option>
                    <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($acc->id); ?>" <?php echo e(old('recognition_account_id') == $acc->id ? 'selected' : ''); ?>>
                        <?php echo e($acc->code); ?> - <?php echo e($acc->name); ?> (<?php echo e($acc->getTypeLabel()); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nomor Referensi (opsional)</label>
                <input type="text" name="reference_number" value="<?php echo e(old('reference_number')); ?>" placeholder="No. Invoice / Kontrak"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('deferred.index')); ?>" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</a>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat & Generate Jadwal</button>
            </div>
        </form>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\deferred\create.blade.php ENDPATH**/ ?>