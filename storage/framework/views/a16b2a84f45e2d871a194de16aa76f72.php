

<?php $__env->startSection('title', 'Packaging Materials'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Packaging Materials</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage primary & secondary packaging specifications</p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('cosmetic.packaging.labels')); ?>"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                        Label Versions
                    </a>
                    <button onclick="document.getElementById('add-material-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Material
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Materials</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_materials']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Primary Packaging</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e($stats['primary_packaging']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Secondary Packaging</p>
                        <p class="text-2xl font-bold text-orange-600"><?php echo e($stats['secondary_packaging']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7l9-4 9 4v10l-9 4-9-4V7z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Recyclable</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo e($stats['recyclable']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="<?php echo e(route('cosmetic.packaging.index')); ?>"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Material name..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="primary" <?php echo e(request('type') == 'primary' ? 'selected' : ''); ?>>Primary</option>
                        <option value="secondary" <?php echo e(request('type') == 'secondary' ? 'selected' : ''); ?>>Secondary</option>
                        <option value="tertiary" <?php echo e(request('type') == 'tertiary' ? 'selected' : ''); ?>>Tertiary</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <option value="bottle" <?php echo e(request('category') == 'bottle' ? 'selected' : ''); ?>>Bottle</option>
                        <option value="tube" <?php echo e(request('category') == 'tube' ? 'selected' : ''); ?>>Tube</option>
                        <option value="jar" <?php echo e(request('category') == 'jar' ? 'selected' : ''); ?>>Jar</option>
                        <option value="box" <?php echo e(request('category') == 'box' ? 'selected' : ''); ?>>Box</option>
                        <option value="carton" <?php echo e(request('category') == 'carton' ? 'selected' : ''); ?>>Carton</option>
                        <option value="label" <?php echo e(request('category') == 'label' ? 'selected' : ''); ?>>Label</option>
                        <option value="cap" <?php echo e(request('category') == 'cap' ? 'selected' : ''); ?>>Cap</option>
                        <option value="pump" <?php echo e(request('category') == 'pump' ? 'selected' : ''); ?>>Pump</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Materials Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recyclable</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono font-medium text-gray-900"><?php echo e($material->sku); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($material->material_name); ?></div>
                                <?php if($material->dimensions): ?>
                                    <div class="text-xs text-gray-500"><?php echo e($material->dimensions); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full 
                            <?php if($material->material_type == 'primary'): ?> bg-green-100 text-green-800
                            <?php elseif($material->material_type == 'secondary'): ?> bg-orange-100 text-orange-800
                            <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                    <?php echo e($material->type_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e($material->category_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($material->unit_cost): ?>
                                    <div class="text-sm font-medium text-gray-900">Rp
                                        <?php echo e(number_format($material->unit_cost, 0, ',', '.')); ?></div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($material->is_recyclable): ?>
                                    <span class="text-green-600">✓ Yes</span>
                                <?php else: ?>
                                    <span class="text-gray-500">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST"
                                    action="<?php echo e(route('cosmetic.packaging.materials.destroy', $material->id)); ?>"
                                    class="inline" onsubmit="return confirm('Delete this material?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-2">No packaging materials found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($materials->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($materials->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Material Modal -->
    <div id="add-material-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Packaging Material</h3>
                <button onclick="document.getElementById('add-material-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?php echo e(route('cosmetic.packaging.materials.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Material Name *</label>
                        <input type="text" name="material_name" required placeholder="e.g., Bottle 50ml PET"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input type="text" name="sku" placeholder="Auto-generated if empty"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="material_type" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="primary">Primary Packaging</option>
                            <option value="secondary">Secondary Packaging</option>
                            <option value="tertiary">Tertiary Packaging</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="material_category" required
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <option value="bottle">Bottle</option>
                            <option value="tube">Tube</option>
                            <option value="jar">Jar</option>
                            <option value="box">Box</option>
                            <option value="carton">Carton</option>
                            <option value="label">Label</option>
                            <option value="cap">Cap</option>
                            <option value="pump">Pump</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dimensions</label>
                        <input type="text" name="dimensions" placeholder="50ml, 30g, 10x5cm"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label>
                        <input type="number" name="unit_cost" step="0.01" min="0" placeholder="0.00"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Material Composition</label>
                        <input type="text" name="material_composition" placeholder="PET, HDPE, Glass"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                        <input type="text" name="supplier_name" placeholder="Supplier name"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="text" name="color" placeholder="Clear, White, Blue"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_recyclable" value="1"
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Recyclable</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('add-material-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Material
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\packaging\index.blade.php ENDPATH**/ ?>