

<?php $__env->startSection('title', 'COA Certificates'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <a href="<?php echo e(route('cosmetic.qc.tests')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                        ← Back to QC Tests
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">COA Certificates</h1>
                    <p class="mt-1 text-sm text-gray-500">Certificate of Analysis management</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total COAs</div>
                <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($stats['total_coas']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Approved</div>
                <div class="mt-2 text-2xl font-bold text-green-600"><?php echo e($stats['approved_coas']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Valid</div>
                <div class="mt-2 text-2xl font-bold text-blue-600"><?php echo e($stats['valid_coas']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expired</div>
                <div class="mt-2 text-2xl font-bold text-red-600"><?php echo e($stats['expired_coas']); ?></div>
            </div>
        </div>

        <!-- COA Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('cosmetic.qc.coa')); ?>" class="flex gap-4">
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="issued" <?php echo e(request('status') == 'issued' ? 'selected' : ''); ?>>Issued</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="revoked" <?php echo e(request('status') == 'revoked' ? 'selected' : ''); ?>>Revoked</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                        Filter
                    </button>
                </form>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">COA Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($coa->coa_number); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo e($coa->batch->batch_number); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($coa->batch->formula->formula_name); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo e($coa->issue_date->format('d M Y')); ?>

                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if($coa->expiry_date): ?>
                                    <span class="<?php echo e($coa->isExpired() ? 'text-red-600 font-medium' : 'text-gray-500'); ?>">
                                        <?php echo e($coa->expiry_date->format('d M Y')); ?>

                                        <?php if($coa->isExpired()): ?>
                                            ⚠️ Expired
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">No expiry</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($coa->status == 'approved'): ?> bg-green-100 text-green-800
                            <?php elseif($coa->status == 'issued'): ?> bg-blue-100 text-blue-800
                            <?php elseif($coa->status == 'revoked'): ?> bg-red-100 text-red-800
                            <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                    <?php echo e($coa->status_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <?php if($coa->status == 'issued'): ?>
                                        <form method="POST" action="<?php echo e(route('cosmetic.qc.coa.approve', $coa)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No COA certificates found. Generate from batch with approved tests.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($coas->hasPages()): ?>
            <div class="mt-4"><?php echo e($coas->links()); ?></div>
        <?php endif; ?>

        <!-- Generate COA Modal -->
        <div id="generate-coa-modal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-semibold mb-4">Generate COA Certificate</h3>
                <form method="POST" action="" id="coa-form">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Batch *</label>
                        <select name="batch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select Batch</option>
                            <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($batch->id); ?>"><?php echo e($batch->batch_number); ?> -
                                    <?php echo e($batch->formula->formula_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Batch must have approved QC tests</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Generate COA
                        </button>
                        <button type="button"
                            onclick="document.getElementById('generate-coa-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\qc\coa.blade.php ENDPATH**/ ?>