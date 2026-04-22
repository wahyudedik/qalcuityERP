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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('New Health Education Material')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.health-education.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                            <input type="text" name="title" required value="<?php echo e(old('title')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Understanding Diabetes Management">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <select name="category" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Category</option>
                                    <option value="nutrition" <?php echo e(old('category') === 'nutrition' ? 'selected' : ''); ?>>
                                        Nutrition & Diet</option>
                                    <option value="exercise" <?php echo e(old('category') === 'exercise' ? 'selected' : ''); ?>>
                                        Exercise & Fitness</option>
                                    <option value="mental_health"
                                        <?php echo e(old('category') === 'mental_health' ? 'selected' : ''); ?>>Mental Health
                                    </option>
                                    <option value="chronic_disease"
                                        <?php echo e(old('category') === 'chronic_disease' ? 'selected' : ''); ?>>Chronic Disease
                                        Management</option>
                                    <option value="preventive_care"
                                        <?php echo e(old('category') === 'preventive_care' ? 'selected' : ''); ?>>Preventive Care
                                    </option>
                                    <option value="maternal_health"
                                        <?php echo e(old('category') === 'maternal_health' ? 'selected' : ''); ?>>Maternal & Child
                                        Health</option>
                                    <option value="medication" <?php echo e(old('category') === 'medication' ? 'selected' : ''); ?>>
                                        Medication Safety</option>
                                    <option value="first_aid" <?php echo e(old('category') === 'first_aid' ? 'selected' : ''); ?>>
                                        First Aid & Emergency</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="draft" <?php echo e(old('status') === 'draft' ? 'selected' : ''); ?>>Draft
                                    </option>
                                    <option value="published" <?php echo e(old('status') === 'published' ? 'selected' : ''); ?>>
                                        Published</option>
                                    <option value="archived" <?php echo e(old('status') === 'archived' ? 'selected' : ''); ?>>
                                        Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="target_audience" class="block text-sm font-medium text-gray-700">Target
                                    Audience</label>
                                <input type="text" name="target_audience" value="<?php echo e(old('target_audience')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Adults, Seniors, Parents">
                            </div>
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                                <input type="text" name="language" value="<?php echo e(old('language', 'Indonesian')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="summary" class="block text-sm font-medium text-gray-700">Summary</label>
                            <textarea name="summary" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Brief description of the material..."><?php echo e(old('summary')); ?></textarea>
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Content *</label>
                            <textarea name="content" required rows="10"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Full educational content..."><?php echo e(old('content')); ?></textarea>
                        </div>

                        <div>
                            <label for="attachment_path" class="block text-sm font-medium text-gray-700">Attachment
                                Path</label>
                            <input type="text" name="attachment_path" value="<?php echo e(old('attachment_path')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., /storage/materials/diabetes-guide.pdf">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.health-education.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Save Material</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\health-education\create.blade.php ENDPATH**/ ?>