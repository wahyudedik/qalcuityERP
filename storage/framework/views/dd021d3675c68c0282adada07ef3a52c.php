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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Cosmetic Formulas')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('cosmetic.formulas.builder')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-flask mr-2"></i>Formula Builder
                </a>
        <a href="<?php echo e(route('cosmetic.formulas.create')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Create Formula
                </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Formulas</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total_formulas']); ?>

                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">In Testing</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600"><?php echo e($stats['in_testing']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($stats['approved']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">In Production</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600"><?php echo e($stats['in_production']); ?></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="<?php echo e(route('cosmetic.formulas.index')); ?>"
                    class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Search by code, name, or brand..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <select name="status"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="testing" <?php echo e(request('status') == 'testing' ? 'selected' : ''); ?>>In Testing
                        </option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved
                        </option>
                        <option value="production" <?php echo e(request('status') == 'production' ? 'selected' : ''); ?>>In
                            Production
                        </option>
                        <option value="discontinued" <?php echo e(request('status') == 'discontinued' ? 'selected' : ''); ?>>
                            Discontinued
                        </option>
                    </select>
                    <?php if($productTypes->count() > 0): ?>
                        <select name="product_type"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = $productTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"
                                    <?php echo e(request('product_type') == $type ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                    <button type="submit"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </form>
            </div>

            <!-- Formulas Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Formula
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ingredients</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cost</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo e($formula->formula_code); ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo e($formula->formula_name); ?></div>
                                        <?php if($formula->brand): ?>
                                            <div class="text-xs text-gray-400"><?php echo e($formula->brand); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                            <?php echo e(ucfirst($formula->product_type)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($formula->status == 'draft'): ?> bg-gray-100 text-gray-800
                            <?php elseif($formula->status == 'testing'): ?> bg-yellow-100 text-yellow-800
                            <?php elseif($formula->status == 'approved'): ?> bg-green-100 text-green-800
                            <?php elseif($formula->status == 'production'): ?> bg-blue-100 text-blue-800
                            <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                            <?php echo e($formula->status_label); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($formula->ingredients->count()); ?> items
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Rp
                                            <?php echo e(number_format($formula->total_cost, 0, ',', '.')); ?></div>
                                        <?php if($formula->cost_per_unit): ?>
                                            <div class="text-xs text-gray-500">Rp
                                                <?php echo e(number_format($formula->cost_per_unit, 0, ',', '.')); ?>/unit</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($formula->created_at->format('d M Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="<?php echo e(route('cosmetic.formulas.show', $formula)); ?>"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('cosmetic.formulas.edit', $formula)); ?>"
                                                class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST"
                                                action="<?php echo e(route('cosmetic.formulas.destroy', $formula)); ?>"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this formula?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="text-gray-400">
                                            <i class="fas fa-flask text-6xl text-gray-300 mb-4"></i>
                                            <p class="mt-2 text-sm">No formulas found</p>
                                            <a href="<?php echo e(route('cosmetic.formulas.create')); ?>"
                                                class="mt-2 inline-block text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-plus mr-1"></i>Create your first formula
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if($formulas->hasPages()): ?>
                <div class="mt-4">
                    <?php echo e($formulas->links()); ?>

                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\formulas\index.blade.php ENDPATH**/ ?>