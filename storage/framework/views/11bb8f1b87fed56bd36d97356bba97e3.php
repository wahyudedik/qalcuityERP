

<?php $__env->startSection('title', 'Channel Inventory'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Channel Inventory</h1>
                    <p class="mt-1 text-sm text-gray-500">Monitor and manage inventory allocation across channels</p>
                </div>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mb-4">
            <a href="<?php echo e(route('cosmetic.distribution.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Distribution Channels
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="<?php echo e(route('cosmetic.distribution.inventory')); ?>" class="flex gap-4">
                <div class="flex-1">
                    <select name="channel_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Channels</option>
                        <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($channel->id); ?>"
                                <?php echo e(request('channel_id') == $channel->id ? 'selected' : ''); ?>>
                                <?php echo e($channel->channel_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Allocated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                            Restock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $inventory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $available = $item->allocated_stock - $item->sold_stock - $item->reserved_stock;
                            $isLowStock = $available < 10;
                        ?>
                        <tr class="hover:bg-gray-50 <?php echo e($isLowStock ? 'bg-red-50' : ''); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium"><?php echo e($item->channel->channel_name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($item->channel->channel_code); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if($item->product): ?>
                                    <div class="font-medium"><?php echo e($item->product->formula_name); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($item->product->formula_code); ?></div>
                                <?php else: ?>
                                    <span class="text-gray-400">Product Deleted</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(number_format($item->allocated_stock, 0)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(number_format($item->sold_stock, 0)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(number_format($item->reserved_stock, 0)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold <?php echo e($isLowStock ? 'text-red-600' : 'text-green-600'); ?>">
                                    <?php echo e(number_format($available, 0)); ?>

                                </span>
                                <?php if($isLowStock): ?>
                                    <div class="text-xs text-red-600 font-semibold">Low Stock!</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if($item->last_restock_date): ?>
                                    <?php echo e($item->last_restock_date->format('d M Y')); ?>

                                <?php else: ?>
                                    <span class="text-gray-400">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button"
                                    onclick="openRestockModal(<?php echo e($item->id); ?>, '<?php echo e($item->channel->channel_name); ?>', '<?php echo e($item->product ? $item->product->formula_name : 'Unknown'); ?>')"
                                    class="text-blue-600 hover:text-blue-900">
                                    Restock
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-2">No inventory records found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <?php echo e($inventory->links()); ?>

        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-40 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Restock Inventory</h3>
                <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="restockInfo" class="mb-4 p-3 bg-blue-50 rounded-lg">
                <!-- Dynamic content -->
            </div>

            <form id="restockForm" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Add *</label>
                    <input type="number" name="quantity" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openRestockModal(inventoryId, channelName, productName) {
                document.getElementById('restockInfo').innerHTML = `
                    <div class="text-sm">
                        <div class="font-medium">${channelName}</div>
                        <div class="text-gray-600">${productName}</div>
                    </div>
                `;
                document.getElementById('restockForm').action = `/cosmetic/distribution/inventory/${inventoryId}/restock`;
                document.getElementById('restockModal').classList.remove('hidden');
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\distribution\inventory.blade.php ENDPATH**/ ?>