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
     <?php $__env->slot('header', null, []); ?> Riwayat Pergerakan Stok <?php $__env->endSlot(); ?>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Semua Pergerakan Stok</h3>
            <a href="<?php echo e(route('inventory.index')); ?>" class="text-sm text-blue-600 hover:underline">← Kembali</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sebelum</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sesudah</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Catatan</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($m->product->name ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500"><?php echo e($m->warehouse->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($m->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                <?php echo e($m->type === 'in' ? 'Masuk' : 'Keluar'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold <?php echo e($m->type === 'in' ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($m->type === 'in' ? '+' : '-'); ?><?php echo e($m->quantity); ?>

                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500"><?php echo e($m->quantity_before); ?></td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900"><?php echo e($m->quantity_after); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs"><?php echo e($m->notes ?? '-'); ?></td>
                        <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($m->created_at->format('d M Y H:i')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada pergerakan stok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($movements->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($movements->links()); ?></div>
        <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\movements.blade.php ENDPATH**/ ?>