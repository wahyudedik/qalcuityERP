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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Edit Ward')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.wards.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="<?php echo e(route('healthcare.wards.update', $ward)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="space-y-6">
                            <div>
                                <label for="ward_code" class="block text-sm font-medium text-gray-700">Ward Code</label>
                                <input type="text" name="ward_code" id="ward_code" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="<?php echo e(old('ward_code', $ward->ward_code)); ?>" readonly>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Ward Name</label>
                                <input type="text" name="name" id="name" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="<?php echo e(old('name', $ward->name)); ?>">
                            </div>

                            <div>
                                <label for="ward_type" class="block text-sm font-medium text-gray-700">Ward Type</label>
                                <select name="ward_type" id="ward_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="general" <?php if(old('ward_type', $ward->ward_type) === 'general'): echo 'selected'; endif; ?>>General</option>
                                    <option value="icu" <?php if(old('ward_type', $ward->ward_type) === 'icu'): echo 'selected'; endif; ?>>ICU</option>
                                    <option value="emergency" <?php if(old('ward_type', $ward->ward_type) === 'emergency'): echo 'selected'; endif; ?>>Emergency</option>
                                    <option value="maternity" <?php if(old('ward_type', $ward->ward_type) === 'maternity'): echo 'selected'; endif; ?>>Maternity</option>
                                    <option value="pediatric" <?php if(old('ward_type', $ward->ward_type) === 'pediatric'): echo 'selected'; endif; ?>>Pediatric</option>
                                    <option value="surgical" <?php if(old('ward_type', $ward->ward_type) === 'surgical'): echo 'selected'; endif; ?>>Surgical</option>
                                    <option value="psychiatric" <?php if(old('ward_type', $ward->ward_type) === 'psychiatric'): echo 'selected'; endif; ?>>Psychiatric</option>
                                </select>
                            </div>

                            <div>
                                <label for="floor" class="block text-sm font-medium text-gray-700">Floor</label>
                                <input type="text" name="floor" id="floor" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="<?php echo e(old('floor', $ward->floor)); ?>">
                            </div>

                            <div>
                                <label for="description"
                                    class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('description', $ward->description)); ?></textarea>
                            </div>

                            <div>
                                <label for="is_active" class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        <?php if(old('is_active', $ward->is_active)): echo 'checked'; endif; ?>
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="<?php echo e(route('healthcare.wards.index')); ?>"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Update Ward
                            </button>
                        </div>
                    </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\wards\edit.blade.php ENDPATH**/ ?>