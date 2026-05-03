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
     <?php $__env->slot('header', null, []); ?> Invoice <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <?php
            $statCards = [
                ['label' => 'Total',       'value' => $stats['total'],   'color' => 'text-blue-600',   'bg' => 'bg-blue-50'],
                ['label' => 'Belum Bayar', 'value' => $stats['unpaid'],  'color' => 'text-red-600',     'bg' => 'bg-red-50'],
                ['label' => 'Sebagian',    'value' => $stats['partial'], 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                ['label' => 'Lunas',       'value' => $stats['paid'],    'color' => 'text-green-600', 'bg' => 'bg-green-50'],
                ['label' => 'Jatuh Tempo', 'value' => $stats['overdue'], 'color' => 'text-rose-600',   'bg' => 'bg-rose-50'],
            ];
            ?>
            <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-xl border border-gray-200 p-4 <?php echo e($card['bg']); ?>">
                <p class="text-xs text-gray-500"><?php echo e($card['label']); ?></p>
                <p class="text-2xl font-bold mt-1 <?php echo e($card['color']); ?>"><?php echo e($card['value']); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex gap-2 flex-1 flex-wrap">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari no. invoice / customer..."
                    class="flex-1 min-w-0 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="unpaid"  <?php echo e(request('status') === 'unpaid'  ? 'selected' : ''); ?>>Belum Dibayar</option>
                    <option value="partial" <?php echo e(request('status') === 'partial' ? 'selected' : ''); ?>>Sebagian</option>
                    <option value="paid"    <?php echo e(request('status') === 'paid'    ? 'selected' : ''); ?>>Lunas</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-100 text-sm text-gray-700 hover:bg-gray-200 transition">Cari</button>
            </form>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'invoices', 'create')): ?>
            <a href="<?php echo e(route('invoices.create')); ?>" class="shrink-0 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Invoice
            </a>
            <?php endif; ?>
        </div>

        
        <div class="rounded-2xl border border-gray-200 overflow-hidden bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Sisa</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isOverdue = $invoice->status !== 'paid' && $invoice->due_date < now();
                            $statusColor = match($invoice->status) {
                                'paid'    => 'bg-green-100 text-green-700',
                                'partial' => 'bg-amber-100 text-amber-700',
                                default   => 'bg-red-100 text-red-700',
                            };
                            $statusLabel = match($invoice->status) {
                                'paid'    => 'Lunas',
                                'partial' => 'Sebagian',
                                default   => 'Belum Bayar',
                            };
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="font-medium text-blue-600 hover:underline"><?php echo e($invoice->number); ?></a>
                            </td>
                            <td class="px-4 py-3 text-gray-900">
                                <?php echo e($invoice->customer?->name ?? '-'); ?>

                                <?php if($invoice->customer?->company): ?>
                                <span class="block text-xs text-gray-400"><?php echo e($invoice->customer->company); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell <?php echo e($isOverdue ? 'text-red-600 font-medium' : 'text-gray-600'); ?>">
                                <?php echo e($invoice->due_date?->format('d M Y') ?? '-'); ?>

                                <?php if($isOverdue): ?><span class="block text-xs">Terlambat <?php echo e($invoice->daysOverdue()); ?> hari</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?>

                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell <?php echo e($invoice->remaining_amount > 0 ? 'text-red-600' : 'text-green-600'); ?>">
                                Rp <?php echo e(number_format($invoice->remaining_amount, 0, ',', '.')); ?>

                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?php echo e($statusColor); ?>"><?php echo e($statusLabel); ?></span>
                                
                                <?php if(($invoice->posting_status ?? 'draft') !== 'posted'): ?>
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold <?php echo e($invoice->postingStatusColor()); ?>"><?php echo e($invoice->postingStatusLabel()); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="<?php echo e(route('invoices.show', $invoice)); ?>" title="Detail" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="<?php echo e(route('invoices.pdf', $invoice)); ?>" title="Download PDF" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                Belum ada invoice. <a href="<?php echo e(route('invoices.create')); ?>" class="text-blue-500 hover:underline">Buat invoice pertama</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($invoices->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100">
                <?php echo e($invoices->links()); ?>

            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\invoices\index.blade.php ENDPATH**/ ?>