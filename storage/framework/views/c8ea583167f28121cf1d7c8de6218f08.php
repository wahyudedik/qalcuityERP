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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('QC Test Templates')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('qc.templates.create')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>New Template
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Templates Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Template Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Product Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Parameters</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    AQL</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspections</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-sm font-medium text-blue-600"><?php echo e($template->name); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($template->product_type ?? 'All Types'); ?></td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                            <?php echo e($template->stage_label); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e(is_array($template->test_parameters) ? count($template->test_parameters) : 0); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($template->acceptance_quality_limit); ?>%</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($template->inspections_count); ?></td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded <?php echo e($template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo e($template->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <a href="<?php echo e(route('qc.templates.show', $template)); ?>"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('qc.templates.edit', $template)); ?>"
                                                class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('qc.templates.toggle', $template)); ?>" method="POST"
                                                class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-yellow-600 hover:text-yellow-800">
                                                    <i
                                                        class="fas fa-<?php echo e($template->is_active ? 'pause' : 'play'); ?>"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('qc.templates.destroy', $template)); ?>"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Delete this template?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No QC test templates found</p>
                                        <a href="<?php echo e(route('qc.templates.create')); ?>"
                                            class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                            Create your first template
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($templates->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\templates\index.blade.php ENDPATH**/ ?>