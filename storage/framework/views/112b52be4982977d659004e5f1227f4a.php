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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('New Compliance Report')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.compliance-reports.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="report_type" class="block text-sm font-medium text-gray-700">Report Type
                                    *</label>
                                <select name="report_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="hipaa" <?php echo e(old('report_type') === 'hipaa' ? 'selected' : ''); ?>>HIPAA
                                    </option>
                                    <option value="jci" <?php echo e(old('report_type') === 'jci' ? 'selected' : ''); ?>>JCI
                                    </option>
                                    <option value="iso" <?php echo e(old('report_type') === 'iso' ? 'selected' : ''); ?>>ISO
                                    </option>
                                    <option value="regulatory"
                                        <?php echo e(old('report_type') === 'regulatory' ? 'selected' : ''); ?>>Regulatory</option>
                                    <option value="internal" <?php echo e(old('report_type') === 'internal' ? 'selected' : ''); ?>>
                                        Internal</option>
                                </select>
                            </div>
                            <div>
                                <label for="report_date" class="block text-sm font-medium text-gray-700">Report Date
                                    *</label>
                                <input type="date" name="report_date" required
                                    value="<?php echo e(old('report_date', today()->format('Y-m-d'))); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="reporting_period_start"
                                    class="block text-sm font-medium text-gray-700">Reporting Period Start *</label>
                                <input type="date" name="reporting_period_start" required
                                    value="<?php echo e(old('reporting_period_start')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="reporting_period_end"
                                    class="block text-sm font-medium text-gray-700">Reporting Period End *</label>
                                <input type="date" name="reporting_period_end" required
                                    value="<?php echo e(old('reporting_period_end')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="findings" class="block text-sm font-medium text-gray-700">Findings</label>
                            <textarea name="findings[]" rows="6"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter findings (one per line)"><?php echo e(old('findings')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Enter one finding per line</p>
                        </div>

                        <div>
                            <label for="recommendations"
                                class="block text-sm font-medium text-gray-700">Recommendations</label>
                            <textarea name="recommendations" rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter recommendations..."><?php echo e(old('recommendations')); ?></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter additional notes..."><?php echo e(old('notes')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.compliance-reports.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Create Report</button>
                    </div>
                </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\compliance-reports\create.blade.php ENDPATH**/ ?>