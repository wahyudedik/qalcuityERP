

<?php $__env->startSection('title', 'Expiry Reports'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('cosmetic.expiry.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Dashboard
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Expiry Reports</h1>
                    <p class="mt-1 text-sm text-gray-500">Compliance reporting & analytics</p>
                </div>
                <button onclick="document.getElementById('generate-report-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Generate Report
                </button>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batches</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expired</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recalled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loss Value</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono font-medium text-gray-900"><?php echo e($report->report_number); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e($report->type_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($report->start_date->format('d M Y')); ?></div>
                                <div class="text-xs text-gray-500">to <?php echo e($report->end_date->format('d M Y')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($report->total_batches_monitored); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-red-600 font-medium"><?php echo e($report->batches_expired); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($report->expiry_rate); ?>%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-orange-600 font-medium"><?php echo e($report->batches_recalled); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($report->recall_rate); ?>%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">Rp
                                    <?php echo e(number_format($report->total_loss_value, 0, ',', '.')); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2">No reports generated yet</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if($reports->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200"><?php echo e($reports->links()); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div id="generate-report-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Generate Expiry Report</h3>
                <button onclick="document.getElementById('generate-report-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="<?php echo e(route('cosmetic.expiry.reports.generate')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Type *</label>
                    <select name="report_type" required class="w-full rounded-lg border-gray-300">
                        <option value="monthly">Monthly Report</option>
                        <option value="quarterly">Quarterly Report</option>
                        <option value="annual">Annual Report</option>
                        <option value="ad_hoc">Ad-Hoc Report</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                        <input type="date" name="end_date" required class="w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button"
                        onclick="document.getElementById('generate-report-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Generate
                        Report</button>
                </div>
            </form>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\expiry\reports.blade.php ENDPATH**/ ?>