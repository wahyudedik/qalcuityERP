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
     <?php $__env->slot('header', null, []); ?> Opname — <?php echo e($stockOpnameSession->number); ?> <?php $__env->endSlot(); ?>

    <?php $s = $stockOpnameSession; ?>
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($s->warehouse->name ?? '-'); ?> ·
                <?php echo e($s->opname_date->format('d/m/Y')); ?></p>
        </div>
        <?php if($s->status !== 'completed'): ?>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'wms', 'edit')): ?>
            <form method="POST" action="<?php echo e(route('wms.opname.complete', $s)); ?>"
                onsubmit="return confirm('Selesaikan opname? Stok bin akan diperbarui.')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesaikan
                    Opname</button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">Bin</th>
                        <th class="px-4 py-3 text-right">Sistem</th>
                        <th class="px-4 py-3 text-right">Aktual</th>
                        <th class="px-4 py-3 text-right">Selisih</th>
                        <?php if($s->status !== 'completed'): ?>
                            <th class="px-4 py-3 text-center">Input</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $s->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $diff = $item->difference ?? 0; ?>
                        <tr
                            class="<?php echo e($diff != 0 ? ($diff > 0 ? 'bg-green-50/50 dark:bg-green-500/5' : 'bg-red-50/50 dark:bg-red-500/5') : ''); ?>">
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($item->product->name ?? '-'); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-slate-400">
                                <?php echo e($item->bin->code ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                <?php echo e(number_format($item->system_qty, 0)); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                <?php echo e($item->actual_qty !== null ? number_format($item->actual_qty, 0) : '-'); ?></td>
                            <td
                                class="px-4 py-3 text-right font-medium <?php echo e($diff > 0 ? 'text-green-500' : ($diff < 0 ? 'text-red-500' : 'text-gray-400')); ?>">
                                <?php echo e($diff != 0 ? ($diff > 0 ? '+' : '') . number_format($diff, 0) : '-'); ?>

                            </td>
                            <?php if($s->status !== 'completed'): ?>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="<?php echo e(route('wms.opname.item.update', $item)); ?>"
                                        class="inline flex items-center justify-center gap-1">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <input type="number" name="actual_qty"
                                            value="<?php echo e($item->actual_qty ?? $item->system_qty); ?>" min="0"
                                            step="1"
                                            class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg">Confirm</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\wms\opname-show.blade.php ENDPATH**/ ?>