

<?php $__env->startSection('title', 'Menu Items - ' . $menu->name); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?php echo e($menu->name); ?> - Menu Items</h1>
                <p class="text-gray-600">Manage items in this menu</p>
            </div>
            <div class="flex space-x-2">
                <a href="<?php echo e(route('hotel.fb.menus.index')); ?>" class="px-4 py-2 border rounded hover:bg-gray-50">
                    ← Back to Menus
                </a>
                <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Add Item
                </button>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Item Name</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Price</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Cost</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Margin</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Prep Time</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Sold Today</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium"><?php echo e($item->name); ?></div>
                                    <?php if($item->description): ?>
                                        <div class="text-sm text-gray-500"><?php echo e(Str::limit($item->description, 50)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center font-medium">$<?php echo e(number_format($item->price, 2)); ?></td>
                                <td class="px-4 py-3 text-center text-gray-600">$<?php echo e(number_format($item->cost, 2)); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php
                                        $margin =
                                            $item->price > 0 ? (($item->price - $item->cost) / $item->price) * 100 : 0;
                                    ?>
                                    <span
                                        class="<?php echo e($margin >= 60 ? 'text-green-600' : ($margin >= 40 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                        <?php echo e(number_format($margin, 1)); ?>%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center"><?php echo e($item->preparation_time ?? '-'); ?> min</td>
                                <td class="px-4 py-3 text-center"><?php echo e($item->sold_today); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded <?php echo e($item->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'); ?>">
                                        <?php echo e($item->is_available ? 'Available' : 'Unavailable'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editItem(<?php echo e($item->id); ?>)"
                                        class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                                    <form action="<?php echo e(route('hotel.fb.menu-items.destroy', $item)); ?>" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this item?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No items in this menu yet. Add your first item.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Add Menu Item</h3>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="<?php echo e(route('hotel.fb.menu-items.store')); ?>" method="POST" class="p-6">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="menu_id" value="<?php echo e($menu->id); ?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                            <input type="text" name="name" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price ($)</label>
                            <input type="number" name="price" step="0.01" min="0" required
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost ($)</label>
                            <input type="number" name="cost" step="0.01" min="0"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (minutes)</label>
                            <input type="number" name="preparation_time" min="1"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Daily Limit</label>
                            <input type="number" name="daily_limit" min="1" class="w-full border rounded px-3 py-2"
                                placeholder="Optional">
                        </div>
                        <div class="col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_available" value="1" checked class="mr-2">
                                <span class="text-sm">Available</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add
                            Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\fb\menus\items.blade.php ENDPATH**/ ?>