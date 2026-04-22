

<?php $__env->startSection('title', 'Product Profitability Matrix'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">← Back to
                Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Product Profitability Matrix</h1>
        </div>

        <!-- Quadrant Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <?php $__currentLoopData = ['Stars' => 'bg-green', 'Cash Cows' => 'bg-blue', 'Question Marks' => 'bg-yellow', 'Dogs' => 'bg-red']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quadrant => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $data = $profitabilityData['quadrants'][$quadrant] ?? null; ?>
                <div
                    class="bg-white rounded-lg shadow p-4 border-l-4 <?php echo e(str_replace('bg-', "border-{$color}-500 ", $color)); ?>">
                    <div class="text-xs font-medium text-gray-500 uppercase"><?php echo e($quadrant); ?></div>
                    <div class="mt-2 text-xl font-bold text-gray-900"><?php echo e($data['count'] ?? 0); ?> products</div>
                    <div class="text-xs text-gray-500">Avg Margin: <?php echo e($data['avg_margin'] ?? 0); ?>%</div>
                    <div class="text-sm font-semibold text-green-600">Rp
                        <?php echo e(number_format($data['total_profit'] ?? 0, 0, ',', '.')); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quadrant</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $profitabilityData['matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($product['product_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                <?php echo e(number_format($product['total_revenue'], 0, ',', '.')); ?></td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-semibold <?php echo e($product['total_profit'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                Rp <?php echo e(number_format($product['total_profit'], 0, ',', '.')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($product['profit_margin']); ?>%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e(number_format($product['total_qty_sold'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $colors = [
                                        'Stars' => 'bg-green-100 text-green-800',
                                        'Cash Cows' => 'bg-blue-100 text-blue-800',
                                        'Question Marks' => 'bg-yellow-100 text-yellow-800',
                                        'Dogs' => 'bg-red-100 text-red-800',
                                    ];
                                ?>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($colors[$product['quadrant']] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($product['quadrant']); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No product data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\product-profitability.blade.php ENDPATH**/ ?>