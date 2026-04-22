

<?php $__env->startSection('title', 'Variant ' . $variant->sku); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('cosmetic.variants.index')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Variants
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e($variant->sku); ?></h1>
                        <?php if($variant->is_active): ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-1 text-sm text-gray-500"><?php echo e($variant->product->formula_name ?? 'N/A'); ?></p>
                </div>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('edit-variant-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Edit Variant
                    </button>
                    <form method="POST" action="<?php echo e(route('cosmetic.variants.destroy', $variant->id)); ?>"
                        onsubmit="return confirm('Delete this variant?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Variant Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Pricing Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 Pricing</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-500">Current Price</label>
                            <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($variant->price, 0, ',', '.')); ?>

                            </p>
                        </div>
                        <?php if($variant->compare_price): ?>
                            <div>
                                <label class="text-sm text-gray-500">Compare Price</label>
                                <p class="text-2xl font-bold text-gray-500 line-through">Rp
                                    <?php echo e(number_format($variant->compare_price, 0, ',', '.')); ?></p>
                                <?php if($variant->compare_price > $variant->price): ?>
                                    <p class="text-sm text-green-600">
                                        Save
                                        <?php echo e(round((($variant->compare_price - $variant->price) / $variant->compare_price) * 100)); ?>%
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Variant Attributes -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">🎨 Variant Attributes</h2>
                    <?php if($variant->variantAttributes->count() > 0): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $variant->variantAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <?php if($attr->hex_code): ?>
                                            <div class="w-8 h-8 rounded border border-gray-300"
                                                style="background-color: <?php echo e($attr->hex_code); ?>"></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo e(ucfirst($attr->attribute_name)); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo e($attr->attribute_value); ?></div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                        <?php echo e($attr->attribute_type); ?>

                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No attributes defined</p>
                    <?php endif; ?>
                </div>

                <!-- Inventory Information -->
                <?php if($variant->inventory): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 Inventory</h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm text-gray-500">Current Stock</label>
                                <p
                                    class="text-3xl font-bold <?php echo e($variant->inventory->stock_quantity <= $variant->inventory->low_stock_threshold ? 'text-red-600' : 'text-gray-900'); ?>">
                                    <?php echo e(number_format($variant->inventory->stock_quantity, 0)); ?>

                                </p>
                                <p class="text-sm text-gray-500"><?php echo e($variant->inventory->unit); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Low Stock Threshold</label>
                                <p class="text-xl font-semibold text-orange-600">
                                    <?php echo e(number_format($variant->inventory->low_stock_threshold, 0)); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Reserved Stock</label>
                                <p class="text-xl font-semibold text-gray-900">
                                    <?php echo e(number_format($variant->inventory->reserved_quantity, 0)); ?></p>
                            </div>
                        </div>

                        <?php if($variant->inventory->stock_quantity <= $variant->inventory->low_stock_threshold): ?>
                            <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="text-sm text-orange-800 font-medium">Low Stock Alert</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Stock Adjustment -->
                        <form method="POST" action="<?php echo e(route('cosmetic.variants.inventory.adjust', $variant->id)); ?>"
                            class="mt-4 pt-4 border-t border-gray-200">
                            <?php echo csrf_field(); ?>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                                    <select name="type" required
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="add">Add Stock</option>
                                        <option value="remove">Remove Stock</option>
                                        <option value="set">Set Stock</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" name="quantity" required step="0.01" min="0"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="flex items-end">
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                                        Update Stock
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" name="reason" placeholder="Reason for adjustment (optional)"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">📦 Inventory</h2>
                        <form method="POST" action="<?php echo e(route('cosmetic.variants.inventory.create', $variant->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock *</label>
                                    <input type="number" name="stock_quantity" required step="0.01" min="0"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                                    <input type="text" name="unit" required placeholder="pcs, ml, g"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                                    <input type="number" name="low_stock_threshold" step="0.01" min="0"
                                        value="10"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                                Create Inventory Record
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Metadata -->
            <div class="space-y-6">
                <!-- Quick Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Information</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-500">Created At</label>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($variant->created_at->format('d M Y, H:i')); ?>

                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Last Updated</label>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($variant->updated_at->format('d M Y, H:i')); ?>

                            </p>
                        </div>
                        <?php if($variant->notes): ?>
                            <div>
                                <label class="text-sm text-gray-500">Notes</label>
                                <p class="text-sm text-gray-900"><?php echo e($variant->notes); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Product -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">🔗 Related Product</h2>
                    <?php if($variant->product): ?>
                        <a href="<?php echo e(route('cosmetic.formulas.show', $variant->product->id)); ?>"
                            class="block p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            <div class="font-medium text-blue-900"><?php echo e($variant->product->formula_name); ?></div>
                            <div class="text-sm text-blue-700"><?php echo e($variant->product->code); ?></div>
                        </a>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">No related product</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Variant Modal -->
    <div id="edit-variant-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Variant</h3>
                <button onclick="document.getElementById('edit-variant-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?php echo e(route('cosmetic.variants.update', $variant->id)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                    <input type="number" name="price" required step="0.01" min="0"
                        value="<?php echo e($variant->price); ?>"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price</label>
                    <input type="number" name="compare_price" step="0.01" min="0"
                        value="<?php echo e($variant->compare_price); ?>"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"><?php echo e($variant->notes); ?></textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                            <?php echo e($variant->is_active ? 'checked' : ''); ?>

                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('edit-variant-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Update Variant
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\variants\show.blade.php ENDPATH**/ ?>