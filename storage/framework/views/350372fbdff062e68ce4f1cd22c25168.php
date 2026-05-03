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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Equipment Details')); ?> -
                <?php echo e($equipment->equipment_code); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.medical-equipment.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Equipment Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Equipment Code</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($equipment->equipment_code); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo e($equipment->name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst($equipment->equipment_type)); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($equipment->status === 'available' ? 'bg-green-100 text-green-800' : ($equipment->status === 'in_use' ? 'bg-blue-100 text-blue-800' : ($equipment->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'))); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $equipment->status))); ?></span>
                            </dd>
                        </div>
                        <?php if($equipment->location): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($equipment->location); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-cogs mr-2 text-purple-600"></i>Manufacturer Details</h3>
                    <dl class="space-y-4">
                        <?php if($equipment->manufacturer): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Manufacturer</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($equipment->manufacturer); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($equipment->model): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Model</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($equipment->model); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($equipment->serial_number): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Serial Number</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($equipment->serial_number); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-calendar mr-2 text-green-600"></i>Warranty & Maintenance</h3>
                    <dl class="space-y-4">
                        <?php if($equipment->purchase_date): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Purchase Date</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($equipment->purchase_date->format('d/m/Y')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if($equipment->warranty_expiry): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Warranty Expiry</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($equipment->warranty_expiry->format('d/m/Y')); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($equipment->last_maintenance_date): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Maintenance</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($equipment->last_maintenance_date->format('d/m/Y')); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <?php if($equipment->notes): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                class="fas fa-sticky-note mr-2 text-orange-600"></i>Notes</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($equipment->notes); ?></p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-equipment\show.blade.php ENDPATH**/ ?>