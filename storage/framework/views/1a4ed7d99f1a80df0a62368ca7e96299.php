

<?php $__env->startSection('title', 'Quality Checks List'); ?>

<?php $__env->startSection('content'); ?>
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Quality Checks</h1>
                <p class="text-gray-600 mt-1">Manage and track quality inspections</p>
            </div>
            <a href="<?php echo e(route('manufacturing.quality.checks.create')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Quality Check
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border-gray-300">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="in_progress" <?php echo e(request('status') == 'in_progress' ? 'selected' : ''); ?>>In Progress
                        </option>
                        <option value="passed" <?php echo e(request('status') == 'passed' ? 'selected' : ''); ?>>Passed</option>
                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Failed</option>
                        <option value="conditional_pass" <?php echo e(request('status') == 'conditional_pass' ? 'selected' : ''); ?>>
                            Conditional Pass</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                    <select name="stage" class="w-full rounded-md border-gray-300">
                        <option value="">All Stages</option>
                        <option value="incoming" <?php echo e(request('stage') == 'incoming' ? 'selected' : ''); ?>>Incoming</option>
                        <option value="in_process" <?php echo e(request('stage') == 'in_process' ? 'selected' : ''); ?>>In Process
                        </option>
                        <option value="final" <?php echo e(request('stage') == 'final' ? 'selected' : ''); ?>>Final</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                        class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                        class="w-full rounded-md border-gray-300">
                </div>
                <div class="md:col-span-4 flex justify-end gap-2">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Filter</button>
                    <a href="<?php echo e(route('manufacturing.quality.checks')); ?>"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Clear</a>
                </div>
            </form>
        </div>

        <!-- Quality Checks Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sample</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pass Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inspected</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $qualityChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($check->check_number); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($check->workOrder?->number ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($check->product?->name ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $check->stage))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($check->sample_passed); ?>/<?php echo e($check->sample_size); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="text-sm font-semibold <?php echo e($check->pass_rate >= 95 ? 'text-green-600' : ($check->pass_rate >= 80 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                    <?php echo e(number_format($check->pass_rate, 1)); ?>%
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($check->status === 'passed'): ?>
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">PASSED</span>
                                <?php elseif($check->status === 'failed'): ?>
                                    <span
                                        class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">FAILED</span>
                                <?php elseif($check->status === 'conditional_pass'): ?>
                                    <span
                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">CONDITIONAL</span>
                                <?php elseif($check->status === 'in_progress'): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">IN
                                        PROGRESS</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($check->inspected_at?->format('d/m/Y H:i') ?? '-'); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if($check->status === 'pending' || $check->status === 'in_progress'): ?>
                                    <a href="<?php echo e(route('manufacturing.quality.checks.edit', $check)); ?>"
                                        class="text-blue-600 hover:text-blue-900">Inspect</a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('manufacturing.quality.checks.edit', $check)); ?>"
                                        class="text-gray-600 hover:text-gray-900">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                No quality checks found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($qualityChecks->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\quality\checks.blade.php ENDPATH**/ ?>