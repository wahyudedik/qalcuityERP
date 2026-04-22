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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Edit Sterilization Record')); ?> -
            <?php echo e($sterilization->record_id); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.sterilization.update', $sterilization)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="items_description" class="block text-sm font-medium text-gray-700">Items
                                Description *</label>
                            <textarea name="items_description" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('items_description', $sterilization->items_description)); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="method" class="block text-sm font-medium text-gray-700">Sterilization
                                    Method *</label>
                                <select name="method" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="steam"
                                        <?php echo e(old('method', $sterilization->method) === 'steam' ? 'selected' : ''); ?>>Steam
                                        Autoclave</option>
                                    <option value="dry_heat"
                                        <?php echo e(old('method', $sterilization->method) === 'dry_heat' ? 'selected' : ''); ?>>Dry
                                        Heat</option>
                                    <option value="ethylene_oxide"
                                        <?php echo e(old('method', $sterilization->method) === 'ethylene_oxide' ? 'selected' : ''); ?>>
                                        Ethylene Oxide</option>
                                    <option value="hydrogen_peroxide"
                                        <?php echo e(old('method', $sterilization->method) === 'hydrogen_peroxide' ? 'selected' : ''); ?>>
                                        Hydrogen Peroxide</option>
                                    <option value="chemical"
                                        <?php echo e(old('method', $sterilization->method) === 'chemical' ? 'selected' : ''); ?>>
                                        Chemical</option>
                                </select>
                            </div>
                            <div>
                                <label for="operator_name" class="block text-sm font-medium text-gray-700">Operator Name
                                    *</label>
                                <input type="text" name="operator_name" required
                                    value="<?php echo e(old('operator_name', $sterilization->operator_name)); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="sterilized_at" class="block text-sm font-medium text-gray-700">Sterilization
                                    Date/Time</label>
                                <input type="datetime-local" name="sterilized_at"
                                    value="<?php echo e(old('sterilized_at', $sterilization->sterilized_at ? $sterilization->sterilized_at->format('Y-m-d\TH:i') : '')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="temperature" class="block text-sm font-medium text-gray-700">Temperature
                                    (°C)</label>
                                <input type="number" name="temperature"
                                    value="<?php echo e(old('temperature', $sterilization->temperature)); ?>" step="0.1"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration
                                    (minutes)</label>
                                <input type="number" name="duration_minutes"
                                    value="<?php echo e(old('duration_minutes', $sterilization->duration_minutes)); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="completed"
                                        <?php echo e(old('status', $sterilization->status) === 'completed' ? 'selected' : ''); ?>>
                                        Completed</option>
                                    <option value="in_progress"
                                        <?php echo e(old('status', $sterilization->status) === 'in_progress' ? 'selected' : ''); ?>>
                                        In Progress</option>
                                    <option value="failed"
                                        <?php echo e(old('status', $sterilization->status) === 'failed' ? 'selected' : ''); ?>>
                                        Failed</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('notes', $sterilization->notes)); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.sterilization.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Record</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\sterilization\edit.blade.php ENDPATH**/ ?>