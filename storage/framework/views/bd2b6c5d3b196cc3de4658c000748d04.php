

<?php $__env->startSection('title', 'Batch Recalls'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('cosmetic.expiry.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Dashboard
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Batch Recalls</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage product recall procedures</p>
                </div>
                <button onclick="document.getElementById('add-recall-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Initiate Recall
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Total Recalls</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_recalls']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold text-orange-600"><?php echo e($stats['active_recalls']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-green-600"><?php echo e($stats['completed_recalls']); ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Critical</p>
                <p class="text-2xl font-bold text-red-800"><?php echo e($stats['critical_recalls']); ?></p>
            </div>
        </div>

        <!-- Recalls Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recall #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $recalls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recall): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono font-medium text-gray-900"><?php echo e($recall->recall_number); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($recall->batch->batch_number ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo e(Str::limit($recall->recall_reason, 30)); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full 
                            <?php if($recall->severity == 'critical'): ?> bg-red-100 text-red-800
                            <?php elseif($recall->severity == 'major'): ?> bg-orange-100 text-orange-800
                            <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                    <?php echo e($recall->severity_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($recall->return_percentage); ?>%</div>
                                <div class="text-xs text-gray-500">
                                    <?php echo e($recall->units_returned); ?>/<?php echo e($recall->total_units); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full 
                            <?php if($recall->status == 'completed'): ?> bg-green-100 text-green-800
                            <?php elseif($recall->status == 'in_progress'): ?> bg-blue-100 text-blue-800
                            <?php elseif($recall->status == 'initiated'): ?> bg-orange-100 text-orange-800
                            <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                    <?php echo e($recall->status_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($recall->status != 'completed' && $recall->status != 'cancelled'): ?>
                                    <button
                                        onclick="document.getElementById('complete-recall-<?php echo e($recall->id); ?>').classList.remove('hidden')"
                                        class="text-green-600 hover:text-green-900 mr-2">Complete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No recalls found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if($recalls->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200"><?php echo e($recalls->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Recall Modal -->
    <div id="add-recall-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Initiate Batch Recall</h3>
                <button onclick="document.getElementById('add-recall-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="<?php echo e(route('cosmetic.expiry.recalls.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batch *</label>
                        <select name="batch_id" required class="w-full rounded-lg border-gray-300">
                            <option value="">Select Batch</option>
                            <!-- Add batches from controller -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recall Date *</label>
                        <input type="date" name="recall_date" required value="<?php echo e(now()->format('Y-m-d')); ?>"
                            class="w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recall Reason *</label>
                    <input type="text" name="recall_reason" required placeholder="contamination, labeling_error, etc."
                        class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" required rows="3" class="w-full rounded-lg border-gray-300"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Severity *</label>
                        <select name="severity" required class="w-full rounded-lg border-gray-300">
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Units *</label>
                        <input type="number" name="total_units" required min="0"
                            class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Affected Regions</label>
                        <input type="text" name="affected_regions" placeholder="Jakarta, Surabaya"
                            class="w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('add-recall-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">Initiate
                        Recall</button>
                </div>
            </form>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\expiry\recalls.blade.php ENDPATH**/ ?>