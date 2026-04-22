
<?php $__env->startSection('title', 'Cashflow Forecast'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Cashflow Forecasting</h1>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Cash Flow Projection</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = $cashflowData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="flex justify-between items-center p-3 <?php echo e($item['type'] === 'forecast' ? 'bg-blue-50' : 'bg-gray-50'); ?> rounded">
                            <div>
                                <div class="text-sm font-medium"><?php echo e($item['label']); ?></div>
                                <div class="text-xs text-gray-500 capitalize"><?php echo e($item['type']); ?></div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-sm font-semibold <?php echo e($item['net'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                    Net: Rp <?php echo e(number_format($item['net'], 0, ',', '.')); ?></div>
                                <div class="text-xs text-gray-500">In: <?php echo e(number_format($item['inflow'], 0, ',', '.')); ?> |
                                    Out: <?php echo e(number_format($item['outflow'], 0, ',', '.')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Revenue Forecast</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = array_merge($revenueData['historical'] ?? [], $revenueData['projected'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="flex justify-between items-center p-3 <?php echo e($item['type'] === 'forecast' ? 'bg-yellow-50' : 'bg-gray-50'); ?> rounded">
                            <div>
                                <div class="text-sm font-medium"><?php echo e($item['label']); ?></div>
                                <div class="text-xs text-gray-500 capitalize"><?php echo e($item['type']); ?></div>
                            </div>
                            <div class="text-sm font-semibold">Rp <?php echo e(number_format($item['amount'], 0, ',', '.')); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\cashflow-forecast.blade.php ENDPATH**/ ?>