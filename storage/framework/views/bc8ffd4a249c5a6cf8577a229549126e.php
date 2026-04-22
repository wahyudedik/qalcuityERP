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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Add Medical Equipment')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.medical-equipment.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Equipment Name
                                    *</label>
                                <input type="text" name="name" required value="<?php echo e(old('name')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., ECG Machine">
                            </div>
                            <div>
                                <label for="equipment_type" class="block text-sm font-medium text-gray-700">Equipment
                                    Type *</label>
                                <select name="equipment_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="diagnostic"
                                        <?php echo e(old('equipment_type') === 'diagnostic' ? 'selected' : ''); ?>>Diagnostic
                                    </option>
                                    <option value="therapeutic"
                                        <?php echo e(old('equipment_type') === 'therapeutic' ? 'selected' : ''); ?>>Therapeutic
                                    </option>
                                    <option value="monitoring"
                                        <?php echo e(old('equipment_type') === 'monitoring' ? 'selected' : ''); ?>>Monitoring
                                    </option>
                                    <option value="surgical"
                                        <?php echo e(old('equipment_type') === 'surgical' ? 'selected' : ''); ?>>Surgical</option>
                                    <option value="laboratory"
                                        <?php echo e(old('equipment_type') === 'laboratory' ? 'selected' : ''); ?>>Laboratory
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="manufacturer"
                                    class="block text-sm font-medium text-gray-700">Manufacturer</label>
                                <input type="text" name="manufacturer" value="<?php echo e(old('manufacturer')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                                <input type="text" name="model" value="<?php echo e(old('model')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial
                                    Number</label>
                                <input type="text" name="serial_number" value="<?php echo e(old('serial_number')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" name="location" value="<?php echo e(old('location')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Room 201">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase
                                    Date</label>
                                <input type="date" name="purchase_date" value="<?php echo e(old('purchase_date')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="warranty_expiry" class="block text-sm font-medium text-gray-700">Warranty
                                    Expiry</label>
                                <input type="date" name="warranty_expiry" value="<?php echo e(old('warranty_expiry')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="available" <?php echo e(old('status') === 'available' ? 'selected' : ''); ?>>
                                    Available</option>
                                <option value="in_use" <?php echo e(old('status') === 'in_use' ? 'selected' : ''); ?>>In Use
                                </option>
                                <option value="maintenance" <?php echo e(old('status') === 'maintenance' ? 'selected' : ''); ?>>
                                    Under Maintenance</option>
                                <option value="out_of_service"
                                    <?php echo e(old('status') === 'out_of_service' ? 'selected' : ''); ?>>Out of Service</option>
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Additional information..."><?php echo e(old('notes')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.medical-equipment.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Save Equipment</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-equipment\create.blade.php ENDPATH**/ ?>