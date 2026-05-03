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
            <?php echo e(__('Report Expired')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl p-8 shadow text-center">
                <div class="text-6xl mb-4">⏰</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    Report Has Expired
                </h3>
                <p class="text-gray-600 mb-6">
                    The report "<strong><?php echo e($sharedReport->name); ?></strong>" is no longer available.
                </p>

                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold">Expired on:</span>
                        <?php echo e($sharedReport->expires_at->format('d M Y H:i')); ?>

                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold">Shared by:</span>
                        <?php echo e($sharedReport->creator->name ?? 'Unknown'); ?>

                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Total views:</span>
                        <?php echo e($sharedReport->access_count); ?>

                    </p>
                </div>

                <p class="text-sm text-gray-500 mb-6">
                    Please contact the person who shared this report if you need access.
                </p>

                <a href="/"
                    class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Go to Dashboard
                </a>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\shared-report-expired.blade.php ENDPATH**/ ?>