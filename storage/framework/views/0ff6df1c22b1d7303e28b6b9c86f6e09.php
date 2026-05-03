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
     <?php $__env->slot('header', null, []); ?> <i class="fas fa-chart-line mr-2 text-green-600"></i>Yield Analysis <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('cosmetic.batches.show', $batch)); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Batch
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Current Yield Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-percentage mr-2 text-blue-600"></i>Current Batch Yield
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-blue-600">Current Yield</div>
                        <div class="mt-2 text-3xl font-bold text-blue-900">
                            <?php echo e(number_format($yieldAnalysis['current_yield'], 1)); ?>%
                        </div>
                        <div class="text-xs text-blue-600 mt-1">
                            Status: <?php echo e(ucfirst(str_replace('_', ' ', $yieldAnalysis['yield_status']))); ?>

                        </div>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg">
                        <div class="text-sm text-green-600">Planned vs Actual</div>
                        <div class="mt-2 text-lg font-bold text-green-900">
                            <?php echo e(number_format($yieldAnalysis['planned_quantity'], 2)); ?> →
                            <?php echo e(number_format($yieldAnalysis['actual_quantity'], 2)); ?>

                        </div>
                    </div>

                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <div class="text-sm text-yellow-600">Loss</div>
                        <div class="mt-2 text-lg font-bold text-yellow-900">
                            <?php echo e(number_format($yieldAnalysis['loss_quantity'], 2)); ?>

                            (<?php echo e($yieldAnalysis['loss_percentage']); ?>%)
                        </div>
                    </div>

                    <div class="p-4 bg-purple-50 rounded-lg">
                        <div class="text-sm text-purple-600">Rework Losses</div>
                        <div class="mt-2 text-lg font-bold text-purple-900">
                            <?php echo e(number_format($yieldAnalysis['rework_losses'], 2)); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Comparison -->
            <?php if($yieldAnalysis['historical_average']): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history mr-2 text-purple-600"></i>Historical Comparison
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg text-center">
                            <div class="text-sm text-gray-600">Average Yield</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">
                                <?php echo e($yieldAnalysis['historical_average']); ?>%
                            </div>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg text-center">
                            <div class="text-sm text-green-600">Best Yield</div>
                            <div class="mt-2 text-2xl font-bold text-green-900">
                                <?php echo e($yieldAnalysis['historical_best']); ?>%
                            </div>
                        </div>

                        <div class="p-4 bg-red-50 rounded-lg text-center">
                            <div class="text-sm text-red-600">Worst Yield</div>
                            <div class="mt-2 text-2xl font-bold text-red-900">
                                <?php echo e($yieldAnalysis['historical_worst']); ?>%
                            </div>
                        </div>

                        <div
                            class="p-4 rounded-lg text-center
                        <?php if($yieldAnalysis['vs_average'] >= 0): ?> bg-green-50
                        <?php else: ?> bg-red-50 <?php endif; ?>">
                            <div class="text-sm">Vs Average</div>
                            <div
                                class="mt-2 text-2xl font-bold
                            <?php if($yieldAnalysis['vs_average'] >= 0): ?> text-green-900
                            <?php else: ?> text-red-900 <?php endif; ?>">
                                <?php echo e($yieldAnalysis['vs_average'] >= 0 ? '+' : ''); ?><?php echo e($yieldAnalysis['vs_average']); ?>%
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-600">
                        Based on <?php echo e($yieldAnalysis['total_batches_analyzed']); ?> released batches
                    </div>
                </div>
            <?php endif; ?>

            <!-- Yield Trends Chart -->
            <?php if(count($yieldTrends['trends']) > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-chart-area mr-2 text-green-600"></i>Yield Trends (Last 6 Months)
                        </h3>
                        <a href="<?php echo e(route('cosmetic.batches.yield-report', ['formulaId' => $batch->formula_id, 'months' => 6])); ?>"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-file-pdf mr-2"></i>Export Report
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Date</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Batch</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Yield</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actual</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Planned</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $yieldTrends['trends']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <?php echo e($trend['date']); ?></td>
                                        <td class="px-4 py-3 text-sm text-blue-600">
                                            <?php echo e($trend['batch_number']); ?></td>
                                        <td
                                            class="px-4 py-3 text-sm font-bold
                                    <?php if($trend['yield'] >= 95): ?> text-green-600
                                    <?php elseif($trend['yield'] >= 90): ?> text-yellow-600
                                    <?php else: ?> text-red-600 <?php endif; ?>">
                                            <?php echo e($trend['yield']); ?>%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <?php echo e(number_format($trend['actual'], 2)); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?php echo e(number_format($trend['planned'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-3 bg-gray-50 rounded text-center">
                            <div class="text-xs text-gray-600">Average</div>
                            <div class="text-lg font-bold text-gray-900"><?php echo e($yieldTrends['average']); ?>%
                            </div>
                        </div>
                        <div class="p-3 bg-green-50 rounded text-center">
                            <div class="text-xs text-green-600">Maximum</div>
                            <div class="text-lg font-bold text-green-900">
                                <?php echo e($yieldTrends['max']); ?>%</div>
                        </div>
                        <div class="p-3 bg-red-50 rounded text-center">
                            <div class="text-xs text-red-600">Minimum</div>
                            <div class="text-lg font-bold text-red-900"><?php echo e($yieldTrends['min']); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\batches\yield-analysis.blade.php ENDPATH**/ ?>