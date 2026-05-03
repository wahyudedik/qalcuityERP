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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Lab Order Details')); ?> -
                <?php echo e($labOrder->order_number); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.lab-orders.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Order Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($labOrder->order_number); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($labOrder->patient->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Test</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($labOrder->test->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($labOrder->priority === 'stat' ? 'bg-red-100 text-red-800' : ($labOrder->priority === 'urgent' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')); ?>"><?php echo e(ucfirst($labOrder->priority)); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($labOrder->status === 'completed' ? 'bg-green-100 text-green-800' : ($labOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $labOrder->status))); ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timeline</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ordered At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($labOrder->ordered_at ? $labOrder->ordered_at->format('d/m/Y H:i') : '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Collected At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($labOrder->collected_at ? $labOrder->collected_at->format('d/m/Y H:i') : '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($labOrder->completed_at ? $labOrder->completed_at->format('d/m/Y H:i') : '-'); ?></dd>
                        </div>
                    </dl>
                    <?php if($labOrder->clinical_notes): ?>
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Clinical Notes</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line"><?php echo e($labOrder->clinical_notes); ?>

                            </dd>
                        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\lab-orders\show.blade.php ENDPATH**/ ?>