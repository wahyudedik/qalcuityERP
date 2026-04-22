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
     <?php $__env->slot('header', null, []); ?> My Appointments <?php $__env->endSlot(); ?>

    <?php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    ?>

    <?php if(!$patient): ?>
        <div
            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-2xl p-6 text-center">
            <p class="text-sm text-red-700 dark:text-red-300">Patient profile not found. Please contact reception.</p>
        </div>
    <?php else: ?>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <?php
                $upcomingAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('appointment_date', '>=', now())
                    ->where('status', 'scheduled')
                    ->count();
                $completedAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('status', 'completed')
                    ->count();
                $cancelledAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->where('status', 'cancelled')
                    ->count();
                $todayAppointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->whereDate('appointment_date', today())
                    ->where('status', 'scheduled')
                    ->count();
            ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Upcoming</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($upcomingAppointments); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Today</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($todayAppointments); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Completed</p>
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-1"><?php echo e($completedAppointments); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">Cancelled</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($cancelledAppointments); ?></p>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
            <div class="p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <select name="status"
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">All Status</option>
                        <option value="scheduled" <?php if(request('status') === 'scheduled'): echo 'selected'; endif; ?>>Scheduled</option>
                        <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                        <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                        <option value="no_show" <?php if(request('status') === 'no_show'): echo 'selected'; endif; ?>>No Show</option>
                    </select>
                    <input type="date" name="from" value="<?php echo e(request('from')); ?>"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <input type="date" name="to" value="<?php echo e(request('to')); ?>"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        
        <div class="space-y-4">
            <?php
                $appointments = \App\Models\Appointment::where('patient_id', $patient->id)
                    ->when(request('status'), function ($q) {
                        $q->where('status', request('status'));
                    })
                    ->when(request('from'), function ($q) {
                        $q->whereDate('appointment_date', '>=', request('from'));
                    })
                    ->when(request('to'), function ($q) {
                        $q->whereDate('appointment_date', '<=', request('to'));
                    })
                    ->orderBy('appointment_date', 'desc')
                    ->paginate(10);
            ?>

            <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        
                        <div class="flex-shrink-0">
                            <div
                                class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    <?php echo e($appointment->appointment_date->format('d')); ?>

                                </span>
                                <span class="text-xs text-blue-600 dark:text-blue-400">
                                    <?php echo e($appointment->appointment_date->format('M')); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        <?php echo e($appointment->doctor ? $appointment->doctor->name : 'Doctor Not Assigned'); ?>

                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-slate-300">
                                        <?php echo e($appointment->doctor ? $appointment->doctor->specialization ?? '-' : '-'); ?>

                                    </p>
                                </div>
                                <?php if($appointment->status === 'scheduled'): ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        Scheduled
                                    </span>
                                <?php elseif($appointment->status === 'completed'): ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        Completed
                                    </span>
                                <?php elseif($appointment->status === 'cancelled'): ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        Cancelled
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        <?php echo e(ucfirst($appointment->status)); ?>

                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-600 dark:text-slate-300">
                                        <?php echo e($appointment->appointment_date->format('H:i')); ?> -
                                        <?php echo e($appointment->appointment_date->format('d M Y')); ?>

                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-gray-600 dark:text-slate-300">
                                        <?php echo e($appointment->department ?? 'General'); ?>

                                    </span>
                                </div>
                            </div>

                            <?php if($appointment->notes): ?>
                                <p
                                    class="mt-3 text-sm text-gray-600 dark:text-slate-300 bg-gray-50 dark:bg-white/5 p-3 rounded-lg">
                                    <span class="font-medium">Notes:</span> <?php echo e($appointment->notes); ?>

                                </p>
                            <?php endif; ?>
                        </div>

                        
                        <div class="flex sm:flex-col gap-2">
                            <?php if($appointment->status === 'scheduled'): ?>
                                <button onclick="cancelAppointment(<?php echo e($appointment->id); ?>)"
                                    class="flex-1 sm:flex-none px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                                    Cancel
                                </button>
                                <button onclick="rescheduleAppointment(<?php echo e($appointment->id); ?>)"
                                    class="flex-1 sm:flex-none px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                    Reschedule
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-slate-500 mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No Appointments Found</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-4">You don't have any appointments matching
                        the selected filters.</p>
                    <a href="<?php echo e(route('healthcare.portal.appointments.create')); ?>"
                        class="inline-flex items-center px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Book New Appointment
                    </a>
                </div>
            <?php endif; ?>

            <?php if($appointments->hasPages()): ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                    <?php echo e($appointments->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function cancelAppointment(id) {
                if (confirm('Are you sure you want to cancel this appointment?')) {
                    fetch(`/healthcare/portal/appointments/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    }).then(() => location.reload());
                }
            }

            function rescheduleAppointment(id) {
                window.location.href = `/healthcare/portal/appointments/${id}/reschedule`;
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-portal\appointments.blade.php ENDPATH**/ ?>