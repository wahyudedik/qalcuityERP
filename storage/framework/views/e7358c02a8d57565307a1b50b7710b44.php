<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Linen Inventory']); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Linen Inventory</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">Track and manage linen stock levels</p>
            </div>
            <button onclick="openMovementModal()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Record Movement
            </button>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="category" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">All Categories</option>
                    <option value="Bathroom" <?php echo e(request('category') === 'Bathroom' ? 'selected' : ''); ?>>Bathroom
                    </option>
                    <option value="Bedroom" <?php echo e(request('category') === 'Bedroom' ? 'selected' : ''); ?>>Bedroom</option>
                    <option value="Dining" <?php echo e(request('category') === 'Dining' ? 'selected' : ''); ?>>Dining</option>
                    <option value="Pool" <?php echo e(request('category') === 'Pool' ? 'selected' : ''); ?>>Pool</option>
                </select>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="low_stock" value="1" <?php echo e(request('low_stock') ? 'checked' : ''); ?>

                        onchange="this.form.submit()" class="rounded border-gray-300 dark:border-white/10">
                    <span class="text-sm text-gray-700 dark:text-slate-300">Show Low Stock Only</span>
                </label>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Item</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Category</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Available</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                In Use</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Soiled</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo e($item->item_name); ?></p>
                                        <p class="text-xs text-gray-600 dark:text-slate-400"><?php echo e($item->item_code); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400"><?php echo e($item->category); ?>

                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($item->available_quantity); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">
                                    <?php echo e($item->in_use_quantity); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">
                                    <?php echo e($item->soiled_quantity); ?></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($item->stock_status === 'out_of_stock'
                                            ? 'bg-red-100 text-red-700'
                                            : ($item->stock_status === 'low_stock'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-green-100 text-green-700')); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $item->stock_status))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openMovementModal(<?php echo e($item->id); ?>, '<?php echo e($item->item_name); ?>')"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Record
                                        Movement</button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-slate-400">No linen
                                    items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div id="modal-movement" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl max-w-lg w-full p-6">
            <form action="<?php echo e(route('hotel.housekeeping.linen.movement')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record Linen Movement</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Item *</label>
                        <select name="linen_inventory_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>"><?php echo e($item->item_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Movement Type
                            *</label>
                        <select name="movement_type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="transfer">Transfer to Room</option>
                            <option value="laundry_out">Send to Laundry</option>
                            <option value="laundry_in">Receive from Laundry</option>
                            <option value="damage">Mark as Damaged</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Quantity
                            *</label>
                        <input type="number" name="quantity" min="1" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Reason</label>
                        <textarea name="reason" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeMovementModal()"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Record</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openMovementModal(itemId = null, itemName = '') {
                document.getElementById('modal-movement').classList.remove('hidden');
                if (itemId) {
                    document.querySelector('select[name="linen_inventory_id"]').value = itemId;
                }
            }

            function closeMovementModal() {
                document.getElementById('modal-movement').classList.add('hidden');
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\housekeeping\linen\index.blade.php ENDPATH**/ ?>