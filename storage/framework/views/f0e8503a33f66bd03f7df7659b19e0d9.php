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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('QC Inspection Details')); ?> - <?php echo e($inspection->inspection_number); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($inspection->inspection_number); ?>

                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($inspection->stage_label); ?></p>
                </div>
                <div class="flex gap-2">
                    <?php if($inspection->status == 'pending' || $inspection->status == 'in_progress'): ?>
                        <a href="<?php echo e(route('qc.inspections.edit', $inspection)); ?>"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-edit mr-2"></i>Record Results
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('qc.inspections.index')); ?>"
                        class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Inspection Details -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Inspection Information</h2>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Inspection Number</label>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->inspection_number); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded 
                                        <?php echo e($inspection->status_color == 'green' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : ''); ?>

                                        <?php echo e($inspection->status_color == 'red' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : ''); ?>

                                        <?php echo e($inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : ''); ?>

                                        <?php echo e($inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : ''); ?>">
                                        <?php echo e(str_replace('_', ' ', ucfirst($inspection->status))); ?>

                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Work Order</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->workOrder->number ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Stage</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->stage_label); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Template</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->template->name ?? 'Manual Inspection'); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Inspector</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->inspector->name ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Sample Size</label>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->sample_size); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Passed</label>
                                <p class="text-lg font-bold text-green-600"><?php echo e($inspection->sample_passed); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Failed</label>
                                <p class="text-lg font-bold text-red-600"><?php echo e($inspection->sample_failed); ?></p>
                            </div>
                        </div>

                        <?php if($inspection->pass_rate !== null): ?>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Pass Rate</label>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                        <div class="h-4 rounded-full <?php echo e($inspection->pass_rate >= 95 ? 'bg-green-500' : ($inspection->pass_rate >= 85 ? 'bg-yellow-500' : 'bg-red-500')); ?>"
                                            style="width: <?php echo e($inspection->pass_rate); ?>%"></div>
                                    </div>
                                    <span
                                        class="text-lg font-bold <?php echo e($inspection->pass_rate >= 95 ? 'text-green-600' : ($inspection->pass_rate >= 85 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                        <?php echo e($inspection->pass_rate); ?>%
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($inspection->grade): ?>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Grade</label>
                                <p
                                    class="text-2xl font-bold 
                                <?php echo e($inspection->grade == 'A' ? 'text-green-600' : ''); ?>

                                <?php echo e($inspection->grade == 'B' ? 'text-blue-600' : ''); ?>

                                <?php echo e($inspection->grade == 'C' ? 'text-yellow-600' : ''); ?>

                                <?php echo e($inspection->grade == 'D' ? 'text-orange-600' : ''); ?>

                                <?php echo e($inspection->grade == 'F' ? 'text-red-600' : ''); ?>">
                                    <?php echo e($inspection->grade); ?>

                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if($inspection->inspected_at): ?>
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Inspected At</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <?php echo e($inspection->inspected_at->format('Y-m-d H:i:s')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Test Results -->
                <?php if($inspection->test_results): ?>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Test Results</h2>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php $__currentLoopData = $inspection->test_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 border dark:border-gray-700 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                                <?php echo e($result['parameter']); ?></h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Value: <span class="font-medium"><?php echo e($result['value']); ?></span>
                                                <?php if(isset($result['unit'])): ?>
                                                    <?php echo e($result['unit']); ?>

                                                <?php endif; ?>
                                            </p>
                                            <?php if(isset($result['notes']) && $result['notes']): ?>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Notes:
                                                    <?php echo e($result['notes']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded 
                                    <?php echo e($result['passed'] ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'); ?>">
                                            <?php echo e($result['passed'] ? 'PASSED' : 'FAILED'); ?>

                                        </span>
                                    </div>
                                    <?php if(isset($result['error']) && $result['error']): ?>
                                        <p class="text-xs text-red-600 mt-2"><i
                                                class="fas fa-exclamation-triangle mr-1"></i><?php echo e($result['error']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($inspection->corrective_action || $inspection->defects_found || $inspection->inspector_notes): ?>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Additional Notes</h2>

                    <div class="space-y-3">
                        <?php if($inspection->inspector_notes): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Inspector
                                    Notes</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                    <?php echo e($inspection->inspector_notes); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if($inspection->defects_found): ?>
                            <div>
                                <label class="text-sm font-medium text-red-700 dark:text-red-400">Defects Found</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                    <?php echo e($inspection->defects_found); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if($inspection->corrective_action): ?>
                            <div>
                                <label class="text-sm font-medium text-orange-700 dark:text-orange-400">Corrective
                                    Action</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                    <?php echo e($inspection->corrective_action); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\inspections\show.blade.php ENDPATH**/ ?>