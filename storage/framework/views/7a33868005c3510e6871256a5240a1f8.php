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
     <?php $__env->slot('header', null, []); ?> Manajemen Lahan <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Lahan</p>
            <p class="text-xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Luas</p>
            <p class="text-xl font-bold text-emerald-600"><?php echo e(number_format($stats['total_area'], 1)); ?> ha</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Sedang Ditanam</p>
            <p class="text-xl font-bold text-blue-600"><?php echo e($stats['planted']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Siap Panen</p>
            <p class="text-xl font-bold text-green-600"><?php echo e($stats['ready_harvest']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Kosong / Bera</p>
            <p class="text-xl font-bold text-gray-400"><?php echo e($stats['idle']); ?></p>
        </div>
    </div>

    
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari lahan..." class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = \App\Models\FarmPlot::STATUS_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌱 Tambah Lahan</button>
    </div>

    
    <?php if($plots->isEmpty()): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <p class="text-3xl mb-3">🌾</p>
        <p class="text-sm text-gray-500">Belum ada lahan. Tambahkan lahan/blok kebun pertama Anda.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__currentLoopData = $plots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $sc = $plot->statusColor(); ?>
        <a href="<?php echo e(route('farm.plots.show', $plot)); ?>" class="block bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-emerald-300 transition group">
            <div class="px-5 py-4 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-bold text-gray-900"><?php echo e($plot->code); ?></span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e($plot->statusLabel()); ?></span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5"><?php echo e($plot->name); ?></p>
                </div>
                <span class="text-xs text-gray-400"><?php echo e(number_format($plot->area_size, 1)); ?> <?php echo e($plot->area_unit); ?></span>
            </div>
            <div class="px-5 pb-4 grid grid-cols-2 gap-2 text-xs">
                <?php if($plot->current_crop): ?>
                <div><span class="text-gray-400">Tanaman:</span> <span class="text-gray-700"><?php echo e($plot->current_crop); ?></span></div>
                <?php endif; ?>
                <?php if($plot->planted_at): ?>
                <div><span class="text-gray-400">Tanam:</span> <span class="text-gray-700"><?php echo e($plot->planted_at->format('d M Y')); ?></span></div>
                <?php endif; ?>
                <?php if($plot->expected_harvest): ?>
                <div>
                    <span class="text-gray-400">Panen:</span>
                    <span class="<?php echo e($plot->isHarvestOverdue() ? 'text-red-500 font-medium' : 'text-gray-700'); ?>">
                        <?php echo e($plot->expected_harvest->format('d M Y')); ?>

                        <?php if($plot->isHarvestOverdue()): ?> (terlambat) <?php elseif($plot->daysUntilHarvest()): ?> (<?php echo e($plot->daysUntilHarvest()); ?>h lagi) <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if($plot->activities_count > 0): ?>
                <div><span class="text-gray-400">Aktivitas:</span> <span class="text-gray-700"><?php echo e($plot->activities_count); ?></span></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-4"><?php echo e($plots->links()); ?></div>
    <?php endif; ?>

    
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🌱 Tambah Lahan</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.plots.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode Lahan *</label>
                        <input type="text" name="code" required placeholder="A1, Blok-01" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Sawah Utara" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Luas *</label>
                        <input type="number" name="area_size" required step="0.001" placeholder="2.5" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                        <select name="area_unit" class="<?php echo e($cls); ?>">
                            <option value="ha">Hektar (ha)</option>
                            <option value="are">Are</option>
                            <option value="m2">m²</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kepemilikan</label>
                        <select name="ownership" class="<?php echo e($cls); ?>">
                            <option value="owned">Milik Sendiri</option>
                            <option value="rented">Sewa</option>
                            <option value="shared">Bagi Hasil</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Tanah</label>
                        <input type="text" name="soil_type" placeholder="Liat, berpasir, humus" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Irigasi</label>
                        <input type="text" name="irrigation_type" placeholder="Irigasi, tadah hujan" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Desa, kecamatan, atau koordinat" class="<?php echo e($cls); ?>">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanaman Saat Ini</label>
                    <input type="text" name="current_crop" placeholder="Padi, jagung, kelapa sawit..." class="<?php echo e($cls); ?>">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="<?php echo e($cls); ?>"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\plots.blade.php ENDPATH**/ ?>