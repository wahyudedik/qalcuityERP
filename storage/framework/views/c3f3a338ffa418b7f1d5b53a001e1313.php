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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Telemedicine Consultation Details')); ?> -
                <?php echo e($telemedicine->consultation_id); ?></h2>
            <a href="<?php echo e(route('healthcare.telemedicine.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if($telemedicine->status === 'scheduled'): ?>
                <div class="bg-green-600 text-white p-4 rounded-lg mb-6">
                    <a href="<?php echo e(route('healthcare.telemedicine.join', $telemedicine)); ?>"
                        class="inline-flex items-center px-6 py-3 bg-white text-green-600 rounded-md font-semibold hover:bg-gray-100">
                        <i class="fas fa-video mr-2"></i>Join Video Call
                    </a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Consultation Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Consultation ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($telemedicine->consultation_id); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($telemedicine->patient->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Doctor</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($telemedicine->doctor->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst($telemedicine->consultation_type)); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($telemedicine->status === 'completed' ? 'bg-green-100 text-green-800' : ($telemedicine->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $telemedicine->status))); ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timeline</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scheduled At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->scheduled_at ? $telemedicine->scheduled_at->format('d/m/Y H:i') : '-'); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Started At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->started_at ? $telemedicine->started_at->format('d/m/Y H:i') : '-'); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ended At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->ended_at ? $telemedicine->ended_at->format('d/m/Y H:i') : '-'); ?></dd>
                        </div>
                        <?php if($telemedicine->duration): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($telemedicine->duration); ?> minutes</dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-notes-medical mr-2 text-red-600"></i>Clinical Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Chief Complaint</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                <?php echo e($telemedicine->chief_complaint); ?></dd>
                        </div>
                        <?php if($telemedicine->diagnosis): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    <?php echo e($telemedicine->diagnosis); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-prescription mr-2 text-green-600"></i>Prescription & Notes</h3>
                    <?php if($telemedicine->prescription): ?>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Prescription</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    <?php echo e($telemedicine->prescription); ?></dd>
                            </div>
                        </dl>
                    <?php endif; ?>
                    <?php if($telemedicine->notes): ?>
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line"><?php echo e($telemedicine->notes); ?></dd>
                        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\show.blade.php ENDPATH**/ ?>