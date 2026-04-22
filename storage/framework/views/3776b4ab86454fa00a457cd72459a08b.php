<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Trend Analysis')); ?></h2>
            <form method="GET" class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Period:</label>
                <select name="period" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm">
                    <option value="6" <?php echo e($period == '6' ? 'selected' : ''); ?>>Last 6 Months</option>
                    <option value="12" <?php echo e($period == '12' ? 'selected' : ''); ?>>Last 12 Months</option>
                    <option value="24" <?php echo e($period == '24' ? 'selected' : ''); ?>>Last 24 Months</option>
                </select>
            </form>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-chart-line mr-2 text-blue-600"></i>Visit Trends (Last <?php echo e($period); ?> Months)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Visits
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $visitTrends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e(\Carbon\Carbon::parse($trend['period'])->format('F Y')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <?php echo e(number_format($trend['count'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($index > 0): ?>
                                            <?php
                                                $change =
                                                    $visitTrends[$index - 1]['count'] > 0
                                                        ? (($trend['count'] - $visitTrends[$index - 1]['count']) /
                                                                $visitTrends[$index - 1]['count']) *
                                                            100
                                                        : 0;
                                            ?>
                                            <span
                                                class="text-sm font-semibold <?php echo e($change >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                                <i class="fas <?php echo e($change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'); ?>"></i>
                                                <?php echo e(number_format(abs($change), 1)); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-money-bill-wave mr-2 text-green-600"></i>Revenue Trends (Last <?php echo e($period); ?>

                    Months)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                    Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $revenueTrends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e(\Carbon\Carbon::parse($trend['period'])->format('F Y')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                        <?php echo e(number_format($trend['revenue'], 0, ',', '.')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($index > 0): ?>
                                            <?php
                                                $change =
                                                    $revenueTrends[$index - 1]['revenue'] > 0
                                                        ? (($trend['revenue'] - $revenueTrends[$index - 1]['revenue']) /
                                                                $revenueTrends[$index - 1]['revenue']) *
                                                            100
                                                        : 0;
                                            ?>
                                            <span
                                                class="text-sm font-semibold <?php echo e($change >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                                <i
                                                    class="fas <?php echo e($change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'); ?>"></i>
                                                <?php echo e(number_format(abs($change), 1)); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-stethoscope mr-2 text-purple-600"></i>Top 10 Diagnoses</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diagnosis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $totalDiagnoses = $diagnosisTrends->sum('count'); ?>
                        <?php $__empty_1 = true; $__currentLoopData = $diagnosisTrends; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $diagnosis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($index + 1); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e($diagnosis->diagnosis); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo e(number_format($diagnosis->count)); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                            <div class="bg-purple-600 h-2.5 rounded-full"
                                                style="width: <?php echo e($totalDiagnoses > 0 ? ($diagnosis->count / $totalDiagnoses) * 100 : 0); ?>%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm text-gray-700"><?php echo e($totalDiagnoses > 0 ? number_format(($diagnosis->count / $totalDiagnoses) * 100, 1) : 0); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No diagnosis data
                                    available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\trend-analysis\index.blade.php ENDPATH**/ ?>