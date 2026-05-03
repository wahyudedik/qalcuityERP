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
     <?php $__env->slot('header', null, []); ?> Konsultasi Telemedicine <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Telemedicine', 'url' => route('healthcare.telemedicine.index')],
        ['label' => 'Konsultasi'],
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
        ['label' => 'Telemedicine', 'url' => route('healthcare.telemedicine.index')],
        ['label' => 'Konsultasi'],
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

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Konsultasi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                <?php echo e(number_format($statistics['total_consultations'] ?? 0)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Terjadwal</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($statistics['scheduled'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Berlangsung</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($statistics['in_progress'] ?? 0); ?>

            </p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">
                <?php echo e($statistics['completed_today'] ?? 0); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                    placeholder="Cari pasien / dokter..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="scheduled" <?php if(request('status') === 'scheduled'): echo 'selected'; endif; ?>>Scheduled</option>
                    <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                    <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                    <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Cancelled</option>
                </select>
                <input type="date" name="date" value="<?php echo e(request('date')); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $consultations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                <?php echo e($consultation->patient ? $consultation->patient->full_name : '-'); ?></h3>
                            <p class="text-sm text-gray-500">Dokter:
                                <?php echo e($consultation->doctor ? $consultation->doctor->name : '-'); ?></p>
                        </div>
                        <div>
                            <?php if($consultation->status === 'scheduled'): ?>
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-blue-500 text-white rounded-lg">Scheduled</span>
                            <?php elseif($consultation->status === 'in_progress'): ?>
                                <span class="px-3 py-1 text-xs font-bold bg-green-500 text-white rounded-lg">In
                                    Progress</span>
                            <?php elseif($consultation->status === 'completed'): ?>
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-purple-500 text-white rounded-lg">Completed</span>
                            <?php elseif($consultation->status === 'cancelled'): ?>
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-red-500 text-white rounded-lg">Cancelled</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span><?php echo e($consultation->scheduled_time ? $consultation->scheduled_time->format('d M Y H:i') : '-'); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg>
                            <span><?php echo e($consultation->chief_complaint ?? 'Konsultasi Umum'); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>Video Call</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <?php if(
                            $consultation->status === 'in_progress' ||
                                ($consultation->status === 'scheduled' &&
                                    $consultation->scheduled_time &&
                                    $consultation->scheduled_time->isPast())): ?>
                            <a href="<?php echo e(route('healthcare.telemedicine.video-room', $consultation)); ?>"
                                class="flex-1 px-4 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium text-center flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Join Call
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('healthcare.telemedicine.consultations.show', $consultation)); ?>"
                            class="px-4 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div
                class="col-span-full bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="text-gray-500">Belum ada konsultasi</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if(isset($consultations) && $consultations->hasPages()): ?>
        <div class="mt-6">
            <?php echo e($consultations->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\consultations.blade.php ENDPATH**/ ?>