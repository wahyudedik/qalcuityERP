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
     <?php $__env->slot('header', null, []); ?> Access Restricted <?php $__env->endSlot(); ?>

    <div class="min-h-screen flex items-center justify-center">
        <div
            class="bg-white rounded-2xl border border-gray-200 p-8 max-w-2xl mx-auto text-center">
            
            <div
                class="w-20 h-20 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                Access Outside Business Hours
            </h1>

            
            <p class="text-gray-600 mb-6">
                <?php echo e($message ?? 'Access to this resource is restricted to business hours only.'); ?>

            </p>

            
            <div
                class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">Business Hours</h3>
                <p class="text-sm text-blue-700">
                    <?php echo e($business_hours['display'] ?? 'Monday - Friday, 08:00 - 18:00'); ?>

                </p>
                <?php if(isset($current_time)): ?>
                    <p class="text-xs text-blue-600 mt-2">
                        Current time: <?php echo e($current_time->format('l, H:i')); ?>

                    </p>
                <?php endif; ?>
            </div>

            
            <?php if(auth()->user() && (auth()->user()->hasRole('doctor') || auth()->user()->hasRole('emergency_staff'))): ?>
                <div
                    class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <h3 class="text-sm font-semibold text-red-900 mb-2">Emergency Access</h3>
                    <p class="text-sm text-red-700 mb-3">
                        If this is an emergency situation, you can request emergency access.
                    </p>
                    <form action="<?php echo e(url()->current()); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="emergency_access" value="true">
                        <div class="mb-3">
                            <textarea name="override_reason" placeholder="Please provide reason for emergency access..." required
                                class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500"
                                rows="3"></textarea>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Request Emergency Access
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="<?php echo e(url('/healthcare')); ?>"
                    class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Back to Healthcare Dashboard
                </a>
                <button onclick="history.back()"
                    class="px-6 py-2 text-sm bg-gray-200 text-gray-900 rounded-xl hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Go Back
                </button>
            </div>

            
            <p class="text-xs text-gray-500 mt-6">
                If you believe this is an error, please contact your system administrator.
            </p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\healthcare-after-hours.blade.php ENDPATH**/ ?>