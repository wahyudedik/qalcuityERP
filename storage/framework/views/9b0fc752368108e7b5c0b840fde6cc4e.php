

<?php $__env->startSection('title', 'Check-in / Check-out Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Check-in / Check-out Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">
                Kelola check-in dan check-out tamu hari ini
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Check-in Hari Ini</p>
                        <p class="text-3xl font-bold mt-2"><?php echo e($checkIns->count()); ?></p>
                    </div>
                    <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Check-out Hari Ini</p>
                        <p class="text-3xl font-bold mt-2"><?php echo e($checkOuts->count()); ?></p>
                    </div>
                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-100 text-sm">Early Check-in</p>
                        <p class="text-3xl font-bold mt-2"><?php echo e($earlyCheckIns->count()); ?></p>
                    </div>
                    <svg class="w-12 h-12 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Late Check-out</p>
                        <p class="text-3xl font-bold mt-2"><?php echo e($lateCheckOuts->count()); ?></p>
                    </div>
                    <svg class="w-12 h-12 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Check-ins Today -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Check-in Hari Ini
                        </h2>
                        <span
                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                            <?php echo e($checkIns->count()); ?>

                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <?php if($checkIns->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $checkIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $hasPreArrival =
                                        $reservation->preArrivalForm && $reservation->preArrivalForm->isComplete();
                                    $roomAssigned = $reservation->room_id !== null;
                                ?>
                                <div
                                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="font-semibold text-gray-900">
                                                    <?php echo e($reservation->guest?->name ?? '-'); ?>

                                                </h3>
                                                <?php if($hasPreArrival): ?>
                                                    <span
                                                        class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium">
                                                        ✓ Pre-Arrival Complete
                                                    </span>
                                                <?php else: ?>
                                                    <span
                                                        class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-medium">
                                                        ⚠ Pre-Arrival Pending
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <?php echo e($reservation->roomType?->name ?? '-'); ?> •
                                                <?php if($roomAssigned): ?>
                                                    <span class="text-green-600 font-medium">Room
                                                        <?php echo e($reservation->room?->number ?? 'TBA'); ?></span>
                                                <?php else: ?>
                                                    <span class="text-amber-600">Room: Not
                                                        Assigned</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="flex flex-col gap-1 items-end">
                                            <?php if($reservation->status === 'confirmed'): ?>
                                                <span
                                                    class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                                    Confirmed
                                                </span>
                                            <?php elseif($reservation->status === 'pending'): ?>
                                                <span
                                                    class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-3 text-sm mb-3">
                                        <div>
                                            <span class="text-gray-500">Check-in:</span>
                                            <span class="ml-1 text-gray-900 font-medium">
                                                <?php echo e($reservation->check_in_date->format('d M Y')); ?>

                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Nights:</span>
                                            <span class="ml-1 text-gray-900 font-medium">
                                                <?php echo e($reservation->nights); ?>

                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Total:</span>
                                            <span class="ml-1 text-blue-600 font-bold">
                                                Rp <?php echo e(number_format($reservation->grand_total, 0, ',', '.')); ?>

                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <?php if($roomAssigned): ?>
                                            <form method="POST" action="<?php echo e(route('hotel.checkin.quick', $reservation)); ?>"
                                                class="flex-1">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Quick Check-in
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('hotel.checkin.form', $reservation)); ?>"
                                                class="flex-1 text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                Assign Room & Check-in
                                            </a>
                                        <?php endif; ?>

                                        <?php if(!$hasPreArrival): ?>
                                            <a href="<?php echo e(route('hotel.checkin.pre-arrival', $reservation)); ?>"
                                                class="px-4 py-2 border border-purple-300 hover:bg-purple-50 text-purple-700 text-sm font-medium rounded-lg transition-colors">
                                                Pre-Arrival
                                            </a>
                                        <?php endif; ?>

                                        <a href="<?php echo e(route('hotel.reservations.show', $reservation)); ?>"
                                            class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Tidak ada check-in hari ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Check-outs Today -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Check-out Hari Ini
                        </h2>
                        <span
                            class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                            <?php echo e($checkOuts->count()); ?>

                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <?php if($checkOuts->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $checkOuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">
                                                <?php echo e($reservation->guest?->name ?? '-'); ?>

                                            </h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <?php echo e($reservation->roomType?->name ?? '-'); ?> • Room
                                                <?php echo e($reservation->room?->number ?? '-'); ?>

                                            </p>
                                        </div>
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">
                                            Checked In
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                        <div>
                                            <span class="text-gray-500">Check-out:</span>
                                            <span class="ml-1 text-gray-900 font-medium">
                                                <?php echo e($reservation->check_out_date->format('d M Y')); ?>

                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Stayed:</span>
                                            <span class="ml-1 text-gray-900 font-medium">
                                                <?php echo e(now()->diffInDays($reservation->check_in_date)); ?> days
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="<?php echo e(route('hotel.checkout.form', $reservation)); ?>"
                                            class="flex-1 text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            Process Check-out
                                        </a>
                                        <a href="<?php echo e(route('hotel.reservations.show', $reservation)); ?>"
                                            class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Tidak ada check-out hari ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Early Check-ins & Late Check-outs -->
        <?php if($earlyCheckIns->count() > 0 || $lateCheckOuts->count() > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                <?php if($earlyCheckIns->count() > 0): ?>
                    <div class="bg-amber-50 rounded-xl border border-amber-200">
                        <div class="p-6">
                            <h3
                                class="text-lg font-semibold text-amber-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Early Check-ins (Overdue)
                            </h3>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $earlyCheckIns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-white rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">
                                                    <?php echo e($reservation->guest?->name ?? '-'); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    Scheduled: <?php echo e($reservation->check_in_date->format('d M Y')); ?>

                                                </p>
                                            </div>
                                            <a href="<?php echo e(route('hotel.checkin.form', $reservation)); ?>"
                                                class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded">
                                                Check-in Now
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($lateCheckOuts->count() > 0): ?>
                    <div class="bg-red-50 rounded-xl border border-red-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Late Check-outs (Extended)
                            </h3>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $lateCheckOuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-white rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">
                                                    <?php echo e($reservation->guest?->name ?? '-'); ?></p>
                                                <p class="text-xs text-gray-500">
                                                    Due: <?php echo e($reservation->check_out_date->format('d M Y')); ?>

                                                </p>
                                            </div>
                                            <a href="<?php echo e(route('hotel.checkout.form', $reservation)); ?>"
                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded">
                                                Check-out Now
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\check-in-out\index.blade.php ENDPATH**/ ?>