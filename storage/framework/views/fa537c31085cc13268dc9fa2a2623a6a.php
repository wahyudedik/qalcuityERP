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
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
            <?php echo e(__('Revenue Postings')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
                <form method="GET" action="<?php echo e(route('hotel.night-audit.revenue-postings')); ?>"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Date From</label>
                        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Date To</label>
                        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Revenue
                            Type</label>
                        <select name="revenue_type"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">All Types</option>
                            <?php $__currentLoopData = $revenueTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"
                                    <?php echo e(request('revenue_type') == $type ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $type))); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending
                            </option>
                            <option value="posted" <?php echo e(request('status') == 'posted' ? 'selected' : ''); ?>>Posted</option>
                            <option value="voided" <?php echo e(request('status') == 'voided' ? 'selected' : ''); ?>>Voided</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-slate-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Reference</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Description</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Amount</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Auto</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-white/10">
                            <?php $__empty_1 = true; $__currentLoopData = $postings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $posting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo e($posting->posting_reference); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($posting->posting_date->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $posting->revenue_type))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($posting->description); ?>

                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp <?php echo e(number_format($posting->total_amount, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo e($posting->status === 'posted'
                                            ? 'bg-green-100 text-green-800'
                                            : ($posting->status === 'voided'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800')); ?>">
                                            <?php echo e(ucfirst($posting->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($posting->auto_generated ? '✓ Yes' : 'No'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if($posting->canBeVoided()): ?>
                                            <button
                                                onclick="openVoidModal(<?php echo e($posting->id); ?>, '<?php echo e($posting->posting_reference); ?>')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                Void
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                        No revenue postings found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="p-6 border-t border-gray-200 dark:border-white/10">
                    <?php echo e($postings->links()); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div id="modal-void" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Void Revenue Posting</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1" id="void-posting-ref"></p>
            </div>

            <form id="void-form" method="POST">
                <?php echo csrf_field(); ?>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Reason for
                            Voiding</label>
                        <textarea name="reason" rows="3" required
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Enter reason for voiding this posting..."></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" onclick="closeVoidModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-md">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                        Void Posting
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openVoidModal(postingId, reference) {
            document.getElementById('modal-void').classList.remove('hidden');
            document.getElementById('void-posting-ref').textContent = 'Posting: ' + reference;
            document.getElementById('void-form').action = `/hotel/night-audit/revenue-postings/${postingId}/void`;
        }

        function closeVoidModal() {
            document.getElementById('modal-void').classList.add('hidden');
        }
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\night-audit\revenue-postings.blade.php ENDPATH**/ ?>