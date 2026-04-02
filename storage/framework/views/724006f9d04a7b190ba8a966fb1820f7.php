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
     <?php $__env->slot('header', null, []); ?> Material Requirement Planning (MRP) <?php $__env->endSlot(); ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Kalkulasi Kebutuhan Material</h3>
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="bom_id" class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">-- Pilih BOM --</option>
                <?php $__currentLoopData = $boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($b->id); ?>" <?php if(request('bom_id') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->name); ?> (<?php echo e($b->product->name ?? '-'); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="number" name="quantity" min="1" step="1" value="<?php echo e($quantity); ?>" placeholder="Jumlah produksi"
                class="w-32 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Hitung</button>
            <button type="submit" name="full_mrp" value="1" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Full MRP (Semua WO)</button>
        </form>
    </div>

    
    <?php if($results !== null): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                Kebutuhan: <?php echo e($selectedBom->name ?? '-'); ?> × <?php echo e(number_format($quantity, 0, ',', '.')); ?>

            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-right">Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-right">PO Pending</th>
                        <th class="px-4 py-3 text-right">Demand WO Lain</th>
                        <th class="px-4 py-3 text-right">Tersedia</th>
                        <th class="px-4 py-3 text-right">Kekurangan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">
                            <?php if($r['level'] > 0): ?><span class="text-gray-400"><?php echo e(str_repeat('└─ ', $r['level'])); ?></span><?php endif; ?>
                            <?php echo e($r['product_name']); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white"><?php echo e(number_format($r['required'], 2, ',', '.')); ?> <?php echo e($r['unit']); ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($r['on_hand'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($r['on_order'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($r['other_demand'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e(number_format($r['available'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right font-bold <?php echo e($r['shortage'] > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                            <?php echo e($r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—'); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($r['shortage'] > 0): ?>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php $totalShortage = collect($results)->sum('shortage'); ?>
        <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-4">
            <?php if($totalShortage > 0): ?>
                <span class="text-sm text-red-500">⚠️ Ada <?php echo e(collect($results)->where('shortage', '>', 0)->count()); ?> material yang kurang stok.</span>
            <?php else: ?>
                <span class="text-sm text-green-500">✅ Semua material tersedia untuk produksi.</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($fullMrp !== null): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Full MRP — Semua Work Order Aktif</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Agregasi kebutuhan material dari semua WO pending/in-progress yang memiliki BOM</p>
        </div>
        <?php if(count($fullMrp) > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-right">Total Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-right">PO Pending</th>
                        <th class="px-4 py-3 text-right">Tersedia</th>
                        <th class="px-4 py-3 text-right">Kekurangan</th>
                        <th class="px-4 py-3 text-left">Work Order</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $fullMrp; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($r['shortage'] > 0 ? 'bg-red-50/50 dark:bg-red-500/5' : ''); ?>">
                        <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($r['product_name']); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white"><?php echo e(number_format($r['required'], 2, ',', '.')); ?> <?php echo e($r['unit']); ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($r['on_hand'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($r['on_order'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e(number_format($r['available'], 2, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right font-bold <?php echo e($r['shortage'] > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                            <?php echo e($r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—'); ?>

                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                            <?php echo e(implode(', ', array_slice($r['wo_refs'], 0, 3))); ?>

                            <?php if(count($r['wo_refs']) > 3): ?> <span class="text-gray-400">+<?php echo e(count($r['wo_refs']) - 3); ?></span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($r['shortage'] > 0): ?>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php $shortageCount = collect($fullMrp)->where('shortage', '>', 0)->count(); ?>
        <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10">
            <?php if($shortageCount > 0): ?>
                <span class="text-sm text-red-500">⚠️ <?php echo e($shortageCount); ?> material kekurangan stok. Buat Purchase Order untuk memenuhi kebutuhan.</span>
            <?php else: ?>
                <span class="text-sm text-green-500">✅ Semua material tersedia untuk seluruh Work Order aktif.</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
            Tidak ada Work Order aktif yang memiliki BOM. Buat WO dengan BOM terlebih dahulu.
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/manufacturing/mrp.blade.php ENDPATH**/ ?>