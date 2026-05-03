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
     <?php $__env->slot('header', null, []); ?> <?php echo e($farmPlot->code); ?> — <?php echo e($farmPlot->name); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <a href="<?php echo e(route('farm.plots')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Kembali ke Daftar Lahan</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">

            
            <?php $sc = $farmPlot->statusColor(); ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold text-gray-900"><?php echo e($farmPlot->code); ?></span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e($farmPlot->statusLabel()); ?></span>
                    </div>
                    <form method="POST" action="<?php echo e(route('farm.plots.status', $farmPlot)); ?>" class="flex items-center gap-2">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <select name="status" class="text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900 py-1.5 px-2">
                            <?php $__currentLoopData = \App\Models\FarmPlot::STATUS_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>" <?php if($farmPlot->status === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                    </form>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Luas</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($farmPlot->area_size, 1)); ?> <?php echo e($farmPlot->area_unit); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Biaya</p>
                        <p class="text-lg font-bold text-red-500">Rp <?php echo e(number_format($farmPlot->totalCost(), 0, ',', '.')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Panen</p>
                        <p class="text-lg font-bold text-emerald-600"><?php echo e(number_format($farmPlot->totalHarvest(), 0)); ?> <?php echo e($farmPlot->activities->where('activity_type', 'harvesting')->first()?->harvest_unit ?? 'kg'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">HPP / Unit</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo e($farmPlot->costPerUnit() ? 'Rp '.number_format($farmPlot->costPerUnit(), 0, ',', '.') : '-'); ?></p>
                    </div>
                </div>
                <?php if($farmPlot->planted_at || $farmPlot->expected_harvest): ?>
                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                    <?php if($farmPlot->planted_at): ?><span>🌱 Tanam: <?php echo e($farmPlot->planted_at->format('d M Y')); ?> (<?php echo e($farmPlot->daysSincePlanted()); ?> hari)</span><?php endif; ?>
                    <?php if($farmPlot->expected_harvest): ?>
                    <span class="<?php echo e($farmPlot->isHarvestOverdue() ? 'text-red-500 font-medium' : ''); ?>">
                        🌾 Panen: <?php echo e($farmPlot->expected_harvest->format('d M Y')); ?>

                        <?php if($farmPlot->isHarvestOverdue()): ?> (terlambat!) <?php elseif($farmPlot->daysUntilHarvest()): ?> (<?php echo e($farmPlot->daysUntilHarvest()); ?>h lagi) <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            
            <?php if($costByType->isNotEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Breakdown Biaya</h3>
                <div class="flex flex-wrap gap-3">
                    <?php $__currentLoopData = $costByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 text-xs">
                        <span class="font-medium text-gray-700"><?php echo e(\App\Models\FarmPlotActivity::ACTIVITY_TYPES[$ct->activity_type] ?? $ct->activity_type); ?></span>
                        <span class="text-gray-400 ml-1">Rp <?php echo e(number_format($ct->total_cost, 0, ',', '.')); ?></span>
                        <span class="text-gray-300 ml-1">(<?php echo e($ct->count); ?>x)</span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Riwayat Aktivitas (<?php echo e($farmPlot->activities->count()); ?>)</h3>
                    <button onclick="document.getElementById('activityModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">+ Catat Aktivitas</button>
                </div>
                <?php if($farmPlot->activities->isEmpty()): ?>
                <div class="p-8 text-center text-sm text-gray-400">Belum ada aktivitas tercatat.</div>
                <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $farmPlot->activities->take(30); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="text-lg mt-0.5"><?php echo e(\App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type] ? explode(' ', \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type])[0] : '📝'); ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900"><?php echo e($act->description); ?></p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-0.5 text-xs text-gray-500">
                                <span><?php echo e($act->date->format('d M Y')); ?></span>
                                <?php if($act->input_product): ?><span><?php echo e($act->input_product); ?>: <?php echo e($act->input_quantity); ?> <?php echo e($act->input_unit); ?></span><?php endif; ?>
                                <?php if($act->harvest_qty > 0): ?><span class="text-emerald-600 font-medium">Panen: <?php echo e(number_format($act->harvest_qty, 0)); ?> <?php echo e($act->harvest_unit); ?> <?php echo e($act->harvest_grade ? "({$act->harvest_grade})" : ''); ?></span><?php endif; ?>
                                <?php if($act->cost > 0): ?><span class="text-red-500">Rp <?php echo e(number_format($act->cost, 0, ',', '.')); ?></span><?php endif; ?>
                                <span>oleh <?php echo e($act->user?->name ?? '-'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Info Lahan</h3>
                <form method="POST" action="<?php echo e(route('farm.plots.update', $farmPlot)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                        <input type="text" name="name" value="<?php echo e($farmPlot->name); ?>" required class="<?php echo e($cls); ?>"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Luas</label>
                            <input type="number" name="area_size" value="<?php echo e($farmPlot->area_size); ?>" step="0.001" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <select name="area_unit" class="<?php echo e($cls); ?>">
                                <?php $__currentLoopData = ['ha'=>'Hektar','are'=>'Are','m2'=>'m²']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v); ?>" <?php if($farmPlot->area_unit === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tanaman</label>
                        <input type="text" name="current_crop" value="<?php echo e($farmPlot->current_crop); ?>" class="<?php echo e($cls); ?>"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Tgl Tanam</label>
                            <input type="date" name="planted_at" value="<?php echo e($farmPlot->planted_at?->format('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Est. Panen</label>
                            <input type="date" name="expected_harvest" value="<?php echo e($farmPlot->expected_harvest?->format('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Lokasi</label>
                        <input type="text" name="location" value="<?php echo e($farmPlot->location); ?>" class="<?php echo e($cls); ?>"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Jenis Tanah</label>
                            <input type="text" name="soil_type" value="<?php echo e($farmPlot->soil_type); ?>" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Irigasi</label>
                            <input type="text" name="irrigation_type" value="<?php echo e($farmPlot->irrigation_type); ?>" class="<?php echo e($cls); ?>"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="<?php echo e($cls); ?>"><?php echo e($farmPlot->notes); ?></textarea></div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    
    <div id="activityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">Catat Aktivitas</h3>
                <button onclick="document.getElementById('activityModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.plots.activities.store', $farmPlot)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Aktivitas *</label>
                        <select name="activity_type" id="act-type" required onchange="toggleHarvestFields()" class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = \App\Models\FarmPlotActivity::ACTIVITY_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                        <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required placeholder="Pemupukan urea 50 kg/ha" class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Input/Produk</label>
                        <input type="text" name="input_product" placeholder="Urea, Pestisida" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah</label>
                        <input type="number" name="input_quantity" step="0.001" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                        <input type="text" name="input_unit" placeholder="kg, liter" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" step="1" min="0" placeholder="0" class="<?php echo e($cls); ?>">
                </div>
                <div id="harvest-fields" class="hidden space-y-3 border-t border-gray-100 pt-3">
                    <p class="text-xs font-bold text-emerald-600 uppercase">Data Panen</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Panen</label>
                            <input type="number" name="harvest_qty" step="0.001" class="<?php echo e($cls); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <input type="text" name="harvest_unit" placeholder="kg, ton" class="<?php echo e($cls); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Grade</label>
                            <input type="text" name="harvest_grade" placeholder="A, B, Premium" class="<?php echo e($cls); ?>">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('activityModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function toggleHarvestFields() {
        const type = document.getElementById('act-type').value;
        document.getElementById('harvest-fields').classList.toggle('hidden', type !== 'harvesting');
    }
    </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\plot-show.blade.php ENDPATH**/ ?>