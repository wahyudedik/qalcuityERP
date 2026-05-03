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
     <?php $__env->slot('header', null, []); ?> Certificate of Analysis <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('manufacturing.quality.checks')); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Back to QC Checks
                </a>
        <a href="<?php echo e(route('manufacturing.quality.coa.print', $quality_check_id)); ?>" target="_blank"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Print COA
                </a>
    </div>

    <div class="max-w-4xl mx-auto">
        
        <div class="bg-white rounded-xl border border-gray-200 p-8">
            
            <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">CERTIFICATE OF ANALYSIS</h1>
                <p class="text-sm text-gray-600">COA Number:
                    <strong><?php echo e($coa['coa_number']); ?></strong>
                </p>
            </div>

            
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 mb-3 border-b border-gray-200 pb-2">
                    Product Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Product Name</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($coa['product']['name'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">SKU</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($coa['product']['sku'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Batch Number</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($coa['product']['batch_number'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Work Order</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($coa['work_order'] ?? 'N/A'); ?>

                        </p>
                    </div>
                </div>
            </div>

            
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 mb-3 border-b border-gray-200 pb-2">
                    Inspection Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">QC Check Number</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($coa['check_number']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Inspection Stage</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($coa['stage']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Inspection Date</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($coa['inspection_date']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Inspector</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($coa['inspector'] ?? 'N/A'); ?>

                        </p>
                    </div>
                </div>
            </div>

            
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 mb-3 border-b border-gray-200 pb-2">
                    Test Results</h3>
                <?php if($coa['results'] && count($coa['results']) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500">
                                        Parameter</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                        Value</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                        Min</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                        Max</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                        Unit</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php $__currentLoopData = $coa['results']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900"><?php echo e($result['parameter']); ?>

                                        </td>
                                        <td class="px-4 py-2 text-center text-gray-900">
                                            <?php echo e($result['value'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-2 text-center text-gray-600">
                                            <?php echo e($result['min_value'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-2 text-center text-gray-600">
                                            <?php echo e($result['max_value'] ?? 'N/A'); ?></td>
                                        <td class="px-4 py-2 text-center text-gray-600">
                                            <?php echo e($result['unit'] ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center">
                                            <?php if(isset($result['passed'])): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($result['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                                    <?php echo e($result['passed'] ? 'PASS' : 'FAIL'); ?>

                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 text-center py-4">No test results available</p>
                <?php endif; ?>
            </div>

            
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Summary</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Sample Size</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo e($coa['sample_size']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-green-600">Passed</p>
                        <p class="text-lg font-bold text-green-600"><?php echo e($coa['summary']['passed']); ?>

                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-red-600">Failed</p>
                        <p class="text-lg font-bold text-red-600"><?php echo e($coa['summary']['failed']); ?></p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xs text-gray-500">Pass Rate</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <?php echo e(number_format($coa['summary']['pass_rate'], 1)); ?>%</p>
                </div>
            </div>

            
            <?php if($coa['defects']->count() > 0): ?>
                <div class="mb-6">
                    <h3
                        class="text-lg font-semibold text-gray-900 mb-3 border-b border-gray-200 pb-2">
                        Defects Found</h3>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $coa['defects']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $defect): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-red-800"><?php echo e($defect['code']); ?></span>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($defect['severity'] === 'critical' ? 'bg-red-600 text-white' : 'bg-orange-600 text-white'); ?>">
                                        <?php echo e(ucfirst($defect['severity'])); ?>

                                    </span>
                                </div>
                                <p class="text-xs text-red-700 mt-1"><?php echo e($defect['type']); ?> -
                                    <?php echo e($defect['quantity']); ?> units</p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <div
                class="mb-6 p-4 <?php echo e($coa['status'] === 'Passed' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200'); ?> border rounded-lg">
                <h3
                    class="text-sm font-semibold <?php echo e($coa['status'] === 'Passed' ? 'text-green-800' : 'text-yellow-800'); ?> mb-2">
                    Conclusion</h3>
                <p
                    class="text-sm <?php echo e($coa['status'] === 'Passed' ? 'text-green-700' : 'text-yellow-700'); ?>">
                    <?php echo e($coa['conclusion']); ?></p>
                <div class="mt-3">
                    <p
                        class="text-xs <?php echo e($coa['status'] === 'Passed' ? 'text-green-600' : 'text-yellow-600'); ?>">
                        Status: <strong><?php echo e($coa['status']); ?></strong></p>
                </div>
            </div>

            
            <div class="border-t-2 border-gray-300 pt-6 mt-6">
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <p class="text-xs text-gray-500">Authorized By</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            <?php echo e($coa['authorized_by'] ?? 'N/A'); ?></p>
                        <div class="mt-8 border-t border-gray-300 pt-2">
                            <p class="text-xs text-gray-500">Signature</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e($coa['signature_date']); ?>

                        </p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\quality\coa.blade.php ENDPATH**/ ?>