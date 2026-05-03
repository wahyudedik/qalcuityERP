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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('QC Test Template Details')); ?> <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo e($template->name); ?></h1>
                    <p class="text-sm text-gray-600"><?php echo e($template->stage_label); ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('qc.templates.edit', $template)); ?>"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="<?php echo e(route('qc.templates.index')); ?>"
                        class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Template Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Information</h2>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Template Name</label>
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($template->name); ?>

                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded <?php echo e($template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'); ?>">
                                        <?php echo e($template->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Product Type</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($template->product_type ?? 'All Types'); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Stage</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($template->stage_label); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Sample Size Formula</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php switch($template->sample_size_formula):
                                        case (1): ?>
                                            √n (Square Root)
                                        <?php break; ?>

                                        <?php case (2): ?>
                                            10% of Lot Size
                                        <?php break; ?>

                                        <?php default: ?>
                                            5% of Lot Size (min 3)
                                    <?php endswitch; ?>
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">AQL</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($template->acceptance_quality_limit); ?>%</p>
                            </div>
                        </div>

                        <?php if($template->instructions): ?>
                            <div>
                                <label class="text-sm text-gray-600">Instructions</label>
                                <p class="text-sm text-gray-900 mt-1 whitespace-pre-line">
                                    <?php echo e($template->instructions); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-4 pt-2 border-t">
                            <div>
                                <label class="text-sm text-gray-600">Created</label>
                                <p class="text-sm text-gray-900">
                                    <?php echo e($template->created_at->format('Y-m-d H:i')); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Last Updated</label>
                                <p class="text-sm text-gray-900">
                                    <?php echo e($template->updated_at->format('Y-m-d H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Parameters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Test Parameters
                        <span
                            class="text-sm font-normal text-gray-500">(<?php echo e(is_array($template->test_parameters) ? count($template->test_parameters) : 0); ?>)</span>
                    </h2>

                    <?php if($template->test_parameters && count($template->test_parameters) > 0): ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php $__currentLoopData = $template->test_parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $param): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 border rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">
                                                <?php echo e($param['name'] ?? 'Unnamed'); ?>

                                                <?php if($param['critical'] ?? false): ?>
                                                    <span
                                                        class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">Critical</span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                Range: <?php echo e($param['min'] ?? '∞'); ?> - <?php echo e($param['max'] ?? '∞'); ?>

                                                <?php if(isset($param['unit']) && $param['unit']): ?>
                                                    <?php echo e($param['unit']); ?>

                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">No test parameters defined.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Inspections -->
            <?php if($template->inspections && $template->inspections->count() > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Inspections</h2>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspection #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Work Order</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $template->inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inspection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <a href="<?php echo e(route('qc.inspections.show', $inspection)); ?>"
                                            class="text-blue-600 hover:text-blue-800">
                                            <?php echo e($inspection->inspection_number); ?>

                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php echo e($inspection->workOrder->number ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php echo e($inspection->stage_label ?? $inspection->stage); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded
                                            <?php echo e($inspection->status == 'passed' ? 'bg-green-100 text-green-700' : ''); ?>

                                            <?php echo e($inspection->status == 'failed' ? 'bg-red-100 text-red-700' : ''); ?>

                                            <?php echo e($inspection->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : ''); ?>

                                            <?php echo e($inspection->status == 'in_progress' ? 'bg-blue-100 text-blue-700' : ''); ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($inspection->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php echo e($inspection->created_at->format('Y-m-d')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\templates\show.blade.php ENDPATH**/ ?>