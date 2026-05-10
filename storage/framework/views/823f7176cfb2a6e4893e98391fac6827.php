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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Create Ministry Report')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="<?php echo e(route('healthcare.ministry-reports.store')); ?>" method="POST"
                class="space-y-6 bg-white shadow-sm sm:rounded-lg p-6">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700">Report Type *</label>
                    <select name="report_type" id="report_type" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select type</option>
                        <option value="monthly">Monthly Report</option>
                        <option value="quarterly">Quarterly Report</option>
                        <option value="annual">Annual Report</option>
                        <option value="episode">Episode Report</option>
                    </select>
                </div>
                <div>
                    <label for="reporting_period" class="block text-sm font-medium text-gray-700">Reporting Period
                        *</label>
                    <input type="date" name="reporting_period" id="reporting_period" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="report_data" class="block text-sm font-medium text-gray-700">Report Data (JSON)
                        *</label>
                    <textarea name="report_data" id="report_data" rows="10" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        placeholder='{"patients": 150, "surgeries": 45, "revenue": 500000000}'></textarea>
                    <p class="mt-1 text-xs text-gray-500">Enter report data in JSON format</p>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <a href="<?php echo e(route('healthcare.ministry-reports.index')); ?>"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create
                        Report</button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/healthcare/ministry-reports/create.blade.php ENDPATH**/ ?>