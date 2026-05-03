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
     <?php $__env->slot('header', null, []); ?> <?php echo e($harvestLog->number); ?> <?php $__env->endSlot(); ?>

    <div class="mb-4">
        <a href="<?php echo e(route('farm.harvests')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Panen</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">🌾</span>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo e($harvestLog->crop_name); ?> — Lahan <?php echo e($harvestLog->plot?->code); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($harvestLog->harvest_date->format('d M Y')); ?> · oleh <?php echo e($harvestLog->user?->name); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div><p class="text-xs text-gray-500">Total</p><p class="text-lg font-bold text-emerald-600"><?php echo e(number_format($harvestLog->total_qty, 0)); ?> <?php echo e($harvestLog->unit); ?></p></div>
                    <div><p class="text-xs text-gray-500">Bersih</p><p class="text-lg font-bold text-gray-900"><?php echo e(number_format($harvestLog->netQty(), 0)); ?> <?php echo e($harvestLog->unit); ?></p></div>
                    <div><p class="text-xs text-gray-500">Reject</p><p class="text-lg font-bold <?php echo e($harvestLog->reject_qty > 0 ? 'text-red-500' : 'text-gray-400'); ?>"><?php echo e(number_format($harvestLog->reject_qty, 0)); ?> (<?php echo e($harvestLog->rejectPercent()); ?>%)</p></div>
                    <div><p class="text-xs text-gray-500">Biaya</p><p class="text-lg font-bold text-gray-900">Rp <?php echo e(number_format($harvestLog->totalCost(), 0, ',', '.')); ?></p></div>
                    <div><p class="text-xs text-gray-500">HPP/<?php echo e($harvestLog->unit); ?></p><p class="text-lg font-bold text-gray-900"><?php echo e($harvestLog->costPerUnit() ? 'Rp '.number_format($harvestLog->costPerUnit(), 0, ',', '.') : '-'); ?></p></div>
                </div>
                <?php if($harvestLog->moisture_pct || $harvestLog->weather || $harvestLog->storage_location): ?>
                <div class="flex gap-4 mt-3 text-xs text-gray-500">
                    <?php if($harvestLog->moisture_pct): ?><span>💧 Kadar air: <?php echo e($harvestLog->moisture_pct); ?>%</span><?php endif; ?>
                    <?php if($harvestLog->weather): ?><span>☀️ <?php echo e($harvestLog->weather); ?></span><?php endif; ?>
                    <?php if($harvestLog->storage_location): ?><span>🏭 <?php echo e($harvestLog->storage_location); ?></span><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            
            <?php if($harvestLog->grades->isNotEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Breakdown Grade</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-2 text-left">Grade</th><th class="px-4 py-2 text-right">Jumlah</th><th class="px-4 py-2 text-right">Harga/Unit</th><th class="px-4 py-2 text-right">Subtotal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $harvestLog->grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900"><?php echo e($g->grade); ?></td>
                            <td class="px-4 py-2 text-right font-mono"><?php echo e(number_format($g->quantity, 0)); ?> <?php echo e($g->unit); ?></td>
                            <td class="px-4 py-2 text-right font-mono text-gray-500"><?php echo e($g->price_per_unit > 0 ? 'Rp '.number_format($g->price_per_unit, 0, ',', '.') : '-'); ?></td>
                            <td class="px-4 py-2 text-right font-mono font-medium text-emerald-600"><?php echo e($g->subtotal() > 0 ? 'Rp '.number_format($g->subtotal(), 0, ',', '.') : '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <?php if($harvestLog->estimatedRevenue() > 0): ?>
                    <tfoot class="bg-gray-50">
                        <tr><td colspan="3" class="px-4 py-2 font-bold text-gray-900">Estimasi Pendapatan</td>
                            <td class="px-4 py-2 text-right font-bold text-emerald-600">Rp <?php echo e(number_format($harvestLog->estimatedRevenue(), 0, ',', '.')); ?></td></tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            <?php endif; ?>

            
            <?php if($harvestLog->workers->isNotEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Pekerja Panen (<?php echo e($harvestLog->workers->count()); ?>)</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-right">Jumlah Petik</th><th class="px-4 py-2 text-right">Upah</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $harvestLog->workers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-2 text-gray-900"><?php echo e($w->worker_name); ?></td>
                            <td class="px-4 py-2 text-right font-mono"><?php echo e($w->quantity_picked > 0 ? number_format($w->quantity_picked, 0).' '.$w->unit : '-'); ?></td>
                            <td class="px-4 py-2 text-right font-mono"><?php echo e($w->wage > 0 ? 'Rp '.number_format($w->wage, 0, ',', '.') : '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Info</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Lahan</span><span class="font-medium"><?php echo e($harvestLog->plot?->code); ?> — <?php echo e($harvestLog->plot?->name); ?></span></div>
                    <?php if($harvestLog->cropCycle): ?><div class="flex justify-between"><span class="text-gray-500">Siklus</span><span><?php echo e($harvestLog->cropCycle->number); ?></span></div><?php endif; ?>
                    <div class="flex justify-between"><span class="text-gray-500">Upah Panen</span><span>Rp <?php echo e(number_format($harvestLog->labor_cost, 0, ',', '.')); ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Biaya Angkut</span><span>Rp <?php echo e(number_format($harvestLog->transport_cost, 0, ',', '.')); ?></span></div>
                    <?php if($harvestLog->notes): ?><div class="pt-2 border-t border-gray-100"><p class="text-xs text-gray-500"><?php echo e($harvestLog->notes); ?></p></div><?php endif; ?>
                </div>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\harvest-show.blade.php ENDPATH**/ ?>