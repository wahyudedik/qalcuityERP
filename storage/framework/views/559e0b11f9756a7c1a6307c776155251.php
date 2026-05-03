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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Supply Details')); ?> -
                <?php echo e($supply->item_code); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.medical-supplies.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-box mr-2 text-blue-600"></i>Supply Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Item Code</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($supply->item_code); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo e($supply->name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800"><?php echo e(ucfirst(str_replace('_', ' ', $supply->category))); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($supply->status === 'in_stock' ? 'bg-green-100 text-green-800' : ($supply->status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $supply->status))); ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-warehouse mr-2 text-green-600"></i>Stock Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Current Quantity</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900"><?php echo e($supply->quantity); ?>

                                <?php echo e($supply->unit); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Minimum Stock Level</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($supply->min_stock_level); ?> <?php echo e($supply->unit); ?>

                            </dd>
                        </div>
                        <?php if($supply->storage_location): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Storage Location</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($supply->storage_location); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($supply->expiry_date): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                <dd
                                    class="mt-1 text-sm <?php echo e($supply->expiry_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-900'); ?>">
                                    <?php echo e($supply->expiry_date->format('d/m/Y')); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if($supply->supplier || $supply->notes): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if($supply->supplier): ?>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-truck mr-2 text-orange-600"></i>Supplier Information</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($supply->supplier); ?></dd>
                                </div>
                            </dl>
                        </div>
                    <?php endif; ?>

                    <?php if($supply->notes): ?>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-sticky-note mr-2 text-blue-600"></i>Notes</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($supply->notes); ?></p>
                        </div>
                    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-supplies\show.blade.php ENDPATH**/ ?>