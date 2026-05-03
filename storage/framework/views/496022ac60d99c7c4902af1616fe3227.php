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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Comparative Analysis')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Comparison Type Selector -->
            <div class="mb-6 flex space-x-2">
                <?php $__currentLoopData = [['yoy', 'Year over Year'], ['mom', 'Month over Month'], ['qoq', 'Quarter over Quarter']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('analytics.comparative', ['comparison' => $key])); ?>"
                        class="px-6 py-3 rounded-lg text-sm font-medium transition <?php echo e($comparison === $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'); ?>">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Growth Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <?php $__currentLoopData = $analysis['growth']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-xl p-6 shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">
                                <?php echo e(ucwords(str_replace('_', ' ', $metric))); ?>

                            </h3>
                            <span class="text-2xl">
                                <?php if($metric === 'revenue'): ?>
                                    💰
                                <?php elseif($metric === 'orders'): ?>
                                    📦
                                <?php elseif($metric === 'customers'): ?>
                                    👥
                                <?php else: ?>
                                    📊
                                <?php endif; ?>
                            </span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            <?php if($metric === 'revenue'): ?>
                                Rp <?php echo e(number_format($data['current'], 0, ',', '.')); ?>

                            <?php else: ?>
                                <?php echo e(number_format($data['current'], 0, ',', '.')); ?>

                            <?php endif; ?>
                        </p>
                        <div
                            class="flex items-center text-sm <?php echo e($data['trend'] === 'up' ? 'text-green-600' : 'text-red-600'); ?>">
                            <span class="text-xl"><?php echo e($data['trend'] === 'up' ? '↑' : '↓'); ?></span>
                            <span class="ml-1 font-semibold"><?php echo e(abs($data['percentage'])); ?>%</span>
                            <span class="ml-1 text-gray-500">vs previous</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Absolute change:
                            <?php if($metric === 'revenue'): ?>
                                Rp <?php echo e(number_format($data['absolute'], 0, ',', '.')); ?>

                            <?php else: ?>
                                <?php echo e(number_format($data['absolute'], 0, ',', '.')); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Detailed Comparison Table -->
            <div class="bg-white rounded-xl p-6 shadow mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Detailed Comparison</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Metric</th>
                                <th class="px-4 py-3 text-right">Current Period</th>
                                <th class="px-4 py-3 text-right">Previous Period</th>
                                <th class="px-4 py-3 text-right">Absolute Change</th>
                                <th class="px-4 py-3 text-right">Growth %</th>
                                <th class="px-4 py-3 text-center">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $analysis['growth']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <?php echo e(ucwords(str_replace('_', ' ', $metric))); ?>

                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        <?php if($metric === 'revenue'): ?>
                                            Rp <?php echo e(number_format($data['current'], 0, ',', '.')); ?>

                                        <?php else: ?>
                                            <?php echo e(number_format($data['current'], 0, ',', '.')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        <?php if($metric === 'revenue'): ?>
                                            Rp <?php echo e(number_format($data['previous'], 0, ',', '.')); ?>

                                        <?php else: ?>
                                            <?php echo e(number_format($data['previous'], 0, ',', '.')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right <?php echo e($data['absolute'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php if($metric === 'revenue'): ?>
                                            Rp <?php echo e(number_format($data['absolute'], 0, ',', '.')); ?>

                                        <?php else: ?>
                                            <?php echo e($data['absolute'] > 0 ? '+' : ''); ?><?php echo e(number_format($data['absolute'], 0, ',', '.')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold <?php echo e($data['percentage'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e($data['percentage'] > 0 ? '+' : ''); ?><?php echo e($data['percentage']); ?>%
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-3 py-1 text-xs rounded-full <?php echo e($data['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo e($data['trend'] === 'up' ? '↑ Up' : '↓ Down'); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Period Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">📅 Current Period</h4>
                    <p class="text-sm text-gray-600">
                        <?php echo e($analysis['current_period']['start']->format('d M Y')); ?> -
                        <?php echo e($analysis['current_period']['end']->format('d M Y')); ?>

                    </p>
                </div>
                <div
                    class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">📅 Previous Period</h4>
                    <p class="text-sm text-gray-600">
                        <?php echo e($analysis['previous_period']['start']->format('d M Y')); ?> -
                        <?php echo e($analysis['previous_period']['end']->format('d M Y')); ?>

                    </p>
                </div>
            </div>

            <!-- Insights & Recommendations -->
            <div class="mt-6 bg-white rounded-xl p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Key Insights</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = $analysis['growth']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($data['percentage'] > 10): ?>
                            <div class="flex items-start p-3 bg-green-50 rounded-lg">
                                <span class="text-green-600 mr-2">✅</span>
                                <p class="text-sm text-gray-700">
                                    <strong><?php echo e(ucwords(str_replace('_', ' ', $metric))); ?></strong> shows strong growth
                                    of <?php echo e($data['percentage']); ?>% compared to previous period.
                                </p>
                            </div>
                        <?php elseif($data['percentage'] < -5): ?>
                            <div class="flex items-start p-3 bg-red-50 rounded-lg">
                                <span class="text-red-600 mr-2">⚠️</span>
                                <p class="text-sm text-gray-700">
                                    <strong><?php echo e(ucwords(str_replace('_', ' ', $metric))); ?></strong> declined by
                                    <?php echo e(abs($data['percentage'])); ?>%. Consider investigating the root cause.
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\comparative.blade.php ENDPATH**/ ?>