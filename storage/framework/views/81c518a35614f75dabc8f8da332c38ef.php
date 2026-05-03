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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('KPI Dashboard')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.analytics.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Bed Occupancy Rate</h3>
                        <i class="fas fa-bed text-blue-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['bed_occupancy_rate'], 1)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: 60-85%</p>
                    <?php if($kpis['bed_occupancy_rate'] >= 60 && $kpis['bed_occupancy_rate'] <= 85): ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">On
                            Target</span>
                    <?php else: ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Off
                            Target</span>
                    <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Avg Length of Stay</h3>
                        <i class="fas fa-clock text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['average_length_of_stay'], 1)); ?>

                        days</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5 days</p>
                            <?php if($kpis['average_length_of_stay'] < 5): ?>
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
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['mortality_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 3%</p>
                            <?php if($kpis['mortality_rate'] < 3): ?>
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
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['infection_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 2%</p>
                            <?php if($kpis['infection_rate'] < 2): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High
                                    Risk</span>
                            <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Patient Satisfaction</h3>
                        <i class="fas fa-smile text-teal-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['patient_satisfaction'], 1)); ?>%
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Target: > 85%</p>
                    <?php if($kpis['patient_satisfaction'] >= 85): ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                    <?php else: ?>
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Needs
                            Work</span>
                    <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Readmission Rate</h3>
                        <i class="fas fa-undo text-purple-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['readmission_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5%</p>
                            <?php if($kpis['readmission_rate'] < 5): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Surgery Cancellation</h3>
                        <i class="fas fa-ban text-pink-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['surgery_cancellation_rate'], 2)); ?>%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 3%</p>
                            <?php if($kpis['surgery_cancellation_rate'] < 3): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Acceptable</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High</span>
                            <?php endif; ?>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Emergency Wait Time</h3>
                        <i class="fas fa-ambulance text-indigo-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($kpis['emergency_wait_time'], 0)); ?> min
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 30 min</p>
                            <?php if($kpis['emergency_wait_time'] < 30): ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            <?php else: ?>
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Slow</span>
                            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\analytics\kpi.blade.php ENDPATH**/ ?>