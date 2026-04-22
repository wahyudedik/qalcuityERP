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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Clinical Quality Metrics')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Readmission Rate</h3>
                        <i class="fas fa-undo text-purple-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($qualityMetrics['readmission_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5%</p>
                            <?php if($qualityMetrics['readmission_rate'] < 5): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Avg Length of Stay</h3>
                        <i class="fas fa-bed text-blue-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($qualityMetrics['average_length_of_stay'], 2)); ?> days</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5 days</p>
                            <?php if($qualityMetrics['average_length_of_stay'] < 5): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Needs
                                    Improvement</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Mortality Rate</h3>
                        <i class="fas fa-heartbeat text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($qualityMetrics['mortality_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 3%</p>
                            <?php if($qualityMetrics['mortality_rate'] < 3): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Acceptable</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Critical</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Infection Rate</h3>
                        <i class="fas fa-virus text-orange-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($qualityMetrics['infection_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 2%</p>
                            <?php if($qualityMetrics['infection_rate'] < 2): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High
                                    Risk</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Patient Satisfaction</h3>
                        <i class="fas fa-smile text-teal-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($qualityMetrics['patient_satisfaction'], 1)); ?>/5.0</p>
                    <p class="text-xs text-gray-500 mt-2">Target: > 4.0</p>
                    <?php if($qualityMetrics['patient_satisfaction'] >= 4): ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                    <?php else: ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Needs
                            Work</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-chart-line mr-2 text-blue-600"></i>Quality Indicators</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Readmission Rate</span>
                            <span
                                class="text-lg font-bold text-blue-900"><?php echo e(number_format($qualityMetrics['readmission_rate'], 2)); ?>%</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Average Length of Stay</span>
                            <span
                                class="text-lg font-bold text-green-900"><?php echo e(number_format($qualityMetrics['average_length_of_stay'], 2)); ?>

                                days</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Mortality Rate</span>
                            <span
                                class="text-lg font-bold text-red-900"><?php echo e(number_format($qualityMetrics['mortality_rate'], 4)); ?>%</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Infection Rate</span>
                            <span
                                class="text-lg font-bold text-orange-900"><?php echo e(number_format($qualityMetrics['infection_rate'], 2)); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-indigo-600"></i>Benchmarks</h3>
                    <div class="space-y-3">
                        <div class="p-3 border-l-4 border-green-500 bg-green-50 rounded">
                            <p class="text-sm font-semibold text-green-900">Readmission Rate</p>
                            <p class="text-xs text-green-700">Excellent: < 5% | Good: 5-8% | Needs Improvement:> 8%</p>
                        </div>
                        <div class="p-3 border-l-4 border-blue-500 bg-blue-50 rounded">
                            <p class="text-sm font-semibold text-blue-900">Average Length of Stay</p>
                            <p class="text-xs text-blue-700">Excellent: < 4 days | Good: 4-6 days | High:> 6 days</p>
                        </div>
                        <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                            <p class="text-sm font-semibold text-red-900">Mortality Rate</p>
                            <p class="text-xs text-red-700">Acceptable: < 3% | Warning: 3-5% | Critical:> 5%</p>
                        </div>
                        <div class="p-3 border-l-4 border-orange-500 bg-orange-50 rounded">
                            <p class="text-sm font-semibold text-orange-900">Infection Rate</p>
                            <p class="text-xs text-orange-700">Good: < 2% | Warning: 2-4% | High Risk:> 4%</p>
                        </div>
                        <div class="p-3 border-l-4 border-teal-500 bg-teal-50 rounded">
                            <p class="text-sm font-semibold text-teal-900">Patient Satisfaction</p>
                            <p class="text-xs text-teal-700">Excellent: > 4.5 | Good: 4.0-4.5 | Needs Work: < 4.0</p>
                        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\clinical-quality\index.blade.php ENDPATH**/ ?>