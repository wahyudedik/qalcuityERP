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
        <?php echo e(__('Network Devices')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Network Devices')); ?></h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        <?php echo e(__('Kelola router, access point, dan network devices')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.devices.create')); ?>"
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <?php echo e(__('Tambah Device')); ?>

                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Total Devices')); ?></p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total']); ?></p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                            <i class="fas fa-server text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Online')); ?></p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($stats['online']); ?></p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Offline')); ?></p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo e($stats['offline']); ?></p>
                        </div>
                        <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-full">
                            <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Maintenance')); ?></p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                <?php echo e($stats['maintenance']); ?></p>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-900/30 p-3 rounded-full">
                            <i class="fas fa-wrench text-yellow-600 dark:text-yellow-400 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.devices.index')); ?>"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="<?php echo e(__('Cari device...')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <select name="status"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?php echo e(__('Semua Status')); ?></option>
                            <option value="online" <?php echo e(request('status') == 'online' ? 'selected' : ''); ?>>
                                <?php echo e(__('Online')); ?></option>
                            <option value="offline" <?php echo e(request('status') == 'offline' ? 'selected' : ''); ?>>
                                <?php echo e(__('Offline')); ?></option>
                            <option value="maintenance" <?php echo e(request('status') == 'maintenance' ? 'selected' : ''); ?>>
                                <?php echo e(__('Maintenance')); ?></option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>
                                <?php echo e(__('Pending')); ?></option>
                        </select>
                    </div>

                    <div>
                        <select name="brand"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?php echo e(__('Semua Brand')); ?></option>
                            <option value="mikrotik" <?php echo e(request('brand') == 'mikrotik' ? 'selected' : ''); ?>>MikroTik
                            </option>
                            <option value="ubiquiti" <?php echo e(request('brand') == 'ubiquiti' ? 'selected' : ''); ?>>Ubiquiti
                            </option>
                            <option value="cisco" <?php echo e(request('brand') == 'cisco' ? 'selected' : ''); ?>>Cisco</option>
                            <option value="openwrt" <?php echo e(request('brand') == 'openwrt' ? 'selected' : ''); ?>>OpenWRT
                            </option>
                            <option value="other" <?php echo e(request('brand') == 'other' ? 'selected' : ''); ?>>Other</option>
                        </select>
                    </div>

                    <div>
                        <select name="type"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?php echo e(__('Semua Type')); ?></option>
                            <option value="router" <?php echo e(request('type') == 'router' ? 'selected' : ''); ?>>
                                <?php echo e(__('Router')); ?></option>
                            <option value="access_point" <?php echo e(request('type') == 'access_point' ? 'selected' : ''); ?>>
                                <?php echo e(__('Access Point')); ?></option>
                            <option value="switch" <?php echo e(request('type') == 'switch' ? 'selected' : ''); ?>>
                                <?php echo e(__('Switch')); ?></option>
                            <option value="firewall" <?php echo e(request('type') == 'firewall' ? 'selected' : ''); ?>>
                                <?php echo e(__('Firewall')); ?></option>
                        </select>
                    </div>

                    <div class="md:col-span-5 flex gap-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i><?php echo e(__('Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.devices.index')); ?>"
                            class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg">
                            <i class="fas fa-redo mr-2"></i><?php echo e(__('Reset')); ?>

                        </a>
                    </div>
                </form>
            </div>

            <!-- Success/Error Messages -->
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

            <!-- Devices Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Device')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Brand/Model')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('IP Address')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Status')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Subscriptions')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Users')); ?></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Last Seen')); ?></th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if($device->status === 'online'): ?>
                                                <div
                                                    class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                    <i class="fas fa-server text-green-600 dark:text-green-400"></i>
                                                </div>
                                            <?php elseif($device->status === 'offline'): ?>
                                                <div
                                                    class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                    <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                                                </div>
                                            <?php else: ?>
                                                <div
                                                    class="h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                                    <i class="fas fa-wrench text-yellow-600 dark:text-yellow-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo e($device->name); ?></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e(ucfirst($device->device_type)); ?></div>
                                            <?php if($device->location): ?>
                                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                                    <?php echo e($device->location); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white"><?php echo e(ucfirst($device->brand)); ?>

                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($device->model ?? '-'); ?>

                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white font-mono">
                                        <?php echo e($device->ip_address); ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Port: <?php echo e($device->port); ?>

                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($device->status === 'online'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            <?php echo e(__('Online')); ?>

                                        </span>
                                    <?php elseif($device->status === 'offline'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                            <?php echo e(__('Offline')); ?>

                                        </span>
                                    <?php elseif($device->status === 'maintenance'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                            <?php echo e(__('Maintenance')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400">
                                            <?php echo e(__('Pending')); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo e($device->subscriptions_count); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo e($device->hotspot_users_count); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e($device->last_seen_at ? $device->last_seen_at->diffForHumans() : __('Never')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="<?php echo e(route('telecom.devices.show', $device)); ?>"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="<?php echo e(__('View Details')); ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('telecom.devices.edit', $device)); ?>"
                                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                            title="<?php echo e(__('Edit')); ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('telecom.devices.destroy', $device)); ?>" method="POST"
                                            onsubmit="return confirm('<?php echo e(__('Yakin ingin menghapus device ini?')); ?>')"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="<?php echo e(__('Delete')); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-server text-6xl"></i>
                                        <p class="mt-2 text-sm"><?php echo e(__('Belum ada device yang terdaftar')); ?></p>
                                        <a href="<?php echo e(route('telecom.devices.create')); ?>"
                                            class="mt-2 inline-block text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            <?php echo e(__('Tambah device pertama')); ?> →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if($devices->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <?php echo e($devices->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\devices\index.blade.php ENDPATH**/ ?>