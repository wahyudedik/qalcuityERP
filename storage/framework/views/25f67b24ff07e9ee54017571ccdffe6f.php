

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Flock Performance</h1>
            <p class="mt-2 text-gray-600">Monitor flock health and performance metrics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Flocks</div>
                <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total_flocks']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Avg Mortality Rate</div>
                <div class="mt-2 text-3xl font-bold text-red-600"><?php echo e(number_format($stats['avg_mortality_rate'], 2)); ?>%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Avg FCR</div>
                <div class="mt-2 text-3xl font-bold text-blue-600"><?php echo e(number_format($stats['avg_fcr'], 2)); ?></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Performance Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Birds Alive</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mortality %</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">FCR</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Health</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $performances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($perf->record_date->format('d M Y')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($perf->herd?->name ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e(number_format($perf->birds_alive)); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    <?php echo e(number_format($perf->mortality_rate_percentage, 2)); ?>%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e(number_format($perf->feed_conversion_ratio, 2)); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php if($perf->health_status === 'healthy'): ?> bg-green-100 text-green-800
                                    <?php elseif($perf->health_status === 'sick'): ?> bg-red-100 text-red-800
                                    <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                        <?php echo e(ucfirst($perf->health_status)); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($performances->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\livestock\poultry\flock-performance.blade.php ENDPATH**/ ?>