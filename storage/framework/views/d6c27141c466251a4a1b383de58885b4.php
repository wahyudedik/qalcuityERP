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
     <?php $__env->slot('header', null, []); ?> <?php echo e($cropCycle->number); ?> — <?php echo e($cropCycle->crop_name); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('farm.cycles')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Siklus</a>
        <a href="<?php echo e(route('farm.plots.show', $cropCycle->plot)); ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400">Lahan <?php echo e($cropCycle->plot->code); ?> →</a>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($cropCycle->crop_name); ?></span>
                <?php if($cropCycle->crop_variety): ?> <span class="text-sm text-gray-400 ml-2">var. <?php echo e($cropCycle->crop_variety); ?></span> <?php endif; ?>
                <?php if($cropCycle->season): ?> <span class="text-xs ml-2 px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"><?php echo e($cropCycle->season); ?></span> <?php endif; ?>
            </div>
            <?php if(!in_array($cropCycle->phase, ['completed', 'cancelled'])): ?>
            <form method="POST" action="<?php echo e(route('farm.cycles.phase', $cropCycle)); ?>" class="flex items-center gap-2">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <select name="phase" class="text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white py-1.5 px-2">
                    <?php $__currentLoopData = \App\Models\CropCycle::PHASE_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\App\Models\CropCycle::PHASE_ORDER[$v] > $cropCycle->phaseIndex() || $v === 'cancelled'): ?>
                    <option value="<?php echo e($v); ?>">→ <?php echo e($l); ?></option>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Ubah Fase</button>
            </form>
            <?php endif; ?>
        </div>

        
        <?php $phases = ['planning','land_prep','planting','vegetative','generative','harvest','post_harvest','completed']; ?>
        <div class="flex gap-1 mb-1">
            <?php $__currentLoopData = $phases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $idx = \App\Models\CropCycle::PHASE_ORDER[$p];
                $currentIdx = $cropCycle->phaseIndex();
                $isCurrent = $cropCycle->phase === $p;
                $done = $idx < $currentIdx && $cropCycle->phase !== 'cancelled';
                $color = $isCurrent ? 'bg-emerald-500' : ($done ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-white/10');
            ?>
            <div class="flex-1 h-2 rounded-full <?php echo e($color); ?> <?php echo e($isCurrent ? 'ring-2 ring-emerald-300' : ''); ?>"></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="flex justify-between text-[9px] text-gray-400 dark:text-slate-500 mt-1 px-0.5">
            <?php $__currentLoopData = $phases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="<?php echo e($cropCycle->phase === $p ? 'text-emerald-600 dark:text-emerald-400 font-bold' : ''); ?>">
                <?php echo e(explode(' ', \App\Models\CropCycle::PHASE_LABELS[$p])[0]); ?>

            </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Durasi</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($cropCycle->durationDays() ?? 0); ?> hari</p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Hasil Panen</p>
                    <p class="text-lg font-bold text-emerald-600"><?php echo e(number_format($cropCycle->actual_yield_qty, 0)); ?> / <?php echo e(number_format($cropCycle->target_yield_qty, 0)); ?> <?php echo e($cropCycle->target_yield_unit); ?></p>
                    <?php if($cropCycle->target_yield_qty > 0): ?>
                    <p class="text-[10px] text-gray-400">(<?php echo e($cropCycle->yieldPercent()); ?>%)</p>
                    <?php endif; ?>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya</p>
                    <p class="text-lg font-bold <?php echo e($cropCycle->budgetUsedPercent() > 100 ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">Rp <?php echo e(number_format($cropCycle->actual_cost, 0, ',', '.')); ?></p>
                    <?php if($cropCycle->estimated_budget > 0): ?>
                    <p class="text-[10px] text-gray-400">dari Rp <?php echo e(number_format($cropCycle->estimated_budget, 0, ',', '.')); ?> (<?php echo e($cropCycle->budgetUsedPercent()); ?>%)</p>
                    <?php endif; ?>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">HPP / <?php echo e($cropCycle->target_yield_unit); ?></p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($cropCycle->costPerUnit() ? 'Rp '.number_format($cropCycle->costPerUnit(), 0, ',', '.') : '-'); ?></p>
                </div>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Aktivitas (<?php echo e($cropCycle->activities->count()); ?>)</h3>
                    <?php if(!in_array($cropCycle->phase, ['completed', 'cancelled'])): ?>
                    <button onclick="document.getElementById('actModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">+ Catat</button>
                    <?php endif; ?>
                </div>
                <?php if($cropCycle->activities->isEmpty()): ?>
                <div class="p-8 text-center text-sm text-gray-400">Belum ada aktivitas.</div>
                <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $cropCycle->activities->take(50); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="text-lg mt-0.5"><?php echo e(explode(' ', \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type] ?? '📝')[0]); ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white"><?php echo e($act->description); ?></p>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-0.5 text-xs text-gray-500 dark:text-slate-400">
                                <span><?php echo e($act->date->format('d M Y')); ?></span>
                                <?php if($act->input_product): ?><span><?php echo e($act->input_product); ?>: <?php echo e($act->input_quantity); ?> <?php echo e($act->input_unit); ?></span><?php endif; ?>
                                <?php if($act->harvest_qty > 0): ?><span class="text-emerald-600 font-medium">Panen: <?php echo e(number_format($act->harvest_qty, 0)); ?> <?php echo e($act->harvest_unit); ?></span><?php endif; ?>
                                <?php if($act->cost > 0): ?><span class="text-red-500">Rp <?php echo e(number_format($act->cost, 0, ',', '.')); ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Jadwal</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Lahan</span><span class="text-gray-900 dark:text-white font-medium"><?php echo e($cropCycle->plot->code); ?> — <?php echo e($cropCycle->plot->name); ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Luas</span><span><?php echo e($cropCycle->plot->area_size); ?> <?php echo e($cropCycle->plot->area_unit); ?></span></div>
                    <?php if($cropCycle->plan_prep_start): ?>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Renc. Olah Tanah</span><span><?php echo e($cropCycle->plan_prep_start->format('d M Y')); ?></span></div>
                    <?php endif; ?>
                    <?php if($cropCycle->plan_plant_date): ?>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Renc. Tanam</span><span><?php echo e($cropCycle->plan_plant_date->format('d M Y')); ?></span></div>
                    <?php endif; ?>
                    <?php if($cropCycle->plan_harvest_date): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Renc. Panen</span>
                        <span class="<?php echo e($cropCycle->isHarvestOverdue() ? 'text-red-500 font-medium' : ''); ?>"><?php echo e($cropCycle->plan_harvest_date->format('d M Y')); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($cropCycle->actual_prep_start): ?>
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Olah Tanah</span><span><?php echo e($cropCycle->actual_prep_start->format('d M Y')); ?></span></div>
                    <?php endif; ?>
                    <?php if($cropCycle->actual_plant_date): ?>
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Tanam</span><span><?php echo e($cropCycle->actual_plant_date->format('d M Y')); ?></span></div>
                    <?php endif; ?>
                    <?php if($cropCycle->actual_harvest_date): ?>
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Panen</span><span><?php echo e($cropCycle->actual_harvest_date->format('d M Y')); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($costByType->isNotEmpty()): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Breakdown Biaya</h3>
                <div class="space-y-2">
                    <?php $__currentLoopData = $costByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-slate-300"><?php echo e(\App\Models\FarmPlotActivity::ACTIVITY_TYPES[$ct->activity_type] ?? $ct->activity_type); ?></span>
                        <span class="font-mono text-gray-900 dark:text-white">Rp <?php echo e(number_format($ct->total_cost, 0, ',', '.')); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="actModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catat Aktivitas</h3>
                <button onclick="document.getElementById('actModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.cycles.activities.store', $cropCycle)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis *</label>
                        <select name="activity_type" id="cyc-act-type" required onchange="document.getElementById('cyc-harvest').classList.toggle('hidden', this.value!=='harvesting')" class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = \App\Models\FarmPlotActivity::ACTIVITY_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="block text-xs text-gray-500 mb-1">Input</label><input type="text" name="input_product" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Jumlah</label><input type="number" name="input_quantity" step="0.001" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Satuan</label><input type="text" name="input_unit" class="<?php echo e($cls); ?>"></div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" step="1" min="0" class="<?php echo e($cls); ?>">
                </div>
                <div id="cyc-harvest" class="hidden space-y-3 border-t border-gray-100 dark:border-white/10 pt-3">
                    <div class="grid grid-cols-3 gap-3">
                        <div><label class="block text-xs text-emerald-600 mb-1">Jumlah Panen</label><input type="number" name="harvest_qty" step="0.001" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs text-emerald-600 mb-1">Satuan</label><input type="text" name="harvest_unit" value="<?php echo e($cropCycle->target_yield_unit); ?>" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs text-emerald-600 mb-1">Grade</label><input type="text" name="harvest_grade" class="<?php echo e($cls); ?>"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('actModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\cycle-show.blade.php ENDPATH**/ ?>