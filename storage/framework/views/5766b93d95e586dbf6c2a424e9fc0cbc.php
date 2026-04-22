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
     <?php $__env->slot('title', null, []); ?> 3-Way Matching — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> 3-Way Matching <?php $__env->endSlot(); ?>

    <div class="mb-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">
            Verifikasi kesesuaian antara <span class="font-semibold text-blue-600">PO</span> (Purchase Order),
            <span class="font-semibold text-green-600">GR</span> (Goods Receipt), dan
            <span class="font-semibold text-amber-600">Invoice/Hutang</span>.
            Toleransi: ±2%.
        </p>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor PO atau supplier..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
    </div>

    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $matchingData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $po = $m['po']; ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border <?php echo e($m['status'] === 'matched' ? 'border-green-200 dark:border-green-500/30' : ($m['status'] === 'partial' ? 'border-amber-200 dark:border-amber-500/30' : 'border-red-200 dark:border-red-500/30')); ?> overflow-hidden">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/5">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-mono text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($po->number); ?></p>
                        <?php
                            $matchBadge = match($m['status']) {
                                'matched'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                'partial'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                default     => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            };
                            $matchLabel = match($m['status']) {
                                'matched' => '✓ 3-Way Match', 'partial' => '⚠ Partial Match', default => '✗ Belum Match',
                            };
                        ?>
                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($matchBadge); ?>"><?php echo e($matchLabel); ?></span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        <?php echo e($po->supplier->name); ?> &bull; <?php echo e($po->date->format('d M Y')); ?>

                    </p>
                </div>
            </div>

            
            <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-white/5">
                
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-400">PO</span>
                        <span class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase">Purchase Order</span>
                        <span class="ml-auto text-xs text-green-600 dark:text-green-400">✓ Referensi</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($m['po_total'], 0, ',', '.')); ?></p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1"><?php echo e(number_format($m['po_qty'], 0)); ?> unit dipesan</p>
                    <div class="mt-3 space-y-1">
                        <?php $__currentLoopData = $po->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400">
                            <span class="truncate max-w-[120px]"><?php echo e($item->product->name ?? '-'); ?></span>
                            <span><?php echo e($item->quantity_ordered); ?> × Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center text-xs font-bold text-green-600 dark:text-green-400">GR</span>
                        <span class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase">Goods Receipt</span>
                        <?php if($m['gr_match']): ?>
                        <span class="ml-auto text-xs text-green-600 dark:text-green-400">✓ Match</span>
                        <?php else: ?>
                        <span class="ml-auto text-xs text-red-500">✗ Selisih</span>
                        <?php endif; ?>
                    </div>
                    <?php if($po->goodsReceipts->count()): ?>
                    <p class="text-lg font-bold <?php echo e($m['gr_match'] ? 'text-green-600 dark:text-green-400' : 'text-red-500'); ?>">
                        <?php echo e(number_format($m['gr_qty'], 0)); ?> unit diterima
                    </p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1"><?php echo e($po->goodsReceipts->count()); ?> GR</p>
                    <div class="mt-3 space-y-1">
                        <?php $__currentLoopData = $po->goodsReceipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-xs text-gray-500 dark:text-slate-400">
                            <?php echo e($gr->number); ?> — <?php echo e($gr->receipt_date->format('d M Y')); ?>

                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-gray-400 dark:text-slate-500 mt-2">Belum ada GR</p>
                    <a href="<?php echo e(route('purchasing.goods-receipts')); ?>" class="inline-block mt-2 text-xs text-blue-600 hover:underline">Catat GR →</a>
                    <?php endif; ?>
                </div>

                
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-xs font-bold text-amber-600 dark:text-amber-400">INV</span>
                        <span class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase">Invoice / Hutang</span>
                        <?php if($m['inv_match']): ?>
                        <span class="ml-auto text-xs text-green-600 dark:text-green-400">✓ Match</span>
                        <?php elseif($m['inv_total'] > 0): ?>
                        <span class="ml-auto text-xs text-red-500">✗ Selisih</span>
                        <?php else: ?>
                        <span class="ml-auto text-xs text-gray-400">Belum ada</span>
                        <?php endif; ?>
                    </div>
                    <?php if($m['inv_total'] > 0): ?>
                    <p class="text-lg font-bold <?php echo e($m['inv_match'] ? 'text-green-600 dark:text-green-400' : 'text-red-500'); ?>">
                        Rp <?php echo e(number_format($m['inv_total'], 0, ',', '.')); ?>

                    </p>
                    <?php $diff = $m['inv_total'] - $m['po_total']; ?>
                    <?php if(!$m['inv_match']): ?>
                    <p class="text-xs <?php echo e($diff > 0 ? 'text-red-500' : 'text-amber-500'); ?> mt-1">
                        Selisih: Rp <?php echo e(number_format(abs($diff), 0, ',', '.')); ?> <?php echo e($diff > 0 ? '(lebih)' : '(kurang)'); ?>

                    </p>
                    <?php endif; ?>
                    <?php else: ?>
                    <p class="text-sm text-gray-400 dark:text-slate-500 mt-2">Belum ada invoice</p>
                    <a href="<?php echo e(route('payables.index')); ?>" class="inline-block mt-2 text-xs text-blue-600 hover:underline">Lihat Hutang →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 px-4 py-12 text-center text-gray-400 dark:text-slate-500">
            Tidak ada PO yang perlu diverifikasi.
        </div>
        <?php endif; ?>

        <?php if($orders->hasPages()): ?>
        <div><?php echo e($orders->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\purchasing\matching.blade.php ENDPATH**/ ?>