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
     <?php $__env->slot('header', null, []); ?> Detail Survei #<?php echo e($survey->id); ?> <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Survei Kepuasan', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Detail Survei'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Survei Kepuasan', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Detail Survei'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Patient Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Patient Name</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($survey->patient->name ?? 'N/A'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Doctor</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($survey->doctor->name ?? 'N/A'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Visit Date</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($survey->visit->visit_date ?? 'N/A'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Survey Date</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($survey->created_at->format('d/m/Y H:i')); ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ratings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Overall</p>
                        <div class="flex items-center justify-center">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i
                                    class="fas fa-star <?php echo e($i <= $survey->overall_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl'); ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-lg font-bold text-gray-900 mt-2"><?php echo e($survey->overall_rating); ?>/5</p>
                    </div>

                    <div class="p-4 bg-green-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Doctor</p>
                        <?php if($survey->doctor_rating): ?>
                            <div class="flex items-center justify-center">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo e($i <= $survey->doctor_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2"><?php echo e($survey->doctor_rating); ?>/5</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Not rated</p>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 bg-teal-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Nurse</p>
                        <?php if($survey->nurse_rating): ?>
                            <div class="flex items-center justify-center">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo e($i <= $survey->nurse_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2"><?php echo e($survey->nurse_rating); ?>/5</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Not rated</p>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 bg-purple-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Facility</p>
                        <?php if($survey->facility_rating): ?>
                            <div class="flex items-center justify-center">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo e($i <= $survey->facility_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2"><?php echo e($survey->facility_rating); ?>/5</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Not rated</p>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 bg-orange-50 rounded text-center">
                        <p class="text-sm font-medium text-gray-700 mb-2">Cleanliness</p>
                        <?php if($survey->cleanliness_rating): ?>
                            <div class="flex items-center justify-center">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo e($i <= $survey->cleanliness_rating ? 'text-yellow-400 text-2xl' : 'text-gray-300 text-2xl'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-lg font-bold text-gray-900 mt-2"><?php echo e($survey->cleanliness_rating); ?>/5</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Not rated</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($survey->would_recommend !== null): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendation</h3>
                    <div class="flex items-center">
                        <?php if($survey->would_recommend): ?>
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <span class="text-lg font-semibold text-gray-900">Patient would recommend this
                                facility</span>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                            <span class="text-lg font-semibold text-gray-900">Patient would NOT recommend this
                                facility</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($survey->comments): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Comments</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-sm text-gray-700"><?php echo e($survey->comments); ?></p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-satisfaction\show.blade.php ENDPATH**/ ?>