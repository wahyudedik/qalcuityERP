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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Lab Result Details')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.lab-results.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if($labResult->is_critical): ?>
                <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i><strong>CRITICAL RESULT</strong> - Immediate
                    attention required
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-vials mr-2 text-blue-600"></i>Result Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                <?php echo e($labResult->patient->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Test</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($labResult->test->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($labResult->order->order_number ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Result Value</dt>
                            <dd
                                class="mt-1 text-3xl font-bold <?php echo e($labResult->is_critical ? 'text-red-600' : 'text-gray-900'); ?>">
                                <?php echo e($labResult->result_value ?? 'N/A'); ?> <span
                                    class="text-lg"><?php echo e($labResult->unit ?? ''); ?></span></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reference Range</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($labResult->reference_range_min ?? '-'); ?> -
                                <?php echo e($labResult->reference_range_max ?? '-'); ?> <?php echo e($labResult->unit ?? ''); ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-purple-600"></i>Verification Status</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <?php if($labResult->is_verified): ?>
                                    <span
                                        class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-check mr-1"></i>Verified</span>
                                    <div class="text-xs text-gray-500 mt-1">By:
                                        <?php echo e($labResult->verifiedBy->name ?? 'N/A'); ?> on
                                        <?php echo e($labResult->verified_at ? $labResult->verified_at->format('d/m/Y H:i') : '-'); ?>

                                    </div>
                                <?php else: ?>
                                    <span
                                        class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending
                                        Verification</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Critical Flag</dt>
                            <dd class="mt-1">
                                <?php echo e($labResult->is_critical ? '<span class="text-red-600 font-semibold">Yes</span>' : 'No'); ?>

                            </dd>
                        </div>
                    </dl>
                    <?php if($labResult->interpretation): ?>
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Interpretation</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line"><?php echo e($labResult->interpretation); ?>

                            </dd>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(!$labResult->is_verified): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form action="<?php echo e(route('healthcare.lab-results.verify', $labResult)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 text-lg font-semibold"><i
                                class="fas fa-check-double mr-2"></i>Verify This Result</button>
                    </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\lab-results\show.blade.php ENDPATH**/ ?>