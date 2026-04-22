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
     <?php $__env->slot('header', null, []); ?> Manajemen Tempat Tidur <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Tempat Tidur'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Tempat Tidur'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="<?php echo e(route('healthcare.beds.create')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-medium text-sm text-white hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Tempat Tidur
                </a>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-bed text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Beds</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($statistics['total_beds']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Available</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($statistics['available_beds']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <i class="fas fa-user-injured text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupied</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($statistics['occupied_beds']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-tools text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Maintenance</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo e($statistics['maintenance_beds']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('healthcare.beds.index')); ?>" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ward</label>
                        <select name="ward_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Wards</option>
                            <?php $__currentLoopData = $wards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ward->id); ?>" <?php if(request('ward_id') == $ward->id): echo 'selected'; endif; ?>><?php echo e($ward->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="available" <?php if(request('status') === 'available'): echo 'selected'; endif; ?>>Available</option>
                            <option value="occupied" <?php if(request('status') === 'occupied'): echo 'selected'; endif; ?>>Occupied</option>
                            <option value="maintenance" <?php if(request('status') === 'maintenance'): echo 'selected'; endif; ?>>Maintenance</option>
                            <option value="reserved" <?php if(request('status') === 'reserved'): echo 'selected'; endif; ?>>Reserved</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Beds Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    <?php $__empty_1 = true; $__currentLoopData = $beds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-2 rounded-lg p-4 text-center cursor-pointer hover:shadow-lg transition
                        <?php if($bed->status === 'available'): ?> border-green-300 bg-green-50
                        <?php elseif($bed->status === 'occupied'): ?> border-red-300 bg-red-50
                        <?php elseif($bed->status === 'maintenance'): ?> border-yellow-300 bg-yellow-50
                        <?php elseif($bed->status === 'reserved'): ?> border-blue-300 bg-blue-50
                        <?php else: ?> border-gray-300 bg-gray-50 <?php endif; ?>"
                            onclick="window.location.href='<?php echo e(route('healthcare.beds.show', $bed)); ?>'">
                            <i
                                class="fas fa-bed text-3xl mb-2
                            <?php if($bed->status === 'available'): ?> text-green-600
                            <?php elseif($bed->status === 'occupied'): ?> text-red-600
                            <?php elseif($bed->status === 'maintenance'): ?> text-yellow-600
                            <?php elseif($bed->status === 'reserved'): ?> text-blue-600
                            <?php else: ?> text-gray-600 <?php endif; ?>"></i>
                            <p class="text-sm font-semibold text-gray-900"><?php echo e($bed->bed_number); ?></p>
                            <p class="text-xs text-gray-600 mt-1"><?php echo e($bed->ward ? $bed->ward->ward_code : 'No Ward'); ?>

                            </p>
                            <span
                                class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full
                            <?php if($bed->status === 'available'): ?> bg-green-200 text-green-800
                            <?php elseif($bed->status === 'occupied'): ?> bg-red-200 text-red-800
                            <?php elseif($bed->status === 'maintenance'): ?> bg-yellow-200 text-yellow-800
                            <?php elseif($bed->status === 'reserved'): ?> bg-blue-200 text-blue-800
                            <?php else: ?> bg-gray-200 text-gray-800 <?php endif; ?>">
                                <?php echo e(ucfirst($bed->status)); ?>

                            </span>
                            <?php if($bed->status === 'occupied' && $bed->patient): ?>
                                <p class="text-xs text-gray-700 mt-1 truncate"><?php echo e($bed->patient->name); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-full text-center py-12 text-gray-500">
                            <i class="fas fa-bed text-6xl mb-4 text-gray-300"></i>
                            <p class="text-lg">No beds found</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($beds->hasPages()): ?>
                    <div class="mt-6">
                        <?php echo e($beds->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\beds\index.blade.php ENDPATH**/ ?>