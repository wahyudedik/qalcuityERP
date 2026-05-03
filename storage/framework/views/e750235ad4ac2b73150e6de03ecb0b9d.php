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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Bed Occupancy Rate (BOR)')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.analytics.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="<?php echo e(route('healthcare.analytics.bor')); ?>"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from"
                                value="<?php echo e($dateFrom instanceof \Carbon\Carbon ? $dateFrom->format('Y-m-d') : $dateFrom); ?>"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to"
                                value="<?php echo e($dateTo instanceof \Carbon\Carbon ? $dateTo->format('Y-m-d') : $dateTo); ?>"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-filter mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-bed text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Beds</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($bor['total_beds']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-procedures text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg Occupied</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($bor['avg_occupied']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-percentage text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($bor['occupancy_rate'], 1)); ?>%
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-chart-line text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Target</p>
                            <p class="text-2xl font-bold text-gray-900">60-85%</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(count($bor['by_ward']) > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-hospital mr-2 text-blue-600"></i>BOR by Ward</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ward
                                        Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Beds</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Occupied
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">BOR</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $bor['by_ward']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo e($ward['name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo e($ward['total_beds']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo e($ward['occupied']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                                        style="width: <?php echo e(min($ward['bor'], 100)); ?>%"></div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-gray-900"><?php echo e(number_format($ward['bor'], 1)); ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($ward['bor'] >= 60 && $ward['bor'] <= 85): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Optimal</span>
                                            <?php elseif($ward['bor'] < 60): ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Under-utilized</span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Over-capacity</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-info-circle mr-2 text-indigo-600"></i>BOR Classification</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-900">&lt; 60%</p>
                        <p class="text-xs text-red-700 mt-1">Under-utilized</p>
                    </div>
                    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-sm font-semibold text-green-900">60% - 85%</p>
                        <p class="text-xs text-green-700 mt-1">Optimal (Ideal)</p>
                    </div>
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <p class="text-sm font-semibold text-yellow-900">85% - 90%</p>
                        <p class="text-xs text-yellow-700 mt-1">High Utilization</p>
                    </div>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-900">&gt; 90%</p>
                        <p class="text-xs text-red-700 mt-1">Over-capacity</p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\analytics\bor.blade.php ENDPATH**/ ?>