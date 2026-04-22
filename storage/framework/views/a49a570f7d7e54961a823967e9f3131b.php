

<?php $__env->startSection('title', 'Pricing Recommendations'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pricing Recommendations</h1>
                <p class="text-gray-600">AI-generated pricing suggestions based on market analysis</p>
            </div>
            <a href="<?php echo e(route('revenue.yield-optimization')); ?>"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Yield Optimization
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-600"><?php echo e($stats['pending']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Applied</div>
                <div class="text-2xl font-bold text-green-600"><?php echo e($stats['applied']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Rejected</div>
                <div class="text-2xl font-bold text-red-600"><?php echo e($stats['rejected']); ?></div>
            </div>
        </div>

        <!-- Recommendations Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold text-gray-800">All Recommendations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Room Type</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Current Rate</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Recommended</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Change</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Reasoning</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><?php echo e($rec->recommendation_date->format('M d, Y')); ?></td>
                                <td class="px-4 py-3 font-medium"><?php echo e($rec->roomType?->name ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-center">$<?php echo e(number_format($rec->current_rate, 2)); ?></td>
                                <td class="px-4 py-3 text-center font-medium">
                                    $<?php echo e(number_format($rec->recommended_rate, 2)); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-medium
                                <?php echo e($rec->suggested_change_percentage > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                        <?php echo e($rec->suggested_change_percentage > 0 ? '+' : ''); ?><?php echo e(number_format($rec->suggested_change_percentage, 1)); ?>%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm max-w-xs truncate" title="<?php echo e($rec->reasoning); ?>">
                                    <?php echo e($rec->reasoning); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-xs
                                <?php echo e($rec->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : ''); ?>

                                <?php echo e($rec->status === 'applied' ? 'bg-green-100 text-green-700' : ''); ?>

                                <?php echo e($rec->status === 'rejected' ? 'bg-red-100 text-red-700' : ''); ?>">
                                        <?php echo e(ucfirst($rec->status)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($rec->status === 'pending'): ?>
                                        <div class="flex justify-center space-x-2">
                                            <form action="<?php echo e(route('revenue.recommendations.apply', $rec)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Apply</button>
                                            </form>
                                            <form action="<?php echo e(route('revenue.recommendations.reject', $rec)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-xs bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600">Reject</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">
                                            <?php if($rec->reviewedBy): ?>
                                                by <?php echo e($rec->reviewedBy->name); ?>

                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No pricing recommendations available.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">
                <?php echo e($recommendations->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\revenue\recommendations.blade.php ENDPATH**/ ?>