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
            <?php echo e(__('Night Audit')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            <?php if($todayStats): ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Occupancy Rate</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            <?php echo e(number_format($todayStats->occupancy_percentage, 1)); ?>%</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1"><?php echo e($todayStats->occupied_rooms); ?> /
                            <?php echo e($todayStats->total_rooms); ?> rooms</p>
                    </div>

                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Monthly ADR</h3>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">Rp
                            <?php echo e(number_format($monthlyADR, 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Average Daily Rate</p>
                    </div>

                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Check-ins Today</h3>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            <?php echo e($todayStats->check_ins ?? 0); ?></p>
                    </div>

                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Check-outs Today</h3>
                        <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                            <?php echo e($todayStats->check_outs ?? 0); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="mb-6">
                <button onclick="openStartAuditModal()"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                    Start Night Audit
                </button>

                <a href="<?php echo e(route('hotel.night-audit.statistics')); ?>"
                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 ml-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    View Statistics
                </a>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Audit Batches</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-slate-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Batch #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Rooms</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Revenue</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    ADR</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Completed</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-white/10">
                            <?php $__empty_1 = true; $__currentLoopData = $recentBatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo e($batch->batch_number); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($batch->audit_date->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo e($batch->status === 'completed'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : ($batch->status === 'in_progress'
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200')); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $batch->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($batch->occupied_rooms); ?> / <?php echo e($batch->total_rooms); ?>

                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp <?php echo e(number_format($batch->total_revenue, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        Rp <?php echo e(number_format($batch->adr, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">
                                        <?php echo e($batch->completed_at ? $batch->completed_at->format('H:i') : '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?php echo e(route('hotel.night-audit.batch', $batch->id)); ?>"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                        No audit batches yet. Start your first night audit!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div id="modal-start-audit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Start Night Audit</h3>
            </div>

            <form action="<?php echo e(route('hotel.night-audit.start')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Audit
                            Date</label>
                        <input type="date" name="audit_date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                            Select the business date you want to audit (usually yesterday)
                        </p>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            <strong>Note:</strong> Night audit will process all room charges, F&B revenue, and calculate
                            statistics for the selected date.
                        </p>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" onclick="closeStartAuditModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-md">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                        Start Audit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStartAuditModal() {
            document.getElementById('modal-start-audit').classList.remove('hidden');
        }

        function closeStartAuditModal() {
            document.getElementById('modal-start-audit').classList.add('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\night-audit\index.blade.php ENDPATH**/ ?>