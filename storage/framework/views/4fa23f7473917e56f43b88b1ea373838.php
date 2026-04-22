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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Add Medical Waste Record')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.medical-waste.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="waste_type" class="block text-sm font-medium text-gray-700">Waste Type
                                    *</label>
                                <select name="waste_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="infectious"
                                        <?php echo e(old('waste_type') === 'infectious' ? 'selected' : ''); ?>>Infectious Waste
                                    </option>
                                    <option value="hazardous" <?php echo e(old('waste_type') === 'hazardous' ? 'selected' : ''); ?>>
                                        Hazardous Waste</option>
                                    <option value="sharps" <?php echo e(old('waste_type') === 'sharps' ? 'selected' : ''); ?>>Sharps
                                    </option>
                                    <option value="pharmaceutical"
                                        <?php echo e(old('waste_type') === 'pharmaceutical' ? 'selected' : ''); ?>>Pharmaceutical
                                    </option>
                                    <option value="general" <?php echo e(old('waste_type') === 'general' ? 'selected' : ''); ?>>
                                        General Waste</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending" <?php echo e(old('status') === 'pending' ? 'selected' : ''); ?>>Pending
                                        Disposal</option>
                                    <option value="disposed" <?php echo e(old('status') === 'disposed' ? 'selected' : ''); ?>>
                                        Disposed</option>
                                    <option value="incinerated" <?php echo e(old('status') === 'incinerated' ? 'selected' : ''); ?>>
                                        Incinerated</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" name="quantity" required value="<?php echo e(old('quantity')); ?>"
                                    min="0" step="0.01"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unit *</label>
                                <select name="unit" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="kg" <?php echo e(old('unit') === 'kg' ? 'selected' : ''); ?>>Kilograms (kg)
                                    </option>
                                    <option value="liters" <?php echo e(old('unit') === 'liters' ? 'selected' : ''); ?>>Liters (L)
                                    </option>
                                    <option value="pieces" <?php echo e(old('unit') === 'pieces' ? 'selected' : ''); ?>>Pieces
                                    </option>
                                    <option value="bags" <?php echo e(old('unit') === 'bags' ? 'selected' : ''); ?>>Bags</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="source" class="block text-sm font-medium text-gray-700">Source
                                    Department</label>
                                <input type="text" name="source" value="<?php echo e(old('source')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Emergency Room">
                            </div>
                            <div>
                                <label for="collection_date" class="block text-sm font-medium text-gray-700">Collection
                                    Date *</label>
                                <input type="date" name="collection_date" required
                                    value="<?php echo e(old('collection_date', now()->format('Y-m-d'))); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="disposal_method" class="block text-sm font-medium text-gray-700">Disposal
                                Method</label>
                            <input type="text" name="disposal_method" value="<?php echo e(old('disposal_method')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Incineration, Autoclaving">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Additional information..."><?php echo e(old('notes')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.medical-waste.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Save Record</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-waste\create.blade.php ENDPATH**/ ?>