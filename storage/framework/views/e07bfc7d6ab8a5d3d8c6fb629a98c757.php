
<?php $__env->startSection('title', 'Employee Performance'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Employee Performance Metrics</h1>
        </div>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $performanceData['employees']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($emp['rank'] == 1): ?>
                                    🥇
                                <?php elseif($emp['rank'] == 2): ?>
                                    🥈
                                <?php elseif($emp['rank'] == 3): ?>
                                    🥉
                                <?php else: ?>
                                    #<?php echo e($emp['rank']); ?>

                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($emp['employee_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e(number_format($emp['total_orders'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                <?php echo e(number_format($emp['total_revenue'], 0, ',', '.')); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp
                                <?php echo e(number_format($emp['avg_order_value'], 0, ',', '.')); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($emp['performance_score'] >= 80 ? 'bg-green-100 text-green-800' : ($emp['performance_score'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')); ?>">
                                    <?php echo e($emp['performance_score']); ?>/100
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($emp['trend'] === 'up'): ?>
                                    <span class="text-green-600">↑ Up</span>
                                <?php elseif($emp['trend'] === 'down'): ?>
                                    <span class="text-red-600">↓ Down</span>
                                <?php else: ?>
                                    <span class="text-gray-500">→ Stable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No employee data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\employee-performance.blade.php ENDPATH**/ ?>