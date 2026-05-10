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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Billing Dashboard')); ?> <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Invoices</p>
                    <p class="text-2xl font-semibold"><?php echo e($statistics['total_invoices'] ?? 0); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold text-yellow-600"><?php echo e($statistics['pending_payment'] ?? 0); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Paid</p>
                    <p class="text-2xl font-semibold text-green-600"><?php echo e($statistics['paid'] ?? 0); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Revenue</p>
                    <p class="text-xl font-semibold">Rp
                        <?php echo e(number_format($statistics['total_revenue'] ?? 0, 0, ',', '.')); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Invoices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Recent Invoices</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $recentInvoices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <p class="text-sm font-medium"><?php echo e($invoice->bill_number); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($invoice->patient?->name ?? '-'); ?></p>
                            </div>
                            <p class="text-sm">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500 text-center">No recent invoices.</p>
                    <?php endif; ?>
                </div>

                <!-- Overdue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-600 mb-4">Overdue Invoices</h3>
                    <?php $__empty_1 = true; $__currentLoopData = $overdueInvoices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <p class="text-sm font-medium"><?php echo e($invoice->bill_number); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($invoice->patient?->name ?? '-'); ?></p>
                            </div>
                            <p class="text-sm text-red-600">Rp <?php echo e(number_format($invoice->balance_due, 0, ',', '.')); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500 text-center">No overdue invoices.</p>
                    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/healthcare/billing/dashboard.blade.php ENDPATH**/ ?>