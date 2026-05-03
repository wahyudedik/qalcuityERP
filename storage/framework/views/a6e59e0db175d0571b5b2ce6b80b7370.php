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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Ward Details')); ?> - <?php echo e($ward->name); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.wards.edit', $ward)); ?>"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
        <a href="<?php echo e(route('healthcare.wards.index')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Ward Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ward Information</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ward Code</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($ward->ward_code); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($ward->name); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php if($ward->ward_type === 'icu'): ?> bg-red-100 text-red-800
                                        <?php elseif($ward->ward_type === 'emergency'): ?> bg-orange-100 text-orange-800
                                        <?php elseif($ward->ward_type === 'maternity'): ?> bg-pink-100 text-pink-800
                                        <?php elseif($ward->ward_type === 'pediatric'): ?> bg-blue-100 text-blue-800
                                        <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                        <?php echo e(ucfirst($ward->ward_type)); ?>

                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($ward->floor); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php if($ward->is_active): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php echo e($ward->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Bed Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bed Statistics</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e($ward->beds_count ?? 0); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Occupied Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-red-600">
                                    <?php echo e($ward->occupied_beds_count ?? 0); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Available Beds</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">
                                    <?php echo e(($ward->beds_count ?? 0) - ($ward->occupied_beds_count ?? 0)); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Occupancy Rate</dt>
                                <dd class="mt-1 text-2xl font-semibold text-blue-600">
                                    <?php
                                        $occupancy =
                                            $ward->beds_count > 0
                                                ? round(
                                                    (($ward->occupied_beds_count ?? 0) / $ward->beds_count) * 100,
                                                    2,
                                                )
                                                : 0;
                                    ?>
                                    <?php echo e($occupancy); ?>%
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                        <p class="text-sm text-gray-700"><?php echo e($ward->description ?: 'No description provided.'); ?></p>

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Additional Information</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($ward->created_at->format('d/m/Y H:i')); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($ward->updated_at->format('d/m/Y H:i')); ?>

                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Beds List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Beds in this Ward</h3>
                    <?php if($ward->beds && $ward->beds->count() > 0): ?>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <?php $__currentLoopData = $ward->beds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="border rounded-lg p-3 text-center
                            <?php if($bed->status === 'available'): ?> bg-green-50 border-green-200
                            <?php elseif($bed->status === 'occupied'): ?> bg-red-50 border-red-200
                            <?php elseif($bed->status === 'maintenance'): ?> bg-yellow-50 border-yellow-200
                            <?php else: ?> bg-gray-50 border-gray-200 <?php endif; ?>">
                                    <i
                                        class="fas fa-bed text-2xl mb-2
                                <?php if($bed->status === 'available'): ?> text-green-600
                                <?php elseif($bed->status === 'occupied'): ?> text-red-600
                                <?php elseif($bed->status === 'maintenance'): ?> text-yellow-600
                                <?php else: ?> text-gray-600 <?php endif; ?>"></i>
                                    <p class="text-sm font-medium"><?php echo e($bed->bed_number); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e(ucfirst($bed->status)); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No beds assigned to this ward yet.</p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\wards\show.blade.php ENDPATH**/ ?>