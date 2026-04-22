

<?php $__env->startSection('title', 'Customer Segmentation - RFM Analysis'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">← Back to
                Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Customer Segmentation & RFM Analysis</h1>
            <p class="mt-1 text-sm text-gray-600">Segmentasi customer berdasarkan Recency, Frequency, Monetary</p>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form method="GET" class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Analysis Period:</label>
                <select name="days" onchange="this.form.submit()"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="90" <?php echo e($daysBack == 90 ? 'selected' : ''); ?>>Last 90 Days</option>
                    <option value="180" <?php echo e($daysBack == 180 ? 'selected' : ''); ?>>Last 6 Months</option>
                    <option value="365" <?php echo e($daysBack == 365 ? 'selected' : ''); ?>>Last 12 Months</option>
                </select>
            </form>
        </div>

        <!-- Segment Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <?php $__currentLoopData = $rfmData['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $segment => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase"><?php echo e($segment); ?></div>
                    <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($stats['count']); ?></div>
                    <div class="text-xs text-gray-500"><?php echo e($stats['percentage']); ?>% of customers</div>
                    <div class="mt-1 text-sm font-semibold text-green-600">Rp
                        <?php echo e(number_format($stats['total_revenue'], 0, ',', '.')); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Customers Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Customer Details (<?php echo e(count($rfmData['segments'])); ?>

                    customers)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recency (days)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monetary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">R/F/M Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Segment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $rfmData['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($customer['customer_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($customer['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($customer['recency_days']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($customer['frequency']); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($customer['monetary'], 0, ',', '.')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e($customer['rfm_score']); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $badgeColor = match ($customer['segment']) {
                                            'Champions' => 'bg-green-100 text-green-800',
                                            'Loyal Customers' => 'bg-blue-100 text-blue-800',
                                            'At Risk' => 'bg-red-100 text-red-800',
                                            'Lost' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                    ?>
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($badgeColor); ?>">
                                        <?php echo e($customer['segment']); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No customer data
                                    available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\customer-segmentation.blade.php ENDPATH**/ ?>