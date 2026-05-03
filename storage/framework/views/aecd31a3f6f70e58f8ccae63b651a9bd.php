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
        <?php echo e(__('Customer Subscriptions')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Customer Subscriptions')); ?>

                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            <?php echo e(__('Manage all customer internet subscriptions')); ?></p>
                    </div>
                    <a href="<?php echo e(route('telecom.subscriptions.create')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>
                        <?php echo e(__('New Subscription')); ?>

                    </a>
                </div>
            </div>

            <!-- Filters & Stats -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.subscriptions.index')); ?>">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="sr-only">Cari</label>
                            <input type="text" name="search" id="search"
                                placeholder="Cari pelanggan..." value="<?php echo e(request('search')); ?>"
                                class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select name="status" id="status" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value=""><?php echo e(__('All Status')); ?></option>
                                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Active')); ?></option>
                                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Suspended')); ?></option>
                                <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Cancelled')); ?></option>
                            </select>
                        </div>

                        <!-- Package Filter -->
                        <div>
                            <select name="package_id" id="package_id" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value=""><?php echo e(__('All Packages')); ?></option>
                                <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pkg->id); ?>"
                                        <?php echo e(request('package_id') == $pkg->id ? 'selected' : ''); ?>>
                                        <?php echo e($pkg->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Device Filter -->
                        <div>
                            <select name="device_id" id="device_id" onchange="this.form.submit()"
                                class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value=""><?php echo e(__('All Devices')); ?></option>
                                <?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dev->id); ?>"
                                        <?php echo e(request('device_id') == $dev->id ? 'selected' : ''); ?>>
                                        <?php echo e($dev->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-100 rounded-md p-3">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        <?php echo e(__('Active')); ?></dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php echo e($stats['active'] ?? 0); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-yellow-100 rounded-md p-3">
                                    <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        <?php echo e(__('Suspended')); ?></dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php echo e($stats['suspended'] ?? 0); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-red-100 rounded-md p-3">
                                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        <?php echo e(__('Cancelled')); ?></dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php echo e($stats['cancelled'] ?? 0); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-indigo-100 rounded-md p-3">
                                    <i class="fas fa-dollar-sign text-indigo-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        <?php echo e(__('Monthly Revenue')); ?></dt>
                                    <dd class="text-2xl font-semibold text-gray-900">Rp
                                        <?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.')); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <?php if($subscriptions->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Customer')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Package')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Device')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Status')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Usage')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Next Billing')); ?>

                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php echo e(__('Actions')); ?>

                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <!-- Customer -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <span
                                                            class="text-indigo-600 font-semibold">
                                                            <?php echo e(substr($sub->customer?->name ?? '?', 0, 2)); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo e($sub->customer?->name ?? '-'); ?>

                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo e($sub->customer?->email ?? ''); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Package -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo e($sub->package?->name ?? '-'); ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo e($sub->package?->download_speed_mbps ?? 0); ?>/<?php echo e($sub->package?->upload_speed_mbps ?? 0); ?>

                                                Mbps
                                            </div>
                                        </td>

                                        <!-- Device -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo e($sub->device->name ?? 'N/A'); ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo e($sub->device->ip_address ?? '-'); ?></div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($sub->status === 'active'): ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <?php echo e(__('Active')); ?>

                                                </span>
                                            <?php elseif($sub->status === 'suspended'): ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <?php echo e(__('Suspended')); ?>

                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <?php echo e(__('Cancelled')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Usage -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                                $quotaBytes = $sub->package->quota_bytes;
                                                $usedBytes = $sub->current_usage_bytes ?? 0;
                                                $percentage =
                                                    $quotaBytes > 0 ? round(($usedBytes / $quotaBytes) * 100, 1) : 0;
                                            ?>
                                            <div class="text-sm text-gray-900">
                                                <?php echo e($percentage); ?>%
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                <div class="bg-indigo-600 h-2 rounded-full"
                                                    style="width: <?php echo e(min($percentage, 100)); ?>%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php echo e(round($usedBytes / 1073741824, 2)); ?> GB /
                                                <?php echo e($quotaBytes > 0 ? round($quotaBytes / 1073741824, 2) . ' GB' : __('Unlimited')); ?>

                                            </div>
                                        </td>

                                        <!-- Next Billing -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e($sub->next_billing_date ? $sub->next_billing_date->format('d M Y') : '-'); ?>

                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="<?php echo e(route('telecom.subscriptions.show', $sub->id)); ?>"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    title="<?php echo e(__('View Details')); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <?php if($sub->status === 'active'): ?>
                                                    <form
                                                        action="<?php echo e(route('telecom.subscriptions.suspend', $sub->id)); ?>"
                                                        method="POST" class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                            class="text-yellow-600 hover:text-yellow-900"
                                                            title="<?php echo e(__('Suspend')); ?>"
                                                            onclick="return confirm('<?php echo e(__('Suspend this subscription?')); ?>')">
                                                            <i class="fas fa-pause-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif($sub->status === 'suspended'): ?>
                                                    <form
                                                        action="<?php echo e(route('telecom.subscriptions.reactivate', $sub->id)); ?>"
                                                        method="POST" class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                            class="text-green-600 hover:text-green-900"
                                                            title="<?php echo e(__('Reactivate')); ?>"
                                                            onclick="return confirm('<?php echo e(__('Reactivate this subscription?')); ?>')">
                                                            <i class="fas fa-play-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="<?php echo e(route('telecom.subscriptions.destroy', $sub->id)); ?>"
                                                    method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="<?php echo e(__('Cancel Subscription')); ?>"
                                                        onclick="return confirm('<?php echo e(__('Are you sure? This will cancel the subscription permanently.')); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <?php echo e($subscriptions->links()); ?>

                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <i class="fas fa-file-invoice text-gray-400 text-5xl mb-3"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            <?php echo e(__('No subscriptions found')); ?></h3>
                        <p class="mt-1 text-sm text-gray-500">
                            <?php echo e(__('Get started by creating a new subscription.')); ?></p>
                        <div class="mt-6">
                            <a href="<?php echo e(route('telecom.subscriptions.create')); ?>"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i>
                                <?php echo e(__('New Subscription')); ?>

                            </a>
                        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\subscriptions\index.blade.php ENDPATH**/ ?>