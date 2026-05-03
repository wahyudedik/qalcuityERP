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
     <?php $__env->slot('header', null, []); ?> Deferred Revenue & Prepaid Expense <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="deferred_revenue" <?php echo e(request('type') === 'deferred_revenue' ? 'selected' : ''); ?>>Pendapatan Diterima di Muka</option>
                <option value="prepaid_expense" <?php echo e(request('type') === 'prepaid_expense' ? 'selected' : ''); ?>>Biaya Dibayar di Muka</option>
            </select>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Selesai</option>
                <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Dibatalkan</option>
            </select>
        </form>
        <a href="<?php echo e(route('deferred.create')); ?>" class="ml-auto px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 shrink-0">+ Buat Baru</a>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor / Deskripsi</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Diakui</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $pct = $item->progressPercent();
                        $statusColor = match($item->status) {
                            'active'    => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-green-100 text-green-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $typeColor = $item->type === 'deferred_revenue'
                            ? 'bg-purple-100 text-purple-700'
                            : 'bg-orange-100 text-orange-700';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('deferred.show', $item)); ?>" class="font-medium text-blue-600 hover:underline"><?php echo e($item->number); ?></a>
                            <p class="text-xs text-gray-500 mt-0.5"><?php echo e($item->description); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($item->start_date->format('d M Y')); ?> – <?php echo e($item->end_date->format('d M Y')); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($typeColor); ?>">
                                <?php echo e($item->type === 'deferred_revenue' ? 'Pend. di Muka' : 'Biaya di Muka'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp <?php echo e(number_format($item->total_amount,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-green-600">Rp <?php echo e(number_format($item->recognized_amount,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">Rp <?php echo e(number_format($item->remaining_amount,0,',','.')); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 min-w-[60px]">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width:<?php echo e($pct); ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500 whitespace-nowrap"><?php echo e($item->recognized_periods); ?>/<?php echo e($item->total_periods); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                <?php echo e(ucfirst($item->status)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo e(route('deferred.show', $item)); ?>" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 inline-flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada data deferred item.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($items->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($items->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\deferred\index.blade.php ENDPATH**/ ?>