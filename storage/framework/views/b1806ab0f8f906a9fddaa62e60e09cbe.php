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
     <?php $__env->slot('header', null, []); ?> 
        <?php echo e(__('Customer Usage Portal')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Customer Usage Portal')); ?></h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo e(__('Monitor & manage customer internet usage')); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('telecom.dashboard')); ?>"
                    class="bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo e(__('Back to Dashboard')); ?>

                </a>
            </div>

            <?php if(session('success')): ?>
                <div
                    class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div
                    class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Customers Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <?php echo e(__('Customers with Active Subscriptions')); ?></h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Customer')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Package')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Device')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Status')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Quota Usage')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <?php echo e(__('Actions')); ?>

                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $__currentLoopData = $customer->telecomSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                    <span
                                                        class="text-blue-600 dark:text-blue-400 font-bold"><?php echo e(substr($customer->name, 0, 1)); ?></span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo e($customer->name); ?></div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        <?php echo e($customer->email ?? __('No email')); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo e($subscription->package?->name ?? '-'); ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo e($subscription->package?->download_speed_mbps ?? 0); ?>/<?php echo e($subscription->package?->upload_speed_mbps ?? 0); ?>

                                                Mbps
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo e($subscription->device?->name ?? '-'); ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?php echo e($subscription->device?->ip_address ?? '-'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo e($subscription->status === 'active'
                                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                                    : ($subscription->status === 'suspended'
                                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'
                                                        : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400')); ?>">
                                                <?php echo e(ucfirst($subscription->status)); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($subscription->package->quota_bytes): ?>
                                                <?php
                                                    $used = $subscription->current_usage_bytes ?? 0;
                                                    $total = $subscription->package->quota_bytes;
                                                    $percent = min(100, round(($used / $total) * 100, 2));
                                                    $color =
                                                        $percent > 90 ? 'red' : ($percent > 70 ? 'yellow' : 'green');
                                                ?>
                                                <div class="w-32">
                                                    <div class="flex justify-between text-xs mb-1">
                                                        <span
                                                            class="text-gray-900 dark:text-white"><?php echo e(round($used / 1073741824, 2)); ?>

                                                            GB</span>
                                                        <span
                                                            class="text-gray-900 dark:text-white"><?php echo e(round($total / 1073741824, 2)); ?>

                                                            GB</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                        <div class="bg-<?php echo e($color); ?>-600 h-2 rounded-full"
                                                            style="width: <?php echo e($percent); ?>%"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        <?php echo e($percent); ?>% <?php echo e(__('used')); ?></div>
                                                </div>
                                            <?php else: ?>
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('Unlimited')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="<?php echo e(route('telecom.customers.show-usage', $customer)); ?>"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"><?php echo e(__('View Details')); ?></a>

                                            <?php if($subscription->status === 'active'): ?>
                                                <form action="<?php echo e(route('telecom.customers.suspend', $customer)); ?>"
                                                    method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit"
                                                        class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300"
                                                        onclick="return confirm('<?php echo e(__('Suspend subscription?')); ?>')"><?php echo e(__('Suspend')); ?></button>
                                                </form>
                                            <?php elseif($subscription->status === 'suspended'): ?>
                                                <form action="<?php echo e(route('telecom.customers.reactivate', $customer)); ?>"
                                                    method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit"
                                                        class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300"><?php echo e(__('Reactivate')); ?></button>
                                                </form>
                                            <?php endif; ?>

                                            <form action="<?php echo e(route('telecom.customers.reset-quota', $customer)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-purple-600 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300"
                                                    onclick="return confirm('<?php echo e(__('Reset quota for this customer?')); ?>')"><?php echo e(__('Reset Quota')); ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-users text-gray-400 dark:text-gray-500 text-5xl mb-3"></i>
                                        <p class="mt-2 text-sm">
                                            <?php echo e(__('Tidak ada customer dengan subscription aktif')); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($customers->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <?php echo e($customers->links()); ?>

                    </div>
                <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\customers\usage.blade.php ENDPATH**/ ?>