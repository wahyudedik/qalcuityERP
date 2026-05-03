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
     <?php $__env->slot('header', null, []); ?> Pencatatan Panen <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Sesi Panen</p>
            <p class="text-xl font-bold text-gray-900"><?php echo e($stats['total_harvests']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Panen</p>
            <p class="text-xl font-bold text-emerald-600"><?php echo e(number_format($stats['total_qty'], 0)); ?> kg</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Reject</p>
            <p class="text-xl font-bold text-red-500"><?php echo e(number_format($stats['total_reject'], 0)); ?> kg</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Biaya Panen</p>
            <p class="text-xl font-bold text-gray-900">Rp <?php echo e(number_format($stats['total_cost'], 0, ',', '.')); ?></p>
        </div>
    </div>

    
    <?php if($perPlot->isNotEmpty()): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Produktivitas per Lahan</p>
        <div class="flex flex-wrap gap-3">
            <?php $__currentLoopData = $perPlot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $perHa = $pp->area_size > 0 ? round($pp->total / $pp->area_size, 0) : 0; ?>
            <div class="px-3 py-2 rounded-lg bg-gray-50 text-xs">
                <span class="font-bold text-gray-700"><?php echo e($pp->code); ?></span>
                <span class="text-emerald-600 ml-1"><?php echo e(number_format($pp->total, 0)); ?> kg</span>
                <span class="text-gray-400 ml-1">(<?php echo e($pp->sessions); ?>x)</span>
                <?php if($perHa > 0): ?><span class="text-blue-500 ml-1"><?php echo e(number_format($perHa, 0)); ?> kg/<?php echo e($pp->area_unit); ?></span><?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('farm.plots')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('harvestModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌾 Catat Panen</button>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Lahan</th>
                        <th class="px-4 py-3 text-left">Tanaman</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Reject</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-right">Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-400">
                            <a href="<?php echo e(route('farm.harvests.show', $log)); ?>" class="text-blue-500 hover:underline"><?php echo e($log->number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($log->harvest_date->format('d M Y')); ?></td>
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($log->plot?->code); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($log->crop_name); ?></td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600"><?php echo e(number_format($log->total_qty, 0)); ?> <?php echo e($log->unit); ?></td>
                        <td class="px-4 py-3 text-right font-mono <?php echo e($log->reject_qty > 0 ? 'text-red-500' : 'text-gray-300'); ?>">
                            <?php echo e($log->reject_qty > 0 ? number_format($log->reject_qty, 0) . ' ' . $log->unit : '-'); ?>

                        </td>
                        <td class="px-4 py-3">
                            <?php $__currentLoopData = $log->grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-block text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 mr-1"><?php echo e($g->grade); ?>: <?php echo e(number_format($g->quantity, 0)); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">Rp <?php echo e(number_format($log->totalCost(), 0, ',', '.')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada data panen.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4"><?php echo e($logs->links()); ?></div>

    
    <div id="harvestModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🌾 Catat Panen</h3>
                <button onclick="document.getElementById('harvestModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.harvests.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lahan *</label>
                        <select name="farm_plot_id" required class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = $plots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->code); ?> — <?php echo e($p->current_crop ?? $p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                        <input type="date" name="harvest_date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanaman *</label>
                        <input type="text" name="crop_name" required placeholder="Padi" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Total Panen *</label>
                        <input type="number" name="total_qty" required step="0.001" placeholder="500" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan *</label>
                        <input type="text" name="unit" required value="kg" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Reject</label>
                        <input type="number" name="reject_qty" step="0.001" value="0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kadar Air (%)</label>
                        <input type="number" name="moisture_pct" step="0.1" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cuaca</label>
                        <input type="text" name="weather" placeholder="Cerah" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 pt-1">Breakdown Grade (opsional)</p>
                <div id="grade-rows" class="space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="grades[0][grade]" placeholder="Grade A" class="<?php echo e($cls); ?>">
                        <input type="number" name="grades[0][quantity]" step="0.001" placeholder="Jumlah" class="<?php echo e($cls); ?>">
                        <input type="number" name="grades[0][price]" step="1" placeholder="Harga/unit" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <button type="button" onclick="addGradeRow()" class="text-xs text-blue-500 hover:text-blue-600">+ Tambah grade</button>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Upah Panen (Rp)</label>
                        <input type="number" name="labor_cost" step="1" min="0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Biaya Angkut (Rp)</label>
                        <input type="number" name="transport_cost" step="1" min="0" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Gudang Tujuan</label>
                    <input type="text" name="storage_location" placeholder="Gudang Panen" class="<?php echo e($cls); ?>">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('harvestModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let gradeIdx = 1;
    function addGradeRow() {
        const cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900';
        document.getElementById('grade-rows').insertAdjacentHTML('beforeend', `
            <div class="grid grid-cols-3 gap-2">
                <input type="text" name="grades[${gradeIdx}][grade]" placeholder="Grade B" class="${cls}">
                <input type="number" name="grades[${gradeIdx}][quantity]" step="0.001" placeholder="Jumlah" class="${cls}">
                <input type="number" name="grades[${gradeIdx}][price]" step="1" placeholder="Harga/unit" class="${cls}">
            </div>`);
        gradeIdx++;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\harvest-logs.blade.php ENDPATH**/ ?>