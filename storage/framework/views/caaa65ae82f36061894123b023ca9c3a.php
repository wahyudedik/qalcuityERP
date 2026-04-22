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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Edit Lab Order')); ?> -
            <?php echo e($labOrder->order_number); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.lab-orders.update', $labOrder)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="patient_visit_id" class="block text-sm font-medium text-gray-700">Patient Visit
                                *</label>
                            <select name="patient_visit_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Visit</option>
                                <?php $__currentLoopData = $visits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($visit->id); ?>"
                                        <?php echo e(old('patient_visit_id', $labOrder->patient_visit_id) == $visit->id ? 'selected' : ''); ?>>
                                        <?php echo e($visit->patient->name); ?> - <?php echo e($visit->visit_number); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label for="lab_test_catalog_id" class="block text-sm font-medium text-gray-700">Test
                                *</label>
                            <select name="lab_test_catalog_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Test</option>
                                <?php $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($test->id); ?>"
                                        <?php echo e(old('lab_test_catalog_id', $labOrder->lab_test_catalog_id) == $test->id ? 'selected' : ''); ?>>
                                        <?php echo e($test->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                            <select name="priority" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="routine"
                                    <?php echo e(old('priority', $labOrder->priority) === 'routine' ? 'selected' : ''); ?>>Routine
                                </option>
                                <option value="urgent"
                                    <?php echo e(old('priority', $labOrder->priority) === 'urgent' ? 'selected' : ''); ?>>Urgent
                                </option>
                                <option value="stat"
                                    <?php echo e(old('priority', $labOrder->priority) === 'stat' ? 'selected' : ''); ?>>STAT
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending"
                                    <?php echo e(old('status', $labOrder->status) === 'pending' ? 'selected' : ''); ?>>Pending
                                </option>
                                <option value="in_progress"
                                    <?php echo e(old('status', $labOrder->status) === 'in_progress' ? 'selected' : ''); ?>>In
                                    Progress</option>
                                <option value="completed"
                                    <?php echo e(old('status', $labOrder->status) === 'completed' ? 'selected' : ''); ?>>Completed
                                </option>
                                <option value="cancelled"
                                    <?php echo e(old('status', $labOrder->status) === 'cancelled' ? 'selected' : ''); ?>>Cancelled
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="clinical_notes" class="block text-sm font-medium text-gray-700">Clinical
                                Notes</label>
                            <textarea name="clinical_notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('clinical_notes', $labOrder->clinical_notes)); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.lab-orders.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Order</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\lab-orders\edit.blade.php ENDPATH**/ ?>