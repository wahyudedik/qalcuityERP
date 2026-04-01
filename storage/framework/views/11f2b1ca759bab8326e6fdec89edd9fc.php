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
     <?php $__env->slot('header', null, []); ?> Populasi Ternak <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kelompok Aktif</p>
            <p class="text-xl font-bold text-blue-600"><?php echo e($stats['active_herds']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Populasi</p>
            <p class="text-xl font-bold text-emerald-600"><?php echo e(number_format($stats['total_animals'])); ?> ekor</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Kematian</p>
            <p class="text-xl font-bold text-red-500"><?php echo e(number_format(abs($stats['total_mortality']))); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Terjual</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stats['total_sold'])); ?></p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('farm.plots')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('addHerdModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🐄 Tambah Ternak</button>
    </div>

    
    <?php if($herds->isEmpty()): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <p class="text-3xl mb-3">🐄</p>
        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data ternak. Tambahkan kelompok ternak pertama.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__currentLoopData = $herds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $herd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('farm.livestock.show', $herd)); ?>" class="block bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden hover:border-emerald-300 dark:hover:border-emerald-500/30 transition">
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg"><?php echo e(explode(' ', \App\Models\LivestockHerd::ANIMAL_TYPES[$herd->animal_type] ?? '🐾')[0]); ?></span>
                        <span class="font-bold text-gray-900 dark:text-white"><?php echo e($herd->code); ?></span>
                        <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($herd->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>"><?php echo e(ucfirst($herd->status)); ?></span>
                    </div>
                    <span class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(number_format($herd->current_count)); ?></span>
                </div>
                <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($herd->name); ?></p>
                <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                    <div><span class="text-gray-400">Awal:</span> <span class="text-gray-700 dark:text-slate-300"><?php echo e($herd->initial_count); ?></span></div>
                    <div><span class="text-gray-400">Mati:</span> <span class="text-red-500"><?php echo e(abs($herd->mortalityCount())); ?> (<?php echo e($herd->mortalityRate()); ?>%)</span></div>
                    <div><span class="text-gray-400">Umur:</span> <span class="text-gray-700 dark:text-slate-300"><?php echo e($herd->ageDays() ?? '-'); ?> hari</span></div>
                </div>
                <?php if($herd->plot): ?>
                <p class="text-[10px] text-gray-400 mt-2">📍 <?php echo e($herd->plot->code); ?> — <?php echo e($herd->plot->name); ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-4"><?php echo e($herds->links()); ?></div>
    <?php endif; ?>

    
    <div id="addHerdModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🐄 Tambah Kelompok Ternak</h3>
                <button onclick="document.getElementById('addHerdModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.livestock.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Ternak *</label>
                        <select name="animal_type" required class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = \App\Models\LivestockHerd::ANIMAL_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ras/Breed</label>
                        <input type="text" name="breed" placeholder="Broiler, Brahman" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Kelompok *</label>
                    <input type="text" name="name" required placeholder="Ayam Broiler Batch 12" class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kandang/Area</label>
                        <select name="farm_plot_id" class="<?php echo e($cls); ?>">
                            <option value="">— Tanpa kandang —</option>
                            <?php $__currentLoopData = $plots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->code); ?> — <?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Masuk *</label>
                        <input type="number" name="initial_count" required min="1" placeholder="1000" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Masuk *</label>
                        <input type="date" name="entry_date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Umur Masuk (hari)</label>
                        <input type="number" name="entry_age_days" min="0" value="1" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berat Rata-rata (kg)</label>
                        <input type="number" name="entry_weight_kg" step="0.001" placeholder="0.04" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Beli Total (Rp)</label>
                        <input type="number" name="purchase_price" step="1" min="0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Panen</label>
                        <input type="date" name="target_harvest_date" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addHerdModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\livestock.blade.php ENDPATH**/ ?>