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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Record Test Results')); ?> - <?php echo e($inspection->inspection_number); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('qc.inspections.show', $inspection)); ?>"
                class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to Details
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Inspection Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Inspection Details</h2>

                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-gray-600">Work Order</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($inspection->workOrder->number ?? 'N/A'); ?></p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600">Template</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($inspection->template->name ?? 'Manual Inspection'); ?></p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600">Stage</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($inspection->stage_label); ?></p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600">Sample Size</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($inspection->sample_size); ?></p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded 
                                        <?php echo e($inspection->status_color == 'green' ? 'bg-green-100 text-green-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'red' ? 'bg-red-100 text-red-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700' : ''); ?>">
                                        <?php echo e(str_replace('_', ' ', ucfirst($inspection->status))); ?>

                                    </span>
                                </p>
                            </div>
                        </div>

                        <?php if($inspection->template && $inspection->template->instructions): ?>
                            <div class="mt-4 p-3 bg-blue-50 rounded">
                                <label class="text-sm font-medium text-blue-900">Instructions</label>
                                <p class="text-sm text-blue-800 mt-1">
                                    <?php echo e($inspection->template->instructions); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Test Results Form -->
                <div class="lg:col-span-2">
                    <form action="<?php echo e(route('qc.inspections.update', $inspection)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h2>

                            <?php if($inspection->template && $inspection->template->test_parameters): ?>
                                <!-- Template-based testing -->
                                <div id="test-results-container">
                                    <?php $__currentLoopData = $inspection->template->test_parameters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $parameter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-4 p-4 border rounded-lg"
                                            data-parameter-index="<?php echo e($index); ?>">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">
                                                        <?php echo e($parameter['name']); ?>

                                                        <?php if($parameter['critical'] ?? false): ?>
                                                            <span class="text-red-500">*</span>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="hidden"
                                                        name="test_results[<?php echo e($index); ?>][parameter]"
                                                        value="<?php echo e($parameter['name']); ?>">
                                                    <input type="number" step="any"
                                                        name="test_results[<?php echo e($index); ?>][value]"
                                                        class="w-full rounded-md border-gray-300"
                                                        placeholder="Enter value">
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 mb-1">
                                                        Range: <?php echo e($parameter['min'] ?? '∞'); ?> -
                                                        <?php echo e($parameter['max'] ?? '∞'); ?> <?php echo e($parameter['unit'] ?? ''); ?>

                                                    </label>
                                                    <input type="hidden" name="test_results[<?php echo e($index); ?>][min]"
                                                        value="<?php echo e($parameter['min'] ?? ''); ?>">
                                                    <input type="hidden" name="test_results[<?php echo e($index); ?>][max]"
                                                        value="<?php echo e($parameter['max'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <input type="text" name="test_results[<?php echo e($index); ?>][notes]"
                                                    class="w-full rounded-md border-gray-300"
                                                    placeholder="Notes (optional)">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <!-- Manual testing -->
                                <div id="manual-test-container">
                                    <div class="mb-4 p-4 border rounded-lg manual-test-row">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1">Parameter
                                                    Name</label>
                                                <input type="text" name="test_results[0][parameter]"
                                                    class="w-full rounded-md border-gray-300">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                                                <input type="number" step="any" name="test_results[0][value]"
                                                    class="w-full rounded-md border-gray-300">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                                <input type="text" name="test_results[0][notes]"
                                                    class="w-full rounded-md border-gray-300">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="addManualTestRow()"
                                    class="mb-4 text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-plus mr-1"></i>Add Test Parameter
                                </button>
                            <?php endif; ?>

                            <!-- Inspector Notes -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Inspector
                                    Notes</label>
                                <textarea name="inspector_notes" rows="3"
                                    class="w-full rounded-md border-gray-300"><?php echo e(old('inspector_notes', $inspection->inspector_notes)); ?></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">
                                    <i class="fas fa-save mr-2"></i>Save Results
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if($inspection->status == 'in_progress' || $inspection->status == 'pending'): ?>
                        <!-- Decision Buttons -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Inspection Decision
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Pass -->
                                <form action="<?php echo e(route('qc.inspections.pass', $inspection)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="p-4 border-2 border-green-300 rounded-lg">
                                        <i class="fas fa-check-circle text-3xl text-green-500 mb-2"></i>
                                        <h3 class="font-semibold text-green-700">Pass</h3>
                                        <p class="text-sm text-gray-600 mb-3">All tests passed</p>
                                        <input type="text" name="notes" placeholder="Optional notes"
                                            class="w-full mb-2 rounded-md border-gray-300 text-sm">
                                        <button type="submit"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                            Mark as Passed
                                        </button>
                                    </div>
                                </form>

                                <!-- Conditional Pass -->
                                <form action="<?php echo e(route('qc.inspections.conditional-pass', $inspection)); ?>"
                                    method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="p-4 border-2 border-yellow-300 rounded-lg">
                                        <i class="fas fa-exclamation-triangle text-3xl text-yellow-500 mb-2"></i>
                                        <h3 class="font-semibold text-yellow-700">Conditional Pass
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-3">Minor issues found</p>
                                        <textarea name="notes" placeholder="Conditions and notes *" required rows="2"
                                            class="w-full mb-2 rounded-md border-gray-300 text-sm"></textarea>
                                        <button type="submit"
                                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                                            Conditional Pass
                                        </button>
                                    </div>
                                </form>

                                <!-- Fail -->
                                <form action="<?php echo e(route('qc.inspections.fail', $inspection)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="p-4 border-2 border-red-300 rounded-lg">
                                        <i class="fas fa-times-circle text-3xl text-red-500 mb-2"></i>
                                        <h3 class="font-semibold text-red-700">Fail</h3>
                                        <p class="text-sm text-gray-600 mb-3">Critical failures</p>
                                        <textarea name="corrective_action" placeholder="Corrective action required *" required rows="2"
                                            class="w-full mb-2 rounded-md border-gray-300 text-sm"></textarea>
                                        <input type="text" name="defects" placeholder="Defects found"
                                            class="w-full mb-2 rounded-md border-gray-300 text-sm">
                                        <button type="submit"
                                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                            Mark as Failed
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let manualTestIndex = 1;

            function addManualTestRow() {
                const container = document.getElementById('manual-test-container');
                const newRow = document.createElement('div');
                newRow.className = 'mb-4 p-4 border rounded-lg manual-test-row';
                newRow.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Name</label>
                    <input type="text" name="test_results[${manualTestIndex}][parameter]" class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                    <input type="number" step="any" name="test_results[${manualTestIndex}][value]" class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" name="test_results[${manualTestIndex}][notes]" class="w-full rounded-md border-gray-300">
                </div>
            </div>
        `;
                container.appendChild(newRow);
                manualTestIndex++;
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\inspections\edit.blade.php ENDPATH**/ ?>