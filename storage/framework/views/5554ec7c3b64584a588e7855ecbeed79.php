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
     <?php $__env->slot('header', null, []); ?> Invoice Saya <?php $__env->endSlot(); ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['draft' => 'Draft', 'sent' => 'Terkirim', 'partial_paid' => 'Sebagian', 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', 'voided' => 'Void']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Invoice</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $ic = match ($invoice->status) {
                                'paid' => 'green',
                                'voided', 'cancelled' => 'red',
                                'overdue' => 'orange',
                                'partial_paid' => 'blue',
                                default => 'amber',
                            };
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <?php echo e($invoice->number ?? '#' . $invoice->id); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500">
                                <?php echo e($invoice->created_at?->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp
                                <?php echo e(number_format($invoice->total_amount ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp
                                <?php echo e(number_format($invoice->remaining_amount ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($ic); ?>-100 text-<?php echo e($ic); ?>-700 $ic }}-500/20 $ic }}-400"><?php echo e(ucfirst(str_replace('_', ' ', $invoice->status))); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('customer-portal.invoices.show', $invoice)); ?>"
                                        class="text-blue-600 hover:underline text-xs">Detail</a>
                                    <a href="<?php echo e(route('customer-portal.invoices.download', $invoice)); ?>"
                                        class="text-green-600 hover:underline text-xs">PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum
                                ada invoice.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($invoices->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($invoices->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\customer-portal\invoices\index.blade.php ENDPATH**/ ?>