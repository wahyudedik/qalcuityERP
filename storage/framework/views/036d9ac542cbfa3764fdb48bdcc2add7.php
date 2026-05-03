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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Sterilization Details')); ?> -
                <?php echo e($sterilization->record_id); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.sterilization.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-shield-virus mr-2 text-blue-600"></i>Sterilization Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Record ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($sterilization->record_id); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Method</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst(str_replace('_', ' ', $sterilization->method))); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($sterilization->status === 'completed' ? 'bg-green-100 text-green-800' : ($sterilization->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $sterilization->status))); ?></span>
                            </dd>
                        </div>
                        <?php if($sterilization->operator_name): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Operator</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($sterilization->operator_name); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-thermometer-half mr-2 text-red-600"></i>Process Parameters</h3>
                    <dl class="space-y-4">
                        <?php if($sterilization->sterilized_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sterilized At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($sterilization->sterilized_at->format('d/m/Y H:i')); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($sterilization->temperature): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Temperature</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($sterilization->temperature); ?>°C</dd>
                            </div>
                        <?php endif; ?>
                        <?php if($sterilization->duration_minutes): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($sterilization->duration_minutes); ?> minutes
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-list mr-2 text-purple-600"></i>Items Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($sterilization->items_description); ?></p>
            </div>

            <?php if($sterilization->notes): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-orange-600"></i>Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($sterilization->notes); ?></p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\sterilization\show.blade.php ENDPATH**/ ?>