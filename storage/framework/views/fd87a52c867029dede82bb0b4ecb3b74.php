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
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <?php echo e(__('Occupancy & ADR Statistics')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <form method="GET" action="<?php echo e(route('hotel.night-audit.statistics')); ?>" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                        <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>"
                            class="rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                        <input type="date" name="date_to" value="<?php echo e($dateTo); ?>"
                            class="rounded-md border-gray-300">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Apply
                    </button>
                </form>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average Occupancy</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        <?php echo e(number_format($avgOccupancy, 1)); ?>%</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average ADR</h3>
                    <p class="text-3xl font-bold text-green-600">Rp
                        <?php echo e(number_format($avgADR, 0, ',', '.')); ?></p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average RevPAR</h3>
                    <p class="text-3xl font-bold text-purple-600">Rp
                        <?php echo e(number_format($avgRevPAR, 0, ',', '.')); ?></p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Total Revenue</h3>
                    <p class="text-3xl font-bold text-orange-600">Rp
                        <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Daily Occupancy Statistics</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Total Rooms</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Occupied</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Available</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Occupancy %</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Check-ins</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Check-outs</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $occupancyStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e($stat->stat_date->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->total_rooms); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->occupied_rooms); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->available_rooms); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo e($stat->occupancy_percentage >= 80
                                            ? 'bg-green-100 text-green-800'
                                            : ($stat->occupancy_percentage >= 50
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800')); ?>">
                                            <?php echo e(number_format($stat->occupancy_percentage, 1)); ?>%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->check_ins); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->check_outs); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No occupancy data for selected period
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Daily Rate Statistics (ADR & RevPAR)
                    </h3>
                    <form action="<?php echo e(route('hotel.night-audit.recalculate-rates')); ?>" method="POST"
                        class="flex gap-2">
                        <?php echo csrf_field(); ?>
                        <input type="date" name="stat_date" required
                            class="rounded-md border-gray-300 text-sm">
                        <button type="submit"
                            class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Recalculate
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Rooms Sold</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Room Revenue</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    ADR</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    RevPAR</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $rateStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e($stat->stat_date->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($stat->rooms_sold); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Rp <?php echo e(number_format($stat->total_room_revenue, 0, ',', '.')); ?>

                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                        Rp <?php echo e(number_format($stat->adr, 0, ',', '.')); ?>

                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-purple-600">
                                        Rp <?php echo e(number_format($stat->revpar, 0, ',', '.')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No rate data for selected period
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\night-audit\statistics.blade.php ENDPATH**/ ?>