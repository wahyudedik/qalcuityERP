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
     <?php $__env->slot('header', null, []); ?> Siklus Tanam <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Siklus Aktif</p>
            <p class="text-xl font-bold text-blue-600"><?php echo e($stats['active']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-xl font-bold text-green-600"><?php echo e($stats['completed']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Panen Terlambat</p>
            <p class="text-xl font-bold <?php echo e($stats['overdue'] > 0 ? 'text-red-500' : 'text-gray-400'); ?>"><?php echo e($stats['overdue']); ?></p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('farm.plots')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('addCycleModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌱 Mulai Siklus Baru</button>
    </div>

    
    <?php if($cycles->isEmpty()): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <p class="text-3xl mb-3">🌱</p>
        <p class="text-sm text-gray-500">Belum ada siklus tanam. Mulai siklus pertama untuk tracking dari persiapan lahan hingga panen.</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php $__currentLoopData = $cycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $pc = $cycle->phaseColor(); ?>
        <a href="<?php echo e(route('farm.cycles.show', $cycle)); ?>" class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-400"><?php echo e($cycle->number); ?></span>
                    <span class="font-semibold text-gray-900"><?php echo e($cycle->crop_name); ?></span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 $pc }}-500/20 $pc }}-400"><?php echo e($cycle->phaseLabel()); ?></span>
                </div>
                <span class="text-xs text-gray-400"><?php echo e($cycle->plot?->code); ?> · <?php echo e($cycle->plot?->name); ?></span>
            </div>
            
            <div class="flex gap-1 mb-2">
                <?php $__currentLoopData = ['planning','land_prep','planting','vegetative','generative','harvest','post_harvest','completed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $idx = \App\Models\CropCycle::PHASE_ORDER[$p];
                    $currentIdx = $cycle->phaseIndex();
                    $done = $idx <= $currentIdx && $cycle->phase !== 'cancelled';
                ?>
                <div class="flex-1 h-1.5 rounded-full <?php echo e($done ? 'bg-emerald-500' : 'bg-gray-200'); ?>"></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <?php if($cycle->plan_harvest_date): ?>
                <span class="<?php echo e($cycle->isHarvestOverdue() ? 'text-red-500 font-medium' : ''); ?>">
                    Panen: <?php echo e($cycle->plan_harvest_date->format('d M Y')); ?>

                    <?php if($cycle->isHarvestOverdue()): ?> (terlambat) <?php elseif($cycle->daysUntilHarvest()): ?> (<?php echo e($cycle->daysUntilHarvest()); ?>h) <?php endif; ?>
                </span>
                <?php endif; ?>
                <?php if($cycle->actual_yield_qty > 0): ?>
                <span class="text-emerald-600">Hasil: <?php echo e(number_format($cycle->actual_yield_qty, 0)); ?> <?php echo e($cycle->target_yield_unit); ?></span>
                <?php endif; ?>
                <?php if($cycle->actual_cost > 0): ?>
                <span>Biaya: Rp <?php echo e(number_format($cycle->actual_cost, 0, ',', '.')); ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-4"><?php echo e($cycles->links()); ?></div>
    <?php endif; ?>

    
    <div id="addCycleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🌱 Mulai Siklus Tanam Baru</h3>
                <button onclick="document.getElementById('addCycleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.cycles.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lahan *</label>
                        <select name="farm_plot_id" required class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = $plots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->code); ?> — <?php echo e($p->name); ?> (<?php echo e($p->area_size); ?> <?php echo e($p->area_unit); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Musim</label>
                        <input type="text" name="season" placeholder="MT1, Gadu, Rendeng" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanaman *</label>
                        <input type="text" name="crop_name" required placeholder="Padi IR64, Jagung Hibrida" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Varietas</label>
                        <input type="text" name="crop_variety" placeholder="IR64, NK7328" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 pt-1">Rencana Jadwal</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Olah Tanah</label>
                        <input type="date" name="plan_prep_start" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tanam</label>
                        <input type="date" name="plan_plant_date" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Panen</label>
                        <input type="date" name="plan_harvest_date" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Target Panen</label>
                        <input type="number" name="target_yield_qty" step="0.001" placeholder="5000" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Satuan</label>
                        <input type="text" name="target_yield_unit" value="kg" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Anggaran (Rp)</label>
                        <input type="number" name="estimated_budget" step="1000" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addCycleModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Mulai Siklus</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\cycles.blade.php ENDPATH**/ ?>