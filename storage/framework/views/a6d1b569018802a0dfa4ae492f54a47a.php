<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Housekeeping Supplies']); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Supplies Inventory</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">Track housekeeping supplies and amenities</p>
            </div>
            <button onclick="openUsageModal()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Record Usage
            </button>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="category" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">All Categories</option>
                    <option value="Amenities" <?php echo e(request('category') === 'Amenities' ? 'selected' : ''); ?>>Amenities
                    </option>
                    <option value="Cleaning Supplies"
                        <?php echo e(request('category') === 'Cleaning Supplies' ? 'selected' : ''); ?>>Cleaning Supplies</option>
                    <option value="Minibar" <?php echo e(request('category') === 'Minibar' ? 'selected' : ''); ?>>Minibar</option>
                    <option value="Office" <?php echo e(request('category') === 'Office' ? 'selected' : ''); ?>>Office</option>
                </select>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="needs_reorder" value="1"
                        <?php echo e(request('needs_reorder') ? 'checked' : ''); ?> onchange="this.form.submit()"
                        class="rounded border-gray-300 dark:border-white/10">
                    <span class="text-sm text-gray-700 dark:text-slate-300">Needs Reorder</span>
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
                                On Hand</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Reorder Point</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Unit Cost</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        <?php $__empty_1 = true; $__currentLoopData = $supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo e($supply->item_name); ?></p>
                                        <p class="text-xs text-gray-600 dark:text-slate-400">
                                            <?php echo e($supply->brand ?? 'N/A'); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400"><?php echo e($supply->category); ?>

                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($supply->quantity_on_hand); ?> <?php echo e($supply->unit_of_measure); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">
                                    <?php echo e($supply->reorder_point); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">Rp
                                    <?php echo e(number_format($supply->unit_cost, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($supply->stock_status === 'out_of_stock'
                                            ? 'bg-red-100 text-red-700'
                                            : ($supply->stock_status === 'low_stock'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-green-100 text-green-700')); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $supply->stock_status))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openUsageModal(<?php echo e($supply->id); ?>, '<?php echo e($supply->item_name); ?>')"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Record
                                        Usage</button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-slate-400">No supplies
                                    found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div id="modal-usage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl max-w-lg w-full p-6">
            <form action="<?php echo e(route('hotel.housekeeping.supplies.usage')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record Supply Usage</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supply Item
                            *</label>
                        <select name="housekeeping_supply_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <?php $__currentLoopData = $supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supply->id); ?>"><?php echo e($supply->item_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Quantity Used
                            *</label>
                        <input type="number" name="quantity_used" min="1" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                            placeholder="Optional notes"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeUsageModal()"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Record
                        Usage</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openUsageModal(supplyId = null, itemName = '') {
                document.getElementById('modal-usage').classList.remove('hidden');
                if (supplyId) {
                    document.querySelector('select[name="housekeeping_supply_id"]').value = supplyId;
                }
            }

            function closeUsageModal() {
                document.getElementById('modal-usage').classList.add('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\housekeeping\supplies\index.blade.php ENDPATH**/ ?>