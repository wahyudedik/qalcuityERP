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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Certificate Details')); ?> -
                <?php echo e($certificate->certificate_number); ?></h2>
            <a href="<?php echo e(route('healthcare.medical-certificates.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0"><i class="fas fa-certificate text-blue-500 text-2xl"></i></div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-semibold">
                            <?php echo e(ucfirst(str_replace('_', ' ', $certificate->certificate_type))); ?></p>
                        <p class="text-xs text-blue-600">Certificate No: <?php echo e($certificate->certificate_number); ?></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-user mr-2 text-blue-600"></i>Patient & Certificate Info</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo e($certificate->patient->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($certificate->status === 'active' ? 'bg-green-100 text-green-800' : ($certificate->status === 'expired' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')); ?>"><?php echo e(ucfirst($certificate->status)); ?></span>
                            </dd>
                        </div>
                        <?php if($certificate->issue_date): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($certificate->issue_date->format('d/m/Y')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if($certificate->valid_until): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($certificate->valid_until->format('d/m/Y')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-stethoscope mr-2 text-red-600"></i>Medical Information</h3>
                    <dl class="space-y-4">
                        <?php if($certificate->diagnosis): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900"><?php echo e($certificate->diagnosis); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Issuing Doctor</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($certificate->doctor_name); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-file-alt mr-2 text-purple-600"></i>Certificate Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($certificate->description); ?></p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-certificates\show.blade.php ENDPATH**/ ?>