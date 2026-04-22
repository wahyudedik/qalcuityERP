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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <?php echo e(__('Production Dashboard')); ?>

                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Real-time production monitoring & analytics</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('production.gantt.index')); ?>"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-chart-gantt mr-2"></i>Gantt Chart
                </a>
                <a href="<?php echo e(route('production.index')); ?>"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-list mr-2"></i>Work Orders
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Work Orders</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                                <?php echo e($stats['total_work_orders']); ?>

                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-check mr-1"></i><?php echo e($stats['this_month_completed']); ?> this month
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-industry text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1"><?php echo e($stats['in_progress']); ?></p>
                            <p class="text-xs text-orange-600 mt-1">
                                <i class="fas fa-clock mr-1"></i><?php echo e($stats['pending']); ?> pending
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cog fa-spin text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                            <p class="text-3xl font-bold text-green-600 mt-1"><?php echo e($stats['completed']); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                Yield: <?php echo e($performance['avg_yield_rate']); ?>%
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Overdue</p>
                            <p class="text-3xl font-bold text-red-600 mt-1"><?php echo e($stats['overdue']); ?></p>
                            <p class="text-xs text-red-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Needs attention
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Yield Rate</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-3xl font-bold text-green-600"><?php echo e($performance['avg_yield_rate']); ?>%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-green-600">
                                    <?php echo e($performance['avg_yield_rate'] >= 95 ? '<i class="fas fa-check mr-1"></i>Excellent' : ($performance['avg_yield_rate'] >= 85 ? '<i class="fas fa-exclamation-circle mr-1"></i>Good' : '<i class="fas fa-times mr-1"></i>Needs Improvement')); ?>

                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-gray-700">
                            <div style="width:<?php echo e($performance['avg_yield_rate']); ?>%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Efficiency Rate</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-3xl font-bold text-blue-600"><?php echo e($performance['avg_efficiency']); ?>%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600">
                                    Planned vs Actual
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200 dark:bg-gray-700">
                            <div style="width:<?php echo e($performance['avg_efficiency']); ?>%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Waste Cost</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Scrap</span>
                            <span class="font-semibold text-red-600">Rp
                                <?php echo e(number_format($performance['total_scrap_cost'], 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 dark:text-slate-400">Rework</span>
                            <span class="font-semibold text-orange-600">Rp
                                <?php echo e(number_format($performance['total_rework_cost'], 0, ',', '.')); ?></span>
                        </div>
                        <div
                            class="border-t border-gray-200 dark:border-slate-700 pt-2 flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">Total</span>
                            <span class="text-lg font-bold text-red-600">Rp
                                <?php echo e(number_format($performance['total_waste_cost'], 0, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Priority Distribution</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-red-600"></i></div>
                        <p class="text-2xl font-bold text-red-600"><?php echo e($priorityDist['urgent']); ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Urgent</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-orange-600"></i></div>
                        <p class="text-2xl font-bold text-orange-600"><?php echo e($priorityDist['high']); ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">High</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-blue-600"></i></div>
                        <p class="text-2xl font-bold text-blue-600"><?php echo e($priorityDist['normal']); ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Normal</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                        <div class="text-3xl mb-2"><i class="fas fa-circle text-gray-600"></i></div>
                        <p class="text-2xl font-bold text-gray-600"><?php echo e($priorityDist['low']); ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Low</p>
                    </div>
                </div>
            </div>

            
            <?php if($overdueOrders->isNotEmpty()): ?>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-6 border-2 border-red-200 dark:border-red-800">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-4">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Overdue Work Orders
                        (<?php echo e($overdueOrders->count()); ?>)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="text-xs text-red-700 dark:text-red-300 uppercase bg-red-100 dark:bg-red-900/40">
                                <tr>
                                    <th class="px-4 py-2 text-left">WO Number</th>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left">Planned End</th>
                                    <th class="px-4 py-2 text-left">Days Overdue</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-200 dark:divide-red-800">
                                <?php $__currentLoopData = $overdueOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            <?php echo e($wo->number); ?>

                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                            <?php echo e($wo->product?->name ?? '-'); ?></td>
                                        <td class="px-4 py-3 text-red-600">
                                            <?php echo e($wo->planned_end_date->format('d M Y')); ?></td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-600 text-white rounded">
                                                <?php echo e(now()->diffInDays($wo->planned_end_date)); ?> days
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 rounded">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $wo->status))); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Work Orders</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo e($wo->number); ?></span>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded 
                            <?php echo e($wo->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ''); ?>

                            <?php echo e($wo->status === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ''); ?>

                            <?php echo e($wo->status === 'pending' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : ''); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $wo->status))); ?>

                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($wo->product?->name ?? '-'); ?>

                                </p>
                                <div
                                    class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Target: <?php echo e(number_format($wo->target_quantity, 0)); ?>

                                        <?php echo e($wo->unit); ?></span>
                                    <span>Progress: <?php echo e($wo->progress_percent); ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                No work orders yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products by Volume</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo e($index + 1); ?>. <?php echo e($product->product?->name ?? 'Unknown'); ?>

                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo e(number_format($product->total_quantity, 0)); ?> units
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: <?php echo e($topProducts->first()->total_quantity > 0 ? ($product->total_quantity / $topProducts->first()->total_quantity) * 100 : 0); ?>%">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($product->order_count); ?>

                                    orders
                                </p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                No production data yet
                            </div>
                        <?php endif; ?>
                    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\production\dashboard.blade.php ENDPATH**/ ?>