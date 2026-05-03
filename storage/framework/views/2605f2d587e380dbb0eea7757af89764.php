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
     <?php $__env->slot('header', null, []); ?> <i class="fas fa-chart-line mr-2 text-green-600"></i>Distribution Channel Analytics <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('cosmetic.distribution.channel.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Add Channel
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Channel Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $channelStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo e($stat['name']); ?></h3>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e(ucfirst($stat['type'])); ?>

                                </span>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Total Sales</span>
                                    <span class="text-lg font-bold text-green-600">Rp
                                        <?php echo e(number_format($stat['total_sales'], 0, ',', '.')); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Units Sold</span>
                                    <span
                                        class="text-md font-semibold text-gray-900"><?php echo e(number_format($stat['total_quantity'])); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Transactions</span>
                                    <span
                                        class="text-md font-semibold text-gray-900"><?php echo e($stat['transaction_count']); ?></span>
                                </div>
                                <div
                                    class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="text-sm text-gray-500">Avg Order Value</span>
                                    <span class="text-md font-bold text-blue-600">Rp
                                        <?php echo e(number_format($stat['avg_order_value'], 0, ',', '.')); ?></span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="<?php echo e(route('cosmetic.distribution.channel.show', $stat['id'])); ?>"
                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-900">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div
                        class="col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                        <i class="fas fa-store text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500 text-lg">No distribution channels yet</p>
                        <a href="<?php echo e(route('cosmetic.distribution.channel.create')); ?>"
                            class="inline-flex items-center mt-4 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>Create First Channel
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sales Trend -->
            <?php if($salesTrend->count() > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Sales Trend (Last 30 Days)</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <?php $__currentLoopData = $salesTrend->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm text-gray-600 w-24"><?php echo e($sale->date); ?></span>
                                    <div class="flex-1 mx-4">
                                        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                                            <div class="bg-green-600 h-full rounded-full transition-all"
                                                style="width: <?php echo e(min(100, ($sale->total / $salesTrend->max('total')) * 100)); ?>%">
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 w-32 text-right">
                                        Rp <?php echo e(number_format($sale->total, 0, ',', '.')); ?>

                                    </span>
                                    <span class="text-xs text-gray-500 w-20 text-right">
                                        <?php echo e($sale->quantity); ?> units
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\distribution\dashboard.blade.php ENDPATH**/ ?>