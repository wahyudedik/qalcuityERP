
<?php $__env->startSection('title', 'Seasonal Trend Analysis'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Seasonal Trend Analysis</h1>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Monthly Trends</h3>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <?php $__currentLoopData = $seasonalData['monthly_trends']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="text-sm"><?php echo e($trend->month_name); ?> <?php echo e($trend->year); ?></div>
                            <div class="text-sm font-semibold">Rp <?php echo e(number_format($trend->total_revenue, 0, ',', '.')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Seasonal Index</h3>
                <div class="space-y-2">
                    <?php $__currentLoopData = $seasonalData['seasonal_index']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between items-center">
                            <div class="text-sm"><?php echo e($index['month_name']); ?></div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: <?php echo e(min(100, $index['seasonal_index'] * 50)); ?>%"></div>
                                </div>
                                <span
                                    class="text-xs font-medium <?php echo e($index['seasonal_index'] > 1 ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($index['seasonal_index']); ?>x</span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <?php if(!empty($seasonalData['peak_seasons'])): ?>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Peak Seasons Identified</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php $__currentLoopData = $seasonalData['peak_seasons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $peak): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-800"><?php echo e($peak['month_name']); ?> <?php echo e($peak['year']); ?>

                            </div>
                            <div class="text-xs text-green-600 mt-1">Revenue: Rp
                                <?php echo e(number_format($peak['revenue'], 0, ',', '.')); ?></div>
                            <div class="text-xs text-green-600">Orders: <?php echo e(number_format($peak['orders'])); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if(!empty($seasonalData['insights'])): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mt-6">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Key Insights:</h4>
                <ul class="space-y-1">
                    <?php $__currentLoopData = $seasonalData['insights']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-sm text-blue-700 flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <?php echo e($insight); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\seasonal-trends.blade.php ENDPATH**/ ?>