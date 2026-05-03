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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Geofencing Zone Management')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('telecom.geofencing.create')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Create Zone
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-map-marked-alt text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Zones</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['total_zones']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Zones</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['active_zones']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-circle text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Circular Zones</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['circular_zones']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <i class="fas fa-draw-polygon text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Polygon Zones</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($stats['polygon_zones']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Zones Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zone Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Devices</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alerts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($zone->name); ?></div>
                                        <?php if($zone->description): ?>
                                            <div class="text-sm text-gray-500"><?php echo e(Str::limit($zone->description, 50)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo e($zone->zone_type === 'circular' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'); ?>">
                                            <?php echo e(ucfirst($zone->zone_type)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($zone->devices_count); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($zone->alerts_count); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo e($zone->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo e($zone->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($zone->created_at->format('M d, Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="<?php echo e(route('telecom.geofencing.show', $zone->id)); ?>"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('telecom.geofencing.edit', $zone->id)); ?>"
                                            class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('telecom.geofencing.toggle-status', $zone->id)); ?>"
                                            method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-<?php echo e($zone->is_active ? 'pause' : 'play'); ?>"></i>
                                            </button>
                                        </form>
                                        <form action="<?php echo e(route('telecom.geofencing.destroy', $zone->id)); ?>"
                                            method="POST" class="inline"
                                            onsubmit="return confirm('Delete this zone?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No geofence zones found. Create your first zone to get started.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <?php echo e($zones->links()); ?>

                    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\geofencing\index.blade.php ENDPATH**/ ?>