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
     <?php $__env->slot('header', null, []); ?> Patient Portal Dashboard <?php $__env->endSlot(); ?>

    <?php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    ?>

    <?php if(!$patient): ?>
        <div
            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-2xl p-6 text-center">
            <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <h3 class="text-lg font-bold text-red-900 dark:text-red-200 mb-2">Patient Profile Not Found</h3>
            <p class="text-sm text-red-700 dark:text-red-300">Please contact reception to link your account with patient
                profile.</p>
        </div>
    <?php else: ?>
        
        <div
            class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Welcome, <?php echo e($patient->name); ?>!</h2>
                    <p class="text-blue-100">Patient ID: <span
                            class="font-mono"><?php echo e($patient->patient_id ?? 'N/A'); ?></span></p>
                    <p class="text-sm text-blue-100 mt-1">Last visit:
                        <?php echo e($patient->lastVisit ? $patient->lastVisit->visit_date->format('d M Y') : 'No visits yet'); ?>

                    </p>
                </div>
                <div class="hidden sm:block">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <p class="text-xs text-blue-100">Next Appointment</p>
                        <?php if(isset($nextAppointment) && $nextAppointment): ?>
                            <p class="text-lg font-bold"><?php echo e($nextAppointment->appointment_date->format('d M Y')); ?></p>
                            <p class="text-sm"><?php echo e($nextAppointment->appointment_date->format('H:i')); ?> -
                                <?php echo e($nextAppointment->doctor ? $nextAppointment->doctor->name : '-'); ?></p>
                        <?php else: ?>
                            <p class="text-sm">No upcoming appointments</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Visits</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($statistics['total_visits'] ?? 0); ?>

                </p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Prescriptions</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                    <?php echo e($statistics['total_prescriptions'] ?? 0); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Lab Tests</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                    <?php echo e($statistics['total_lab_orders'] ?? 0); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Pending Bills</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">Rp
                    <?php echo e(number_format($statistics['pending_bills'] ?? 0, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <a href="<?php echo e(route('healthcare.portal.appointments')); ?>"
                class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Book Appointment</p>
                </div>
            </a>
            <a href="<?php echo e(route('healthcare.portal.records')); ?>"
                class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10 hover:border-green-500 dark:hover:border-green-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Medical Records</p>
                </div>
            </a>
            <a href="<?php echo e(route('healthcare.portal.billing')); ?>"
                class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10 hover:border-purple-500 dark:hover:border-purple-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">View Bills</p>
                </div>
            </a>
            <a href="<?php echo e(route('healthcare.portal.prescriptions')); ?>"
                class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10 hover:border-amber-500 dark:hover:border-amber-400 transition-colors">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Prescriptions</p>
                </div>
            </a>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upcoming Appointments</h3>
                    <a href="<?php echo e(route('healthcare.portal.appointments')); ?>"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                </div>
                <div class="space-y-3">
                    <?php
                        $appointments = \App\Models\Appointment::where('patient_id', $patient->id)
                            ->where('appointment_date', '>=', now())
                            ->where('status', 'scheduled')
                            ->orderBy('appointment_date')
                            ->limit(3)
                            ->get();
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span
                                    class="text-lg font-bold text-blue-600 dark:text-blue-400"><?php echo e($appointment->appointment_date->format('d')); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <?php echo e($appointment->doctor ? $appointment->doctor->name : '-'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($appointment->appointment_date->format('d M Y • H:i')); ?></p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                <?php echo e(ucfirst($appointment->status)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No upcoming appointments
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Lab Results</h3>
                    <a href="<?php echo e(route('healthcare.portal.records')); ?>"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View All</a>
                </div>
                <div class="space-y-3">
                    <?php
                        $labResults = \App\Models\LabResult::where('patient_id', $patient->id)
                            ->where('status', 'completed')
                            ->orderBy('result_date', 'desc')
                            ->limit(3)
                            ->get();
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $labResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                            <div
                                class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    <?php echo e($result->test_name ?? 'Lab Test'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($result->result_date ? \Carbon\Carbon::parse($result->result_date)->format('d M Y') : '-'); ?>

                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Completed
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No lab results available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-portal\dashboard.blade.php ENDPATH**/ ?>